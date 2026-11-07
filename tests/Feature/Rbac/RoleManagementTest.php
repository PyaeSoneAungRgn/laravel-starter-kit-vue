<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    forgetPermissionCache();
});

test('a superadmin can list roles', function () {
    $superadmin = User::factory()->superadmin()->create();
    Role::findOrCreate('editor', 'web');

    $this->actingAs($superadmin)
        ->get('/roles')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('roles/Index', false)->has('roles.data', 1));
});

test('a user without permission cannot list roles', function () {
    $this->actingAs(User::factory()->create())
        ->get('/roles')
        ->assertForbidden();
});

test('a superadmin can create a role with permissions', function () {
    $superadmin = User::factory()->superadmin()->create();
    Permission::findOrCreate('view_any_product', 'web');

    $this->actingAs($superadmin)
        ->post('/roles', [
            'name' => 'editor',
            'permissions' => ['view_any_product'],
        ])
        ->assertRedirect('/roles');

    $role = Role::findByName('editor', 'web');

    expect($role->hasPermissionTo('view_any_product'))->toBeTrue();
});

test('role names must be unique per guard', function () {
    $superadmin = User::factory()->superadmin()->create();
    Role::findOrCreate('editor', 'web');

    $this->actingAs($superadmin)
        ->post('/roles', ['name' => 'editor'])
        ->assertSessionHasErrors('name');
});

test('a superadmin can update a role and sync its permissions', function () {
    $superadmin = User::factory()->superadmin()->create();
    Permission::findOrCreate('view_any_product', 'web');
    Permission::findOrCreate('update_product', 'web');
    $role = Role::findOrCreate('editor', 'web');
    $role->syncPermissions(['view_any_product']);

    $this->actingAs($superadmin)
        ->put("/roles/{$role->id}", [
            'name' => 'content-editor',
            'permissions' => ['update_product'],
        ])
        ->assertRedirect('/roles');

    $role->refresh();

    expect($role->name)->toBe('content-editor')
        ->and($role->hasPermissionTo('update_product'))->toBeTrue()
        ->and($role->hasPermissionTo('view_any_product'))->toBeFalse();
});

test('a role that is assigned to users cannot be deleted', function () {
    $superadmin = User::factory()->superadmin()->create();
    $role = Role::findOrCreate('editor', 'web');
    User::factory()->create()->assignRole($role);

    $this->actingAs($superadmin)
        ->from('/roles')
        ->delete("/roles/{$role->id}")
        ->assertRedirect('/roles')
        ->assertSessionHasErrors('role');

    $this->assertModelExists($role);
});

test('an unassigned role can be deleted', function () {
    $superadmin = User::factory()->superadmin()->create();
    $role = Role::findOrCreate('editor', 'web');

    $this->actingAs($superadmin)
        ->delete("/roles/{$role->id}")
        ->assertRedirect('/roles');

    $this->assertModelMissing($role);
});

test('a user with the role permissions can manage roles', function () {
    $actor = User::factory()->create();
    grantPermissions($actor, ['view_any_role', 'create_role']);

    $this->actingAs($actor->refresh())->get('/roles')->assertSuccessful();
    $this->actingAs($actor->refresh())->get('/roles/create')->assertSuccessful();
});

test('a user with only the view permission cannot create roles', function () {
    $actor = User::factory()->create();
    grantPermissions($actor, ['view_any_role']);

    $this->actingAs($actor->refresh())->get('/roles/create')->assertForbidden();
});

test('the role form shows permissions grouped by resource', function () {
    $superadmin = User::factory()->superadmin()->create();

    $this->artisan('rbac:sync')->assertSuccessful();

    $this->actingAs($superadmin)
        ->get('/roles/create')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('permissionGroups', 3)
            ->where('permissionGroups.0.resource', 'product')
            ->where('permissionGroups.0.label', 'Product')
            ->where('permissionGroups.0.abilities.0.name', 'view_any_product')
            ->where('permissionGroups.0.abilities.0.label', 'View Any')
            ->where('permissionGroups.0.abilities.1.name', 'view_product')
            ->where('permissionGroups.0.abilities.2.name', 'create_product')
            ->where('permissionGroups.0.abilities.3.name', 'update_product')
            ->where('permissionGroups.0.abilities.3.label', 'Edit')
            ->where('permissionGroups.0.abilities.4.name', 'delete_product'));
});

test('superadmin role names are reserved for the command', function (string $name) {
    $superadmin = User::factory()->superadmin()->create();

    $this->actingAs($superadmin)
        ->post('/roles', ['name' => $name])
        ->assertSessionHasErrors('name');
})->with(['superadmin', 'super admin', 'Super-Admin', 'SUPERADMIN']);

test('an existing role cannot be renamed to a reserved superadmin name', function () {
    $superadmin = User::factory()->superadmin()->create();
    $role = Role::findOrCreate('editor', 'web');

    $this->actingAs($superadmin)
        ->put("/roles/{$role->id}", ['name' => 'superadmin'])
        ->assertSessionHasErrors('name');

    expect($role->refresh()->name)->toBe('editor');
});
