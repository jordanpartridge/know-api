<?php

namespace App\Models;

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

    public function scopeByRepository($query, string $repository)
    {
        return $query->where('repository_name', $repository);
    }

    public function scopeByBranch($query, string $branch)
    {
        return $query->where('branch_name', $branch);
    }
}
