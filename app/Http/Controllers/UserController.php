<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserController
{
    public function __invoke(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
