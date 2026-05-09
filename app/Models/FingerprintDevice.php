<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FingerprintDevice extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'device_id',
        'ip_address',
        'port',
        'protocol',
        'vendor',
        'model',
        'api_key',
        'secret_key',
        'is_active',
        'is_connected',
        'last_sync_at',
        'config',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_connected' => 'boolean',
            'last_sync_at' => 'datetime',
            'config' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function attendanceLogs(): HasMany
    {
        return $this->hasMany(FingerprintAttendanceLog::class, 'device_id');
    }

    /**
     * Get the connection configuration for this device
     */
    public function getConnectionConfig(): array
    {
        return [
            'ip' => $this->ip_address,
            'port' => (int) $this->port,
            'protocol' => $this->protocol,
            'vendor' => $this->vendor,
            'api_key' => $this->api_key,
            'secret_key' => $this->secret_key,
            'config' => $this->config ?? [],
        ];
    }

    /**
     * Check if device is configured properly
     */
    public function isConfigured(): bool
    {
        return ! empty($this->ip_address) && ! empty($this->port);
    }
}
