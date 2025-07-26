<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can logout successfully', function () {
    $user = User::factory()->create();
    $token = $user->createToken('auth_token')->plainTextToken;

    $response = $this->postJson('/api/v1/auth/logout', [], [
        'Authorization' => 'Bearer ' . $token,
    ]);

    $response->assertOk()
        ->assertJson([
            'message' => 'Successfully logged out',
        ]);

    // Verify user has no more tokens after logout
    expect($user->fresh()->tokens()->count())->toBe(0);
});

it('enforces rate limiting on login attempts', function () {
    $user = User::factory()->create();

    // Make 6 login attempts (exceeds 5 per minute limit)
    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        if ($i < 5) {
            $response->assertUnprocessable(); // Wrong password
        } else {
            $response->assertStatus(429); // Rate limited
        }
    }
});

it('enforces rate limiting on register attempts', function () {
    // Make 6 register attempts (exceeds 5 per minute limit)
    for ($i = 0; $i < 6; $i++) {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User ' . $i,
            'email' => 'test' . $i . '@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        if ($i < 5) {
            $response->assertCreated(); // Successful registration
        } else {
            $response->assertStatus(429); // Rate limited
        }
    }
});
