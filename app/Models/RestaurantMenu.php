<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RestaurantMenu extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'type',
        'available_from',
        'available_until',
        'is_active',
        'display_order',
    ];

    protected function casts(): array
    {
        return [
            'available_from' => 'datetime:H:i',
            'available_until' => 'datetime:H:i',
            'is_active' => 'boolean',
            'display_order' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Check if menu is currently available
     */
    public function isCurrentlyAvailable(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->available_from && $this->available_until) {
            $now = now()->format('H:i');

            return $now >= $this->available_from->format('H:i') &&
                $now <= $this->available_until->format('H:i');
        }

        return true;
    }
}
