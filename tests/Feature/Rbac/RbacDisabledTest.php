<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Demo\Models\Product;

uses(LazilyRefreshDatabase::class);

test('the management routes are hidden when rbac is disabled', function () {
    config(['rbac.enabled' => false]);

    $user = User::factory()->create();

    $this->actingAs($user)->get('/users')->assertNotFound();
    $this->actingAs($user)->get('/roles')->assertNotFound();
    $this->actingAs($user)->get('/users/create')->assertNotFound();
});

test('permission checks are skipped for authenticated users when rbac is disabled', function () {
    config(['rbac.enabled' => false]);

    $user = User::factory()->create();
    $product = Product::factory()->create();

    expect($user->can('update', $product))->toBeTrue();

    $this->actingAs($user)->get('/products')->assertSuccessful();
});

test('inertia shares an empty permission set when rbac is disabled', function () {
    config(['rbac.enabled' => false]);

    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('rbac.enabled', false)
            ->where('auth.roles', [])
            ->where('auth.permissions', [])
            ->where('auth.is_superadmin', false));
});

test('inertia shares roles and permissions when rbac is enabled', function () {
    $user = User::factory()->create();
    grantPermissions($user, ['view_any_user'], 'reader');

    $this->actingAs($user->refresh())
        ->get('/dashboard')
        ->assertInertia(fn ($page) => $page
            ->where('rbac.enabled', true)
            ->where('auth.roles', ['reader'])
            ->where('auth.permissions', ['view_any_user']));
});
