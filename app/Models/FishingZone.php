<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingZone extends Model
{
    use AuditsChanges;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'zone_code',
        'zone_name',
        'coordinates',
        'area_size',
        'water_type',
        'allowed_species',
        'quota_limit',
        'season_start',
        'season_end',
        'regulations',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'coordinates' => 'array',
            'allowed_species' => 'array',
            'area_size' => 'decimal:2',
            'quota_limit' => 'decimal:2',
            'season_start' => 'date',
            'season_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'fishing_zone_id');
    }

    /**
     * Scope: Active zones only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Search by name or code
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('zone_name', 'like', "%{$search}%")
                ->orWhere('zone_code', 'like', "%{$search}%");
        });
    }
}
