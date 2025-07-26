<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KnowledgeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'summary' => $this->summary,
            'type' => $this->type,
            'metadata' => $this->metadata,
            'is_public' => $this->is_public,
            'captured_at' => $this->captured_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => new UserResource($this->whenLoaded('user')),
            'git_context' => $this->whenLoaded('gitContext', function () {
                return [
                    'id' => $this->gitContext->id,
                    'repository_name' => $this->gitContext->repository_name,
                    'branch_name' => $this->gitContext->branch_name,
                    'commit_hash' => $this->gitContext->commit_hash,
                ];
            }),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
