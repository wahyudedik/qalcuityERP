<?php

namespace App\Models;

use App\DTOs\Layout\PageLayout;
use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserLayoutPreference extends Model
{
    use AuditsChanges, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'page',
        'layout_config',
        'breakpoint_config',
    ];

    protected $casts = [
        'layout_config' => 'array',
        'breakpoint_config' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function widgetPreferences(): HasMany
    {
        return $this->hasMany(UserWidgetPreference::class, 'user_id', 'user_id')
            ->where('page', $this->page);
    }

    public function scopeForPage(Builder $query, string $page): Builder
    {
        return $query->where('page', $page);
    }

    /**
     * Mengambil konfigurasi layout sebagai PageLayout DTO.
     */
    public function getLayoutConfig(): ?PageLayout
    {
        if (empty($this->layout_config)) {
            return null;
        }

        return PageLayout::fromArray($this->layout_config);
    }

    /**
     * Menyimpan konfigurasi layout dari PageLayout DTO.
     */
    public function updateLayoutConfig(PageLayout $layout): bool
    {
        return $this->update([
            'layout_config' => $layout->toArray(),
        ]);
    }
}
