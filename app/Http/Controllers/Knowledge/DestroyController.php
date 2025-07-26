<?php

namespace App\Http\Controllers\Knowledge;

use App\Models\Knowledge;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class DestroyController
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Knowledge $knowledge)
    {
        $this->authorize('delete', $knowledge);

        $knowledge->delete();

        return response()->json(['message' => 'Knowledge deleted successfully']);
    }
}
