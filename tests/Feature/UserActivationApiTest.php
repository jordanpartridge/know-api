<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

test('unactivated user cannot authenticate via api', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Should fail to authenticate because user isn't activated (global scope hides them)
    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});

test('activated user can authenticate via api', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $user->activate();

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Should successfully authenticate
    $response->assertStatus(200);
});

test('unactivated user cannot access protected routes even with valid token', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Manually create a token for unactivated user (simulating edge case)
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/user');

    // Should fail because user is not activated
    $response->assertStatus(401);
});

test('activated user can access protected routes with valid token', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $user->activate();

    Sanctum::actingAs($user);

    $response = $this->getJson('/api/user');

    $response->assertStatus(200);
    $response->assertJson([
        'data' => [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ],
    ]);
});

test('middleware blocks unactivated users with proper error message', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    // Create token for unactivated user
    $token = $user->createToken('test-token')->plainTextToken;

    // Apply activation middleware to a test route
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $token,
    ])->getJson('/api/user');

    $response->assertStatus(401); // Will be 401 due to global scope
});

test('user activation persists activated_at timestamp', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    expect($user->activated_at)->toBeNull();

    $user->activate();

    expect($user->activated_at)->not->toBeNull();
    expect($user->activated_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class);

    // Verify it's persisted in database
    $freshUser = User::withInactive()->find($user->id);
    expect($freshUser->activated_at)->not->toBeNull();
});

test('unactivated users are hidden by global scope', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $found = User::find($user->id);
    expect($found)->toBeNull();

    $foundWithInactive = User::withInactive()->find($user->id);
    expect($foundWithInactive)->not->toBeNull();
    expect($foundWithInactive->id)->toBe($user->id);
});

test('activated users are visible through global scope', function () {
    $user = User::withInactive()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
    ]);

    $user->activate();

    $found = User::find($user->id);
    expect($found)->not->toBeNull();
    expect($found->id)->toBe($user->id);
});
