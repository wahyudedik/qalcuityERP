<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FishingZone extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'zone_code',
        'zone_name',
        'description',
        'coordinates',
        'area_size',
        'max_depth',
        'min_depth',
        'water_temperature',
        'salinity_level',
        'status',
        'allowed_species',
        'fishing_methods',
        'seasonal_restrictions',
        'permit_required',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'coordinates' => 'array',
            'allowed_species' => 'array',
            'fishing_methods' => 'array',
            'seasonal_restrictions' => 'array',
            'area_size' => 'decimal:2',
            'max_depth' => 'decimal:2',
            'min_depth' => 'decimal:2',
            'water_temperature' => 'decimal:2',
            'salinity_level' => 'decimal:2',
            'permit_required' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(FishingTrip::class);
    }

    /**
     * Scope: Active zones only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Search by name or code
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('zone_name', 'like', "%{$search}%")
                ->orWhere('zone_code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
