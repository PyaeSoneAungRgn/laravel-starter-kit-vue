<?php

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(LazilyRefreshDatabase::class);

test('it creates a superadmin user', function () {
    $this->artisan('rbac:superadmin', [
        '--name' => 'Admin',
        '--email' => 'admin@example.com',
        '--password' => 'secret-password',
    ])->assertSuccessful();

    $user = User::query()->where('email', 'admin@example.com')->firstOrFail();

    expect($user->isSuperAdmin())->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('secret-password', $user->password))->toBeTrue();
});

test('it promotes an existing user by email', function () {
    $user = User::factory()->create(['email' => 'admin@example.com']);

    $this->artisan('rbac:superadmin', ['--user' => 'admin@example.com'])->assertSuccessful();

    expect($user->refresh()->isSuperAdmin())->toBeTrue();
});

test('it promotes an existing user by id', function () {
    $user = User::factory()->create();

    $this->artisan('rbac:superadmin', ['--user' => $user->id])->assertSuccessful();

    expect($user->refresh()->isSuperAdmin())->toBeTrue();
});

test('it is idempotent', function () {
    $user = User::factory()->superadmin()->create();

    $this->artisan('rbac:superadmin', ['--user' => $user->email])
        ->expectsOutputToContain('already a superadmin')
        ->assertSuccessful();

    expect($user->refresh()->isSuperAdmin())->toBeTrue();
});

test('it fails for an unknown user', function () {
    $this->artisan('rbac:superadmin', ['--user' => 'missing@example.com'])->assertFailed();
});

test('it rejects a password that does not meet the default rules', function () {
    $this->artisan('rbac:superadmin', [
        '--name' => 'Weak',
        '--email' => 'weak@example.com',
        '--password' => 'short',
    ])->assertFailed();

    expect(User::query()->where('email', 'weak@example.com')->exists())->toBeFalse();
});
