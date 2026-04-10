<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingVessel extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vessel_name',
        'registration_number',
        'vessel_type',
        'gross_tonnage',
        'crew_capacity',
        'fuel_capacity',
        'storage_capacity',
        'home_port',
        'license_expiry_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gross_tonnage' => 'decimal:2',
            'fuel_capacity' => 'decimal:2',
            'storage_capacity' => 'decimal:2',
            'license_expiry_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = [
        'fishing_boat' => 'Fishing Boat',
        'trawler' => 'Trawler',
        'longliner' => 'Longliner',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(FishingTrip::class, 'vessel_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->vessel_type] ?? $this->vessel_type;
    }
}
