<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ColdChainAlert extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'cold_storage_unit_id',
        'temperature_log_id',
        'alert_type',
        'severity',
        'message',
        'recorded_temperature',
        'threshold_min',
        'threshold_max',
        'is_acknowledged',
        'acknowledged_by_user_id',
        'acknowledged_at',
        'resolution_notes',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'recorded_temperature' => 'decimal:2',
            'threshold_min' => 'decimal:2',
            'threshold_max' => 'decimal:2',
            'is_acknowledged' => 'boolean',
            'acknowledged_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public const SEVERITIES = [
        'warning' => 'Warning',
        'critical' => 'Critical',
        'emergency' => 'Emergency',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function coldStorageUnit(): BelongsTo
    {
        return $this->belongsTo(ColdStorageUnit::class);
    }

    public function temperatureLog(): BelongsTo
    {
        return $this->belongsTo(TemperatureLog::class);
    }

    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by_user_id');
    }

    public function severityLabel(): string
    {
        return self::SEVERITIES[$this->severity] ?? $this->severity;
    }
}
