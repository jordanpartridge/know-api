<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class LogoutController
{
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        
        $user->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
