<?php

use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    forgetPermissionCache();
});

test('it creates permissions for every discovered resource', function () {
    $this->artisan('rbac:sync')->assertSuccessful();

    $permissions = Permission::query()->where('guard_name', 'web')->pluck('name')->all();

    expect($permissions)->toContain('view_any_user', 'view_user', 'create_user', 'update_user', 'delete_user')
        ->toContain('view_any_role', 'delete_role')
        ->toContain('view_any_product', 'view_product', 'create_product', 'update_product', 'delete_product')
        ->not->toContain('restore_product')
        ->not->toContain('force_delete_product');
});

test('it is idempotent', function () {
    $this->artisan('rbac:sync')->assertSuccessful();
    $count = Permission::query()->count();

    $this->artisan('rbac:sync')
        ->expectsOutputToContain('0 permission(s) created')
        ->assertSuccessful();

    expect(Permission::query()->count())->toBe($count);
});

test('it limits the sync to a single model', function () {
    $this->artisan('rbac:sync', ['--model' => 'product'])->assertSuccessful();

    $permissions = Permission::query()->pluck('name')->all();

    expect($permissions)->toContain('view_any_product')
        ->not->toContain('view_any_user');
});

test('it prunes stale permissions', function () {
    Permission::findOrCreate('stale_permission', 'web');

    $this->artisan('rbac:sync', ['--prune' => true, '--force' => true])->assertSuccessful();

    expect(Permission::query()->where('name', 'stale_permission')->exists())->toBeFalse();
});

test('it warns when rbac is disabled', function () {
    config(['rbac.enabled' => false]);

    $this->artisan('rbac:sync')
        ->expectsOutputToContain('RBAC is disabled')
        ->assertSuccessful();
});
