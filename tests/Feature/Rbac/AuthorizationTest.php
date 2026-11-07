<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\Demo\Models\Product;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    forgetPermissionCache();
});

test('a superadmin bypasses every permission check', function () {
    $superadmin = User::factory()->superadmin()->create();
    $product = Product::factory()->create();

    expect($superadmin->can('update', $product))->toBeTrue()
        ->and($superadmin->can('view_any_product'))->toBeTrue()
        ->and($superadmin->can('a_permission_that_does_not_exist'))->toBeTrue();
});

test('a user without the permission cannot access a protected resource', function () {
    $this->actingAs(User::factory()->create())
        ->get('/products')
        ->assertForbidden();
});

test('a user with a role permission can access a protected resource', function () {
    $user = User::factory()->create();
    grantPermissions($user, ['view_any_product']);

    $this->actingAs($user->refresh())
        ->get('/products')
        ->assertSuccessful();
});

test('a user with the update permission can edit a product', function () {
    $user = User::factory()->create();
    grantPermissions($user, ['update_product']);
    $product = Product::factory()->create();

    $this->actingAs($user->refresh())
        ->get("/products/{$product->id}")
        ->assertSuccessful();
});

test('a user without the update permission cannot edit a product', function () {
    $user = User::factory()->create();
    grantPermissions($user, ['view_any_product']);
    $product = Product::factory()->create();

    $this->actingAs($user->refresh())
        ->get("/products/{$product->id}")
        ->assertForbidden();
});

test('a superadmin can manage other superadmins while permission holders cannot', function () {
    $superadmin = User::factory()->superadmin()->create();
    $otherSuperadmin = User::factory()->superadmin()->create();

    expect($superadmin->can('update', $otherSuperadmin))->toBeTrue()
        ->and($superadmin->can('delete', $otherSuperadmin))->toBeTrue();

    $admin = User::factory()->create();
    grantPermissions($admin, ['view_any_user', 'update_user', 'delete_user']);

    expect($admin->can('update', $otherSuperadmin))->toBeFalse()
        ->and($admin->can('delete', $otherSuperadmin))->toBeFalse();
});

test('the superadmin bypass also covers gates outside this feature', function () {
    $superadmin = User::factory()->superadmin()->create();
    $user = User::factory()->create();

    expect($superadmin->can('viewHorizon'))->toBeTrue()
        ->and($user->can('viewHorizon'))->toBeFalse();
});
