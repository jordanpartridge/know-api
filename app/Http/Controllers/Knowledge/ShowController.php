<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use Illuminate\Http\Request;

class ShowController
{
    public function __invoke(Request $request, Knowledge $knowledge)
    {
        // Check if user owns this knowledge or if it's public
        if ($knowledge->user_id !== $request->user()->id && ! $knowledge->is_public) {
            abort(403, 'You do not have permission to view this knowledge.');
        }

        return new KnowledgeResource($knowledge->load(['user', 'gitContext', 'tags']));
    }
}
