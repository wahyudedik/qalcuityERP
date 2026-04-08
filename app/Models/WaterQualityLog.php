<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WaterQualityLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pond_id',
        'fishing_zone_id',
        'ph_level',
        'dissolved_oxygen',
        'temperature',
        'salinity',
        'ammonia',
        'nitrite',
        'nitrate',
        'turbidity',
        'measurement_method',
        'measured_by_user_id',
        'measured_at',
        'notes',
    ];

    protected $casts = [
        'ph_level' => 'decimal:2',
        'dissolved_oxygen' => 'decimal:2',
        'temperature' => 'decimal:2',
        'salinity' => 'decimal:2',
        'ammonia' => 'decimal:2',
        'nitrite' => 'decimal:2',
        'nitrate' => 'decimal:2',
        'turbidity' => 'decimal:2',
        'measured_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pond()
    {
        return $this->belongsTo(AquaculturePond::class, 'pond_id');
    }

    public function fishingZone()
    {
        return $this->belongsTo(FishingZone::class, 'fishing_zone_id');
    }

    public function measuredBy()
    {
        return $this->belongsTo(User::class, 'measured_by_user_id');
    }

    public function isPhSafe(): bool
    {
        return $this->ph_level >= 6.5 && $this->ph_level <= 9.0;
    }

    public function isOxygenAdequate(): bool
    {
        return $this->dissolved_oxygen >= 5.0; // mg/L
    }
}
