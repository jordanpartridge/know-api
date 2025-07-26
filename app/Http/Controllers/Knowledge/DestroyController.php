<?php

namespace App\Http\Controllers\Knowledge;

use App\Models\Knowledge;
use Illuminate\Http\Request;

class DestroyController
{
    public function __invoke(Request $request, Knowledge $knowledge)
    {
        // Check if user owns this knowledge
        if ($knowledge->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to delete this knowledge.');
        }

        $knowledge->delete();

        return response()->json(['message' => 'Knowledge deleted successfully']);
    }
}
