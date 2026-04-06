<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BatchQualityCheck extends Model
{
    protected $fillable = [
        'tenant_id',
        'batch_id',
        'check_point',
        'parameter',
        'target_value',
        'actual_value',
        'lower_limit',
        'upper_limit',
        'result',
        'observations',
        'checked_by',
        'checked_at',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'actual_value' => 'decimal:2',
        'lower_limit' => 'decimal:2',
        'upper_limit' => 'decimal:2',
        'checked_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(CosmeticBatchRecord::class, 'batch_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_by');
    }

    /**
     * Result helpers
     */
    public function isPending(): bool
    {
        return $this->result === 'pending';
    }

    public function isPassed(): bool
    {
        return $this->result === 'pass';
    }

    public function isFailed(): bool
    {
        return $this->result === 'fail';
    }

    /**
     * Get result label
     */
    public function getResultLabelAttribute(): string
    {
        return match ($this->result) {
            'pending' => 'Pending',
            'pass' => 'Pass',
            'fail' => 'Fail',
            default => ucfirst($this->result)
        };
    }

    /**
     * Get result color
     */
    public function getResultColorAttribute(): string
    {
        return match ($this->result) {
            'pending' => 'yellow',
            'pass' => 'green',
            'fail' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get check point label
     */
    public function getCheckPointLabelAttribute(): string
    {
        return match ($this->check_point) {
            'mixing' => 'Mixing',
            'filling' => 'Filling',
            'packaging' => 'Packaging',
            'final' => 'Final QC',
            default => ucfirst($this->check_point)
        };
    }

    /**
     * Check if value is within limits
     */
    public function isWithinLimits(): bool
    {
        if (!$this->actual_value || !$this->lower_limit || !$this->upper_limit) {
            return true; // No limits set
        }

        return $this->actual_value >= $this->lower_limit
            && $this->actual_value <= $this->upper_limit;
    }

    /**
     * Calculate deviation from target
     */
    public function getDeviationAttribute(): ?float
    {
        if (!$this->target_value || !$this->actual_value) {
            return null;
        }

        return round($this->actual_value - $this->target_value, 2);
    }

    /**
     * Get deviation percentage
     */
    public function getDeviationPercentageAttribute(): ?float
    {
        if (!$this->target_value || $this->target_value == 0) {
            return null;
        }

        return round(
            (($this->actual_value - $this->target_value) / $this->target_value) * 100,
            2
        );
    }

    /**
     * Pass the check
     */
    public function pass(int $userId, string $observations = ''): void
    {
        $this->result = 'pass';
        $this->checked_by = $userId;
        $this->checked_at = now();
        if ($observations) {
            $this->observations = $observations;
        }
        $this->save();
    }

    /**
     * Fail the check
     */
    public function fail(int $userId, string $observations = ''): void
    {
        $this->result = 'fail';
        $this->checked_by = $userId;
        $this->checked_at = now();
        $this->observations = $observations;
        $this->save();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('result', 'pending');
    }

    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('result', 'fail');
    }

    public function scopeByCheckPoint($query, string $checkPoint)
    {
        return $query->where('check_point', $checkPoint);
    }

    /**
     * Get check summary
     */
    public function getCheckSummaryAttribute(): array
    {
        return [
            'check_point' => $this->check_point_label,
            'parameter' => $this->parameter,
            'target' => $this->target_value,
            'actual' => $this->actual_value,
            'deviation' => $this->deviation,
            'result' => $this->result_label,
            'inspector' => $this->inspector->name ?? 'Unknown',
            'checked_at' => $this->checked_at?->format('d M Y H:i'),
        ];
    }
}
