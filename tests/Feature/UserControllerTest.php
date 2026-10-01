<?php

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('returns ten users per page', function () {
    User::factory()->count(12)->create();

    $this->getJson('/api/users')
        ->assertOk()
        ->assertJsonCount(10, 'data')
        ->assertJsonPath('current_page', 1)
        ->assertJsonPath('total', 12);
});

test('creates a user from the username email and password', function () {
    $this->postJson('/api/users/create', [
        'username' => 'Ada Lovelace',
        'email' => 'ada@example.test',
        'password' => 'secret123',
    ])
        ->assertCreated()
        ->assertJsonPath('user.name', 'Ada Lovelace')
        ->assertJsonPath('user.email', 'ada@example.test')
        ->assertJsonMissingPath('user.password');

    $user = User::where('email', 'ada@example.test')->firstOrFail();

    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

test('returns user data when login credentials are valid', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->postJson('/api/users/login', [
        'email' => 'ada@example.test',
        'password' => 'secret123',
    ])
        ->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->assertJsonMissingPath('user.password');
});

test('returns 401 when login credentials are invalid', function () {
    User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->postJson('/api/users/login', [
        'email' => 'ada@example.test',
        'password' => 'wrong-password',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Email o contraseña incorrectos');
});

test('updates a username after validating the current credentials', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->putJson('/api/users/update_username', [
        'email' => 'ada@example.test',
        'password' => 'secret123',
        'username' => 'Ada Byron',
    ])
        ->assertOk()
        ->assertJsonPath('user.name', 'Ada Byron');

    expect($user->fresh()->name)->toBe('Ada Byron');
});

test('updates an email after validating the current credentials', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->putJson('/api/users/update_email', [
        'email' => 'ada@example.test',
        'password' => 'secret123',
        'new_email' => 'ada.byron@example.test',
    ])->assertOk();

    expect($user->fresh()->email)->toBe('ada.byron@example.test');
});

test('updates a password after validating the current credentials', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->putJson('/api/users/update_password', [
        'email' => 'ada@example.test',
        'password' => 'secret123',
        'new_password' => 'new-secret123',
    ])->assertOk();

    expect(Hash::check('new-secret123', $user->fresh()->password))->toBeTrue();
});

test('deletes a user after validating the current credentials', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'password' => Hash::make('secret123'),
    ]);

    $this->deleteJson('/api/users/delete', [
        'email' => 'ada@example.test',
        'password' => 'secret123',
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Usuario eliminado correctamente');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('returns 401 and preserves the user when update credentials are invalid', function () {
    $user = User::factory()->create([
        'email' => 'ada@example.test',
        'name' => 'Ada Lovelace',
        'password' => Hash::make('secret123'),
    ]);

    $this->putJson('/api/users/update_username', [
        'email' => 'ada@example.test',
        'password' => 'wrong-password',
        'username' => 'Ada Byron',
    ])
        ->assertUnauthorized()
        ->assertJsonPath('message', 'Email o contraseña incorrectos');

    expect($user->fresh()->name)->toBe('Ada Lovelace');
});

test('returns validation errors when required creation data is missing', function () {
    $this->postJson('/api/users/create')
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['username', 'email', 'password']);
});

test('seeds at least two users', function () {
    $this->seed(UserSeeder::class);

    $this->assertDatabaseCount('users', 2);
});
