<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use Illuminate\Http\Request;

class ShowController
{
    public function __invoke(Request $request, Knowledge $knowledge): KnowledgeResource
    {
        return new KnowledgeResource($knowledge->load(['user', 'gitContext', 'tags']));
    }
}
