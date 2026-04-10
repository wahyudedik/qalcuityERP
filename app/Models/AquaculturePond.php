<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AquaculturePond extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pond_code',
        'pond_name',
        'surface_area',
        'depth',
        'volume',
        'pond_type',
        'water_source',
        'current_stock',
        'carrying_capacity',
        'current_species_id',
        'stocking_date',
        'expected_harvest_date',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'surface_area' => 'decimal:2',
            'depth' => 'decimal:2',
            'volume' => 'decimal:2',
            'current_stock' => 'decimal:2',
            'carrying_capacity' => 'decimal:2',
            'stocking_date' => 'date',
            'expected_harvest_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = [
        'earthen' => 'Earthen Pond',
        'concrete' => 'Concrete Pond',
        'tarpaulin' => 'Tarpaulin Pond',
        'cage' => 'Cage Culture',
    ];

    public const STATUSES = [
        'empty' => 'Empty',
        'stocked' => 'Stocked',
        'growing' => 'Growing',
        'ready_harvest' => 'Ready for Harvest',
        'maintenance' => 'Maintenance',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function currentSpecies(): BelongsTo
    {
        return $this->belongsTo(FishSpecies::class, 'current_species_id');
    }

    public function waterQualityLogs(): HasMany
    {
        return $this->hasMany(WaterQualityLog::class, 'pond_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->pond_type] ?? $this->pond_type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Calculate utilization percentage
     */
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->carrying_capacity <= 0) {
            return 0;
        }

        return round(($this->current_stock / $this->carrying_capacity) * 100, 2);
    }
}
