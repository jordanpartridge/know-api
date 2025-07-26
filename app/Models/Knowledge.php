<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @use HasFactory<\Database\Factories\KnowledgeFactory>
 */
class Knowledge extends Model
{
    /** @use HasFactory<\Database\Factories\KnowledgeFactory> */
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

    /**
     * @param  Builder<Knowledge>  $query
     * @return Builder<Knowledge>
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * @param  Builder<Knowledge>  $query
     * @return Builder<Knowledge>
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * @param  Builder<Knowledge>  $query
     * @return Builder<Knowledge>
     */
    public function scopeSearch(Builder $query, string $search): Builder
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
