<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfidScannerDevice extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'device_id',
        'vendor', // Zebra, Honeywell, Impinj, etc
        'model',
        'scanner_type', // handheld, fixed, portal, mobile
        'frequency', // LF, HF, UHF
        'connection_type', // usb, bluetooth, wifi, ethernet
        'port',
        'ip_address',
        'is_active',
        'is_connected',
        'last_scan_at',
        'scan_count',
        'config',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_connected' => 'boolean',
            'last_scan_at' => 'datetime',
            'scan_count' => 'integer',
            'config' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(RfidScanLog::class, 'scanner_device_id');
    }

    /**
     * Increment scan count
     */
    public function recordScan(): void
    {
        $this->increment('scan_count');
        $this->update(['last_scan_at' => now(), 'is_connected' => true]);
    }
}
