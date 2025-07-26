<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Requests\StoreKnowledgeRequest;
use App\Http\Resources\KnowledgeResource;
use App\Models\Knowledge;
use App\Services\GitContextService;

class UpdateController
{
    public function __invoke(StoreKnowledgeRequest $request, Knowledge $knowledge, GitContextService $gitContextService): KnowledgeResource
    {
        $validated = $request->validated();

        // Handle git context if provided
        $gitContextId = $gitContextService->findOrCreateContext($validated['git_context'] ?? null);
        if ($gitContextId) {
            $validated['git_context_id'] = $gitContextId;
        }

        unset($validated['git_context'], $validated['tag_ids']);

        $knowledge->update($validated);

        // Sync tags if provided
        if (isset($request->validated()['tag_ids'])) {
            $knowledge->tags()->sync($request->validated()['tag_ids']);
        }

        return new KnowledgeResource($knowledge->load(['user', 'gitContext', 'tags']));
    }
}
