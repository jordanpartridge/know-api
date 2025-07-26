<?php

namespace App\Http\Controllers\Knowledge;

use App\Http\Requests\StoreKnowledgeRequest;
use App\Http\Resources\KnowledgeResource;
use App\Models\GitContext;
use App\Models\Knowledge;

class UpdateController
{
    public function __invoke(StoreKnowledgeRequest $request, Knowledge $knowledge)
    {
        $validated = $request->validated();

        // Handle git context if provided
        if (isset($validated['git_context'])) {
            $gitContext = GitContext::firstOrCreate(
                [
                    'repository_name' => $validated['git_context']['repository_name'] ?? null,
                    'branch_name' => $validated['git_context']['branch_name'] ?? null,
                ],
                $validated['git_context']
            );
            $validated['git_context_id'] = $gitContext->id;
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
