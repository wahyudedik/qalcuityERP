<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FleetTrip extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'vehicle_id', 'driver_id', 'user_id',
        'trip_number', 'purpose', 'origin', 'destination',
        'odometer_start', 'odometer_end', 'departed_at', 'returned_at',
        'status', 'reference_type', 'reference_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'departed_at' => 'datetime',
            'returned_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(FleetVehicle::class, 'vehicle_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(FleetDriver::class, 'driver_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function distanceKm(): ?int
    {
        return ($this->odometer_start && $this->odometer_end)
            ? $this->odometer_end - $this->odometer_start
            : null;
    }
}
