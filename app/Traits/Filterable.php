<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

/**
 * Filterable Trait
 * 
 * Provides advanced filtering capabilities to Eloquent models.
 * Usage: Add `use Filterable;` to your model
 * 
 * Example:
 * Product::filter(['status' => 'active', 'search' => 'laptop'])->get();
 */
trait Filterable
{
    /**
     * Apply filters to query
     *
     * @param Builder $query
     * @param array $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            match ($field) {
                'search' => $this->applySearchFilter($query, $value),
                'status' => $query->where('status', $value),
                'date_from', 'start_date' => $query->whereDate('created_at', '>=', $value),
                'date_to', 'end_date' => $query->whereDate('created_at', '<=', $value),
                'category', 'category_id' => $query->where('category_id', $value),
                'min_amount', 'amount_from' => $query->where('amount', '>=', $value),
                'max_amount', 'amount_to' => $query->where('amount', '<=', $value),
                'is_active', 'active' => $query->where('is_active', filter_var($value, FILTER_VALIDATE_BOOLEAN)),
                default => $this->applyCustomFilter($query, $field, $value),
            };
        }

        return $query;
    }

    /**
     * Apply search filter across multiple columns
     */
    protected function applySearchFilter(Builder $query, string $search): Builder
    {
        $searchableColumns = $this->getSearchableColumns();

        return $query->where(function ($q) use ($search, $searchableColumns) {
            foreach ($searchableColumns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Apply custom filter (override in model if needed)
     */
    protected function applyCustomFilter(Builder $query, string $field, $value): Builder
    {
        // Default: apply simple where clause
        return $query->where($field, $value);
    }

    /**
     * Get searchable columns for search filter
     * Override this method in your model to customize
     */
    protected function getSearchableColumns(): array
    {
        // Default columns - override in model
        return ['name', 'title', 'description', 'number', 'code'];
    }

    /**
     * Filter by date range
     */
    public function scopeDateRange(Builder $query, $from, $to, string $column = 'created_at'): Builder
    {
        if ($from) {
            $query->whereDate($column, '>=', $from);
        }

        if ($to) {
            $query->whereDate($column, '<=', $to);
        }

        return $query;
    }

    /**
     * Filter by status array (multiple statuses)
     */
    public function scopeStatusIn(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    /**
     * Filter by ID array
     */
    public function scopeIds(Builder $query, array $ids): Builder
    {
        return $query->whereIn('id', $ids);
    }

    /**
     * Save filter preset
     */
    public function saveFilterPreset($userId, string $name, array $filters): bool
    {
        $preset = [
            'user_id' => $userId,
            'model' => static::class,
            'name' => $name,
            'filters' => $filters,
            'created_at' => now(),
        ];

        // Save to cache (30 days TTL)
        return Cache::put("filter_preset:{$userId}:{$name}", $preset, now()->addDays(30));
    }

    /**
     * Load filter preset
     */
    public static function loadFilterPreset($userId, string $name): ?array
    {
        $preset = Cache::get("filter_preset:{$userId}:{$name}");
        return $preset['filters'] ?? null;
    }

    /**
     * Get all filter presets for user
     */
    public static function getFilterPresets($userId): array
    {
        $presets = [];
        $cacheKey = "filter_preset:{$userId}:*";

        // This would need a proper cache driver that supports wildcards
        // For now, return empty array
        return $presets;
    }
}
