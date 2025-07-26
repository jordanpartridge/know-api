<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has a working factory', function () {
    $user = User::factory()->create();

    expect($user)->toBeInstanceOf(User::class);
    expect($user->activated_at)->not->toBeNull();
});
