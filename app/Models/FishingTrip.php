<?php

namespace App\Models;

use App\Models\FishingZone;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FishingTrip extends Model
{
    use BelongsToTenant;

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
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'departure_time' => 'datetime',
            'return_time' => 'datetime',
            'fuel_consumed' => 'decimal:2',
            'total_catch_weight' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'planned' => 'Planned',
        'departed' => 'Departed',
        'fishing' => 'Fishing',
        'returning' => 'Returning',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(FishingVessel::class, 'vessel_id');
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'captain_id');
    }

    public function fishingZone(): BelongsTo
    {
        return $this->belongsTo(FishingZone::class, 'fishing_zone_id');
    }

    public function catchLogs(): HasMany
    {
        return $this->hasMany(CatchLog::class);
    }

    /**
     * Alias for catchLogs - used in views
     */
    public function catches(): HasMany
    {
        return $this->catchLogs();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }
}
