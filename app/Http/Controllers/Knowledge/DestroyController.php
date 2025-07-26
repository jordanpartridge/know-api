<?php

namespace App\Http\Controllers\Knowledge;

use App\Models\Knowledge;
use Illuminate\Http\Request;

class DestroyController
{
    public function __invoke(Request $request, Knowledge $knowledge): \Illuminate\Http\JsonResponse
    {
        $knowledge->delete();

        return response()->json(['message' => 'Knowledge deleted successfully']);
    }
}
