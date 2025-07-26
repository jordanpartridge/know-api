<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController
{
    public function __invoke(Request $request)
    {
        return new UserResource($request->user());
    }
}
