<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ColdStorageUnit extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'unit_code',
        'name',
        'type',
        'capacity',
        'current_temperature',
        'min_temperature',
        'max_temperature',
        'location',
        'sensor_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'decimal:2',
            'current_temperature' => 'decimal:2',
            'min_temperature' => 'decimal:2',
            'max_temperature' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public const TYPES = [
        'warehouse' => 'Warehouse',
        'transport' => 'Transport',
        'display' => 'Display',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function temperatureLogs(): HasMany
    {
        return $this->hasMany(TemperatureLog::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(ColdChainAlert::class);
    }

    public function latestTemperatureLog()
    {
        return $this->hasOne(TemperatureLog::class)->latestOfMany();
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Check if temperature is within safe range
     */
    public function isTemperatureSafe(): bool
    {
        if ($this->current_temperature === null) {
            return false;
        }

        return $this->current_temperature >= $this->min_temperature
            && $this->current_temperature <= $this->max_temperature;
    }

    /**
     * Get utilization percentage (mock - based on capacity)
     */
    public function getUtilizationPercentageAttribute(): float
    {
        // This is a placeholder - actual utilization would come from inventory tracking
        return 0;
    }

    /**
     * Generate unique unit code
     */
    public static function generateCode(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;

        return 'CSU-'.str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
