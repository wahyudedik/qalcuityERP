<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RefrigeratedTransport extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'vehicle_number',
        'vehicle_type',
        'capacity',
        'min_temperature',
        'max_temperature',
        'sensor_id',
        'driver_name',
        'driver_phone',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'min_temperature' => 'decimal:2',
            'max_temperature' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public const VEHICLE_TYPES = [
        'truck' => 'Truck',
        'van' => 'Van',
        'container' => 'Container',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(ExportShipment::class, 'transport_id');
    }

    public function getVehicleTypeLabel(): string
    {
        return self::VEHICLE_TYPES[$this->vehicle_type] ?? $this->vehicle_type;
    }
}
