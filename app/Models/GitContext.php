<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GitContext extends Model
{
    protected $fillable = [
        'repository_url',
        'repository_name',
        'branch_name',
        'commit_hash',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function knowledge(): HasMany
    {
        return $this->hasMany(Knowledge::class);
    }

    /**
     * @param Builder<GitContext> $query
     * @return Builder<GitContext>
     */
    public function scopeByRepository(Builder $query, string $repository): Builder
    {
        return $query->where('repository_name', $repository);
    }

    /**
     * @param Builder<GitContext> $query
     * @return Builder<GitContext>
     */
    public function scopeByBranch(Builder $query, string $branch): Builder
    {
        return $query->where('branch_name', $branch);
    }
}
