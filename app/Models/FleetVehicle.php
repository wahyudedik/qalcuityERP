<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FleetVehicle extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'plate_number',
        'name',
        'type',
        'brand',
        'model',
        'year',
        'color',
        'vin',
        'asset_id',
        'status',
        'registration_expiry',
        'insurance_expiry',
        'odometer',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'registration_expiry' => 'date',
            'insurance_expiry' => 'date',
            'odometer' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function trips(): HasMany
    {
        return $this->hasMany(FleetTrip::class, 'vehicle_id');
    }

    public function fuelLogs(): HasMany
    {
        return $this->hasMany(FleetFuelLog::class, 'vehicle_id');
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(FleetMaintenance::class, 'vehicle_id');
    }

    public function activeDrivers()
    {
        return FleetDriver::whereIn('id', $this->trips()->whereNotNull('driver_id')->pluck('driver_id'))->get();
    }

    public function isExpiringSoon(string $field, int $days = 30): bool
    {
        return $this->{$field} && $this->{$field}->isBetween(now(), now()->addDays($days));
    }

    public function avgFuelConsumption(): ?float
    {
        $logs = $this->fuelLogs()->orderBy('odometer')->get();
        if ($logs->count() < 2) {
            return null;
        }
        $kmDiff = $logs->last()->odometer - $logs->first()->odometer;
        $totalLiters = $logs->sum('liters');

        return $kmDiff > 0 ? round($totalLiters / $kmDiff * 100, 1) : null; // L/100km
    }
}
