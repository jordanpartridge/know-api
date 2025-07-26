<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class ShowController
{
    use AuthorizesRequests;

    public function __invoke(Request $request, Knowledge $knowledge)
    {
        $this->authorize('view', $knowledge);

        return new KnowledgeResource($knowledge->load(['user', 'gitContext', 'tags']));
    }
}
