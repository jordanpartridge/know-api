<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\LoginResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request): LoginResource
    {
        $user = User::withInactive()->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (! $user->isActivated()) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not activated. Please contact support.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return new LoginResource((object) [
            'user' => $user,
            'token' => $token,
        ]);
    }
}
