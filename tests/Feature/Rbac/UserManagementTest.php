<?php

use App\Models\User;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    forgetPermissionCache();
});

test('guests are redirected to the login page', function () {
    $this->get('/users')->assertRedirect('/login');
});

test('a superadmin can list users', function () {
    $superadmin = User::factory()->superadmin()->create();
    User::factory()->count(2)->create();

    $this->actingAs($superadmin)
        ->get('/users')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->component('users/Index', false)->has('users.data', 3));
});

test('a user without permission cannot list users', function () {
    $this->actingAs(User::factory()->create())
        ->get('/users')
        ->assertForbidden();
});

test('a user without permission cannot create users', function () {
    $this->actingAs(User::factory()->create())
        ->post('/users', [])
        ->assertForbidden();
});

test('a user with the view permission can list users', function () {
    $user = User::factory()->create();
    grantPermissions($user, ['view_any_user']);

    $this->actingAs($user->refresh())
        ->get('/users')
        ->assertSuccessful();
});

test('a superadmin can create a user with roles', function () {
    $superadmin = User::factory()->superadmin()->create();
    Role::findOrCreate('editor', 'web');

    $this->actingAs($superadmin)
        ->post('/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => ['editor'],
        ])
        ->assertRedirect('/users');

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->hasRole('editor'))->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();
});

test('store validates the user data', function () {
    $this->actingAs(User::factory()->superadmin()->create())
        ->post('/users', [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

test('the superadmin flag cannot be granted through the management screens', function () {
    $superadmin = User::factory()->superadmin()->create();

    $this->actingAs($superadmin)
        ->post('/users', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_superadmin' => true,
        ])
        ->assertRedirect('/users');

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();

    expect($user->isSuperAdmin())->toBeFalse();
});

test('a superadmin can update a user and their roles', function () {
    $superadmin = User::factory()->superadmin()->create();
    $user = User::factory()->create(['email' => 'old@example.com']);
    Role::findOrCreate('editor', 'web');

    $this->actingAs($superadmin)
        ->put("/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => 'new@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            'roles' => ['editor'],
        ])
        ->assertRedirect('/users');

    $user->refresh();

    expect($user->name)->toBe('Updated Name')
        ->and($user->email)->toBe('new@example.com')
        ->and($user->hasRole('editor'))->toBeTrue()
        ->and(Hash::check('new-password', $user->password))->toBeTrue();
});

test('the superadmin flag cannot be changed through the management screens', function () {
    $superadmin = User::factory()->superadmin()->create();
    $user = User::factory()->create();

    $this->actingAs($superadmin)
        ->put("/users/{$user->id}", [
            'name' => $user->name,
            'email' => $user->email,
            'is_superadmin' => true,
        ])
        ->assertRedirect('/users');

    $this->actingAs($superadmin)
        ->put("/users/{$superadmin->id}", [
            'name' => $superadmin->name,
            'email' => $superadmin->email,
            'is_superadmin' => false,
        ])
        ->assertRedirect('/users');

    expect($user->refresh()->isSuperAdmin())->toBeFalse()
        ->and($superadmin->refresh()->isSuperAdmin())->toBeTrue();
});

test('a non-superadmin cannot edit a superadmin', function () {
    $actor = User::factory()->create();
    grantPermissions($actor, ['update_user']);
    $superadmin = User::factory()->superadmin()->create();

    $this->actingAs($actor->refresh())
        ->get("/users/{$superadmin->id}/edit")
        ->assertForbidden();
});

test('no one can delete their own account from the management screen', function () {
    $superadmin = User::factory()->superadmin()->create();

    $this->actingAs($superadmin)
        ->from('/users')
        ->delete("/users/{$superadmin->id}")
        ->assertRedirect('/users')
        ->assertSessionHasErrors('user');

    $this->assertModelExists($superadmin);
});

test('a superadmin can delete another user', function () {
    $superadmin = User::factory()->superadmin()->create();
    $user = User::factory()->create();

    $this->actingAs($superadmin)
        ->delete("/users/{$user->id}")
        ->assertRedirect('/users');

    $this->assertModelMissing($user);
});

test('a non-superadmin does not see superadmin users in the list', function () {
    $actor = User::factory()->create();
    grantPermissions($actor, ['view_any_user']);
    $superadmin = User::factory()->superadmin()->create(['email' => 'root@example.com']);
    User::factory()->create();

    $this->actingAs($actor->refresh())
        ->get('/users')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 2)
            ->where('users.data.0.is_superadmin', false)
            ->where('users.data.1.is_superadmin', false));

    $this->actingAs($actor->refresh())
        ->get('/users?search=root@example.com')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('users.data', 0));

    $this->assertModelExists($superadmin);
});

test('users can be searched by name or email', function () {
    $superadmin = User::factory()->superadmin()->create();
    User::factory()->create(['name' => 'Searchable Person']);
    User::factory()->create(['name' => 'Someone Else']);

    $this->actingAs($superadmin)
        ->get('/users?search=Searchable')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page->has('users.data', 1));
});

test('the superadmin flag cannot be mass assigned', function () {
    expect(fn () => (new User)->fill([
        'name' => 'Escalated',
        'email' => 'escalated@example.com',
        'password' => 'password',
        'is_superadmin' => true,
    ]))->toThrow(MassAssignmentException::class);
});

test('the superadmin flag fails closed when the column is not loaded', function () {
    $superadmin = User::factory()->superadmin()->create();

    $partial = User::query()->select('id')->whereKey($superadmin->id)->firstOrFail();

    expect($partial->isSuperAdmin())->toBeFalse();
});

test('a superadmin sees other superadmins in the list', function () {
    $superadmin = User::factory()->superadmin()->create();
    User::factory()->superadmin()->create();

    $this->actingAs($superadmin)
        ->get('/users')
        ->assertSuccessful()
        ->assertInertia(fn ($page) => $page
            ->has('users.data', 2)
            ->where('users.data.0.is_superadmin', true)
            ->where('users.data.1.is_superadmin', true));
});
