<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

class LogoutController
{
    public function __invoke(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }
}
