<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class SavedSearch extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'query',
        'type',
        'filters',
        'module',
        'use_count',
        'last_used_at',
        'is_public',
    ];

    protected $casts = [
        'filters' => 'array',
        'is_public' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user who saved this search
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Increment use count and update last used timestamp
     */
    public function markAsUsed(): void
    {
        $this->increment('use_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Scope: searches for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId)
            ->orWhere('is_public', true);
    }

    /**
     * Scope: most frequently used searches
     */
    public function scopeMostUsed($query, $limit = 10)
    {
        return $query->orderByDesc('use_count')->limit($limit);
    }

    /**
     * Scope: recently used searches
     */
    public function scopeRecentlyUsed($query, $limit = 10)
    {
        return $query->whereNotNull('last_used_at')
            ->orderByDesc('last_used_at')
            ->limit($limit);
    }

    /**
     * Clear user's saved searches cache
     */
    public static function clearUserCache($userId): void
    {
        Cache::forget("saved_searches:user:{$userId}");
    }
}
