<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController
{
    public function __invoke(RegisterRequest $request): UserResource
    {
        $validated = $request->validated();

        $user = User::withInactive()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        return new UserResource($user);
    }
}
