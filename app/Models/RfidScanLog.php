<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfidScanLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'tag_id',
        'scanner_device_id',
        'location_id',
        'warehouse_id',
        'scan_type', // check_in, check_out, transfer, audit, movement
        'scanned_by',
        'scan_time',
        'latitude',
        'longitude',
        'additional_data',
    ];

    protected function casts(): array
    {
        return [
            'scan_time' => 'datetime',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'additional_data' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tag(): BelongsTo
    {
        return $this->belongsTo(RfidTag::class, 'tag_id');
    }

    public function scannerDevice(): BelongsTo
    {
        return $this->belongsTo(RfidScannerDevice::class, 'scanner_device_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseBin::class, 'location_id');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function scannedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }
}
