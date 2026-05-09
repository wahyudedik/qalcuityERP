<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserWidgetPreference extends Model
{
    use AuditsChanges, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'page',
        'widget_type',
        'widget_config',
        'position',
        'is_active',
    ];

    protected $casts = [
        'widget_config' => 'array',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function layoutPreference(): BelongsTo
    {
        return $this->belongsTo(UserLayoutPreference::class, 'user_id', 'user_id')
            ->where('page', $this->page);
    }

    public function scopeForPage(Builder $query, string $page): Builder
    {
        return $query->where('page', $page);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position', 'asc');
    }
}
