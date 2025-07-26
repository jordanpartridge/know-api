<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request)
    {
        $query = Knowledge::with(['user', 'gitContext', 'tags'])
            ->where('user_id', $request->user()->id);

        // Filter by type if provided
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by tag if provided
        if ($request->has('tag')) {
            $query->whereHas('tags', function ($q) use ($request) {
                $q->where('slug', $request->tag);
            });
        }

        // Search if query provided
        if ($request->has('search')) {
            $query->search($request->search);
        }

        $knowledge = $query->latest()->paginate(15);

        return KnowledgeResource::collection($knowledge);
    }
}
