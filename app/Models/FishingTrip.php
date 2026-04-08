<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FishingTrip extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vessel_id',
        'captain_id',
        'fishing_zone_id',
        'trip_number',
        'departure_time',
        'return_time',
        'status',
        'fuel_consumed',
        'total_catch_weight',
        'latitude',
        'longitude',
        'weather_conditions',
        'notes',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'return_time' => 'datetime',
        'fuel_consumed' => 'decimal:2',
        'total_catch_weight' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vessel()
    {
        return $this->belongsTo(FishingVessel::class, 'vessel_id');
    }

    public function captain()
    {
        return $this->belongsTo(Employee::class, 'captain_id');
    }

    public function fishingZone()
    {
        return $this->belongsTo(FishingZone::class, 'fishing_zone_id');
    }

    public function crew()
    {
        return $this->belongsToMany(Employee::class, 'fishing_trip_crew')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function catchLogs()
    {
        return $this->hasMany(CatchLog::class);
    }

    public function mortalityLogs()
    {
        return $this->hasMany(MortalityLog::class);
    }

    public function duration(): ?int
    {
        if (!$this->return_time) {
            return null;
        }

        return $this->departure_time->diffInHours($this->return_time);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['planned', 'departed', 'fishing', 'returning']);
    }
}
