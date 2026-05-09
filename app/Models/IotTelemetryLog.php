<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotTelemetryLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'iot_device_id',
        'sensor_type',
        'value',
        'unit',
        'payload',
        'status',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'value' => 'decimal:4',
            'recorded_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class, 'iot_device_id');
    }
}
