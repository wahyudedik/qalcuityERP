<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RfidTag extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'tag_uid',
        'tag_type', // rfid, nfc, barcode_qr
        'frequency', // LF (125kHz), HF (13.56MHz), UHF (860-960MHz)
        'protocol', // ISO14443A, ISO14443B, ISO15693, Mifare, etc
        'taggable_type',
        'taggable_id',
        'status', // active, inactive, lost, damaged
        'encoded_data',
        'is_encrypted',
        'assigned_to',
        'assigned_at',
        'last_scan_at',
        'scan_count',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_encrypted' => 'boolean',
            'assigned_at' => 'datetime',
            'last_scan_at' => 'datetime',
            'scan_count' => 'integer',
            'encoded_data' => 'encrypted', // Auto-encrypt sensitive data
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function taggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scans()
    {
        return $this->hasMany(RfidScanLog::class, 'tag_id');
    }

    /**
     * Assign tag to a model (Product, Asset, etc)
     */
    public function assignTo($model): void
    {
        $this->update([
            'taggable_type' => get_class($model),
            'taggable_id' => $model->id,
            'status' => 'active',
            'assigned_to' => auth()->id(),
            'assigned_at' => now(),
        ]);
    }

    /**
     * Deactivate tag
     */
    public function deactivate(): void
    {
        $this->update([
            'status' => 'inactive',
            'assigned_to' => null,
            'assigned_at' => null,
        ]);
    }

    /**
     * Record a scan
     */
    public function recordScan(array $data): RfidScanLog
    {
        $this->increment('scan_count');
        $this->update(['last_scan_at' => now()]);

        return RfidScanLog::create(array_merge([
            'tenant_id' => $this->tenant_id,
            'tag_id' => $this->id,
            'scanned_by' => auth()->id(),
            'scan_time' => now(),
        ], $data));
    }

    /**
     * Check if tag is assigned
     */
    public function isAssigned(): bool
    {
        return ! empty($this->taggable_type) && ! empty($this->taggable_id);
    }
}
