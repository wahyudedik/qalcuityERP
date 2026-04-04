<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FingerprintAttendanceLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'device_id',
        'employee_uid',
        'employee_id',
        'scan_time',
        'scan_type',
        'is_processed',
        'processed_at',
        'raw_data',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'scan_time' => 'datetime',
            'is_processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class, 'device_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
