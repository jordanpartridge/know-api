<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKnowledgeRequest;
use App\Http\Resources\KnowledgeResource;
use App\Models\GitContext;
use App\Models\Knowledge;
use Illuminate\Http\Request;

class KnowledgeController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreKnowledgeRequest $request)
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

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Knowledge $knowledge)
    {
        // Check if user owns this knowledge or if it's public
        if ($knowledge->user_id !== $request->user()->id && ! $knowledge->is_public) {
            abort(403, 'You do not have permission to view this knowledge.');
        }

        return new KnowledgeResource($knowledge->load(['user', 'gitContext', 'tags']));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreKnowledgeRequest $request, Knowledge $knowledge)
    {
        // Check if user owns this knowledge
        if ($knowledge->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to update this knowledge.');
        }

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

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Knowledge $knowledge)
    {
        // Check if user owns this knowledge
        if ($knowledge->user_id !== $request->user()->id) {
            abort(403, 'You do not have permission to delete this knowledge.');
        }

        $knowledge->delete();

        return response()->json(['message' => 'Knowledge deleted successfully']);
    }
}
