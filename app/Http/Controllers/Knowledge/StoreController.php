<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Requests\StoreKnowledgeRequest;
use App\Http\Resources\KnowledgeResource;
use App\Models\GitContext;
use App\Models\Knowledge;

class StoreController
{
    public function __invoke(StoreKnowledgeRequest $request)
    {
        $validated = $request->validated();

        // Handle git context if provided
        $gitContextId = null;
        if (isset($validated['git_context'])) {
            $gitContext = GitContext::firstOrCreate(
                [
                    'repository_name' => $validated['git_context']['repository_name'] ?? null,
                    'branch_name' => $validated['git_context']['branch_name'] ?? null,
                ],
                $validated['git_context']
            );
            $gitContextId = $gitContext->id;
        }

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
