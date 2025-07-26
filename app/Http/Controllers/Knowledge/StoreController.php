<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Requests\StoreKnowledgeRequest;
use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use App\Services\GitContextService;

class StoreController
{
    public function __invoke(StoreKnowledgeRequest $request, GitContextService $gitContextService)
    {
        $validated = $request->validated();

        // Handle git context if provided
        $gitContextId = $gitContextService->findOrCreateContext($validated['git_context'] ?? null);

        $knowledge = Knowledge::create([
            'user_id' => $request->user()->id,
            'git_context_id' => $gitContextId,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'summary' => $validated['summary'] ?? null,
            'type' => $validated['type'] ?? 'note',
            'metadata' => $validated['metadata'] ?? null,
            'is_public' => $validated['is_public'] ?? false,
        ]);

        // Attach tags if provided
        if (isset($validated['tag_ids'])) {
            $knowledge->tags()->attach($validated['tag_ids']);
        }

        return new KnowledgeResource($knowledge->load(['user', 'gitContext', 'tags']));
    }
}
