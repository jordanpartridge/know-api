<?php

namespace App\Services;

use App\Models\GitContext;

class GitContextService
{
    /**
     * Find or create a git context based on repository and branch.
     *
     * @param array<string, mixed>|null $gitData
     */
    public function findOrCreateContext(?array $gitData): ?int
    {
        if (! $gitData) {
            return null;
        }

        $gitContext = GitContext::firstOrCreate(
            [
                'repository_name' => $gitData['repository_name'] ?? null,
                'branch_name' => $gitData['branch_name'] ?? null,
            ],
            $gitData
        );

        return $gitContext->id;
    }
}
