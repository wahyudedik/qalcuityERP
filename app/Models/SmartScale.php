<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SmartScale extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'name',
        'device_id',
        'vendor',
        'model',
        'serial_number',
        'connection_type', // serial, usb, bluetooth, network
        'port', // COM port for serial/USB or IP address for network
        'baud_rate', // for serial connection
        'data_bits',
        'stop_bits',
        'parity',
        'max_capacity', // in grams
        'precision', // decimal places
        'unit', // g, kg, lb, oz
        'is_active',
        'is_connected',
        'last_reading',
        'last_sync_at',
        'config',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_connected' => 'boolean',
            'max_capacity' => 'decimal:2',
            'precision' => 'integer',
            'last_reading' => 'decimal:4',
            'last_sync_at' => 'datetime',
            'config' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function weighLogs(): HasMany
    {
        return $this->hasMany(ScaleWeighLog::class, 'scale_id');
    }

    /**
     * Get the connection configuration for this scale
     */
    public function getConnectionConfig(): array
    {
        return [
            'type' => $this->connection_type,
            'port' => $this->port,
            'baud_rate' => (int) ($this->baud_rate ?? 9600),
            'data_bits' => (int) ($this->data_bits ?? 8),
            'stop_bits' => (int) ($this->stop_bits ?? 1),
            'parity' => $this->parity ?? 'none',
            'vendor' => $this->vendor,
            'config' => $this->config ?? [],
        ];
    }

    /**
     * Check if scale is configured properly
     */
    public function isConfigured(): bool
    {
        return !empty($this->port);
    }

    /**
     * Convert weight to specified unit
     */
    public function convertWeight(float $weight, string $toUnit): float
    {
        $fromUnit = $this->unit;

        // Convert to grams first
        $weightInGrams = match ($fromUnit) {
            'kg' => $weight * 1000,
            'lb' => $weight * 453.592,
            'oz' => $weight * 28.3495,
            default => $weight, // already in grams
        };

        // Convert from grams to target unit
        return match ($toUnit) {
            'kg' => $weightInGrams / 1000,
            'lb' => $weightInGrams / 453.592,
            'oz' => $weightInGrams / 28.3495,
            default => $weightInGrams,
        };
    }
}
