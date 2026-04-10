<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemperatureLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'cold_storage_unit_id',
        'product_batch_id',
        'temperature',
        'humidity',
        'sensor_id',
        'recorded_by',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:2',
            'humidity' => 'decimal:2',
            'recorded_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function coldStorageUnit(): BelongsTo
    {
        return $this->belongsTo(ColdStorageUnit::class);
    }

    public function productBatch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class);
    }
}
