<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Knowledge extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'git_context_id',
        'title',
        'content',
        'summary',
        'type',
        'metadata',
        'is_public',
        'captured_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'captured_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gitContext(): BelongsTo
    {
        return $this->belongsTo(GitContext::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, string $search)
    {
        // Use fulltext search for MySQL/PostgreSQL, LIKE for SQLite
        if (config('database.default') === 'sqlite') {
            return $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->orWhere('summary', 'LIKE', "%{$search}%");
            });
        }

        return $query->whereFullText(['title', 'content', 'summary'], $search);
    }
}
