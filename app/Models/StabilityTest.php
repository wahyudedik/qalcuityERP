<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StabilityTest extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'batch_id',
        'test_code',
        'test_type',
        'start_date',
        'expected_end_date',
        'actual_end_date',
        'storage_conditions',
        'initial_ph',
        'final_ph',
        'initial_appearance',
        'final_appearance',
        'initial_viscosity',
        'final_viscosity',
        'microbial_results',
        'color_change',
        'odor_change',
        'separation',
        'overall_result',
        'observations',
        'status',
        'tested_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expected_end_date' => 'date',
        'actual_end_date' => 'date',
        'initial_ph' => 'decimal:2',
        'final_ph' => 'decimal:2',
        'initial_viscosity' => 'decimal:2',
        'final_viscosity' => 'decimal:2',
    ];

    /**
     * Relationships
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'batch_id');
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    /**
     * Status helpers
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get test type label
     */
    public function getTestTypeLabelAttribute(): string
    {
        return match ($this->test_type) {
            'accelerated' => 'Accelerated Stability',
            'real_time' => 'Real-Time Stability',
            'freeze_thaw' => 'Freeze-Thaw Cycle',
            'photostability' => 'Photostability',
            default => ucfirst(str_replace('_', ' ', $this->test_type))
        };
    }

    /**
     * Get test type color
     */
    public function getTestTypeColorAttribute(): string
    {
        return match ($this->test_type) {
            'accelerated' => 'orange',
            'real_time' => 'blue',
            'freeze_thaw' => 'purple',
            'photostability' => 'yellow',
            default => 'gray'
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'in_progress' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            default => 'gray'
        };
    }

    /**
     * Check if test passed
     */
    public function isPassed(): bool
    {
        return $this->overall_result === 'Pass';
    }

    /**
     * Check if test failed
     */
    public function isFailedResult(): bool
    {
        return $this->overall_result === 'Fail';
    }

    /**
     * Calculate test duration in days
     */
    public function getDurationDaysAttribute(): ?int
    {
        if (! $this->start_date) {
            return null;
        }

        $endDate = $this->actual_end_date ?? now();

        return $this->start_date->diffInDays($endDate);
    }

    /**
     * Check if test is overdue
     */
    public function isOverdue(): bool
    {
        if (! $this->expected_end_date || $this->isCompleted()) {
            return false;
        }

        return now()->gt($this->expected_end_date);
    }

    /**
     * Get days until completion
     */
    public function getDaysUntilCompletionAttribute(): ?int
    {
        if (! $this->expected_end_date || $this->isCompleted()) {
            return null;
        }

        return now()->diffInDays($this->expected_end_date, false);
    }

    /**
     * Check if pH changed significantly
     */
    public function hasSignificantPhChange(float $threshold = 0.5): bool
    {
        if (! $this->initial_ph || ! $this->final_ph) {
            return false;
        }

        $diff = abs($this->initial_ph - $this->final_ph);

        return $diff > $threshold;
    }

    /**
     * Get next test code
     */
    public static function getNextTestCode(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return 'ST-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Scopes
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopePassed($query)
    {
        return $query->where('overall_result', 'Pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('overall_result', 'Fail');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('test_type', $type);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'in_progress')
            ->where('expected_end_date', '<', now());
    }

    /**
     * Get test summary
     */
    public function getTestSummaryAttribute(): array
    {
        return [
            'test_type' => $this->test_type_label,
            'duration' => $this->duration_days ? "{$this->duration_days} days" : 'N/A',
            'ph_change' => $this->initial_ph && $this->final_ph
                ? round(abs($this->initial_ph - $this->final_ph), 2)
                : 'N/A',
            'result' => $this->overall_result ?? 'Pending',
            'status' => $this->status_label,
        ];
    }

    /**
     * Mark test as completed
     */
    public function complete(string $result, string $observations = ''): void
    {
        $this->status = $result === 'Pass' ? 'completed' : 'failed';
        $this->overall_result = $result;
        $this->actual_end_date = now();
        $this->observations = $observations;
        $this->save();
    }
}
