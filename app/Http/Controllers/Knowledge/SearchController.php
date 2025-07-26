<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use Illuminate\Http\Request;

class SearchController
{
    public function __invoke(Request $request): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $query = Knowledge::with(['user', 'gitContext', 'tags']);

        if ($request->has('q')) {
            $query->search($request->q);
        }

        // Can search public knowledge or user's own knowledge  
        $user = $request->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }
        
        $query->where(function ($q) use ($user) {
            $q->where('is_public', true)
                ->orWhere('user_id', $user->id);
        });

        $results = $query->latest()->paginate(10);

        return KnowledgeResource::collection($results);
    }
}
