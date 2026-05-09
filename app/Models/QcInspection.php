<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * QC Inspection Model
 *
 * TASK-2.19 & 2.20: Quality inspection records with test results
 */
class QcInspection extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'work_order_id',
        'template_id',
        'inspector_id',
        'inspection_number',
        'stage',
        'sample_size',
        'sample_passed',
        'sample_failed',
        'status',
        'test_results',
        'pass_rate',
        'grade',
        'defects_found',
        'corrective_action',
        'inspector_notes',
        'inspected_at',
    ];

    protected function casts(): array
    {
        return [
            'test_results' => 'array',
            'sample_size' => 'integer',
            'sample_passed' => 'integer',
            'sample_failed' => 'integer',
            'pass_rate' => 'decimal:2',
            'inspected_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($inspection) {
            if (! $inspection->inspection_number) {
                $inspection->inspection_number = 'QCI-'.date('Ymd').'-'.str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            }
            if (! $inspection->inspector_id) {
                $inspection->inspector_id = Auth::id();
            }
        });
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QcTestTemplate::class, 'template_id');
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    /**
     * Calculate pass rate
     */
    public function calculatePassRate(): float
    {
        if ($this->sample_size == 0) {
            return 0;
        }

        return round(($this->sample_passed / $this->sample_size) * 100, 2);
    }

    /**
     * Determine grade based on pass rate
     */
    public function determineGrade(): string
    {
        $passRate = $this->calculatePassRate();

        if ($passRate >= 98) {
            return 'A';
        }
        if ($passRate >= 95) {
            return 'B';
        }
        if ($passRate >= 90) {
            return 'C';
        }
        if ($passRate >= 85) {
            return 'D';
        }

        return 'F';
    }

    /**
     * Pass inspection
     */
    public function pass(?string $notes = null): void
    {
        $this->update([
            'status' => 'passed',
            'grade' => $this->determineGrade(),
            'pass_rate' => $this->calculatePassRate(),
            'inspected_at' => now(),
            'inspector_notes' => $notes ?? $this->inspector_notes,
        ]);

        // Update work order
        if ($this->workOrder) {
            $this->workOrder->update([
                'quality_status' => 'passed',
                'quality_passed_at' => now(),
                'quality_grade' => $this->determineGrade(),
                'quality_score' => $this->calculatePassRate(),
            ]);
        }
    }

    /**
     * Fail inspection
     */
    public function fail(string $correctiveAction, ?string $defects = null): void
    {
        $this->update([
            'status' => 'failed',
            'grade' => 'F',
            'pass_rate' => $this->calculatePassRate(),
            'corrective_action' => $correctiveAction,
            'defects_found' => $defects,
            'inspected_at' => now(),
        ]);

        // Update work order
        if ($this->workOrder) {
            $this->workOrder->update([
                'quality_status' => 'failed',
                'quality_failed_at' => now(),
                'quality_grade' => 'F',
                'quality_score' => $this->calculatePassRate(),
            ]);
        }
    }

    /**
     * Conditional pass
     */
    public function conditionalPass(string $notes): void
    {
        $this->update([
            'status' => 'conditional_pass',
            'grade' => $this->determineGrade(),
            'pass_rate' => $this->calculatePassRate(),
            'inspector_notes' => $notes,
            'inspected_at' => now(),
        ]);

        if ($this->workOrder) {
            $this->workOrder->update([
                'quality_status' => 'conditional',
                'quality_notes' => $notes,
                'quality_score' => $this->calculatePassRate(),
            ]);
        }
    }

    /**
     * Record test results
     */
    public function recordTestResults(array $results): void
    {
        $passed = 0;
        $failed = 0;

        foreach ($results as $result) {
            if ($result['passed']) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $this->update([
            'test_results' => $results,
            'sample_passed' => $passed,
            'sample_failed' => $failed,
            'sample_size' => $passed + $failed,
        ]);
    }

    /**
     * Check if inspection passed
     */
    public function isPassed(): bool
    {
        return $this->status === 'passed';
    }

    /**
     * Check if inspection failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Get stage label
     */
    public function getStageLabelAttribute(): string
    {
        return match ($this->stage) {
            'incoming' => 'Incoming Material',
            'in-process' => 'In-Process',
            'final' => 'Final Inspection',
            'random' => 'Random Check',
            default => ucfirst($this->stage),
        };
    }

    /**
     * Get status color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'passed' => 'green',
            'failed' => 'red',
            'conditional_pass' => 'yellow',
            'in_progress' => 'blue',
            default => 'gray',
        };
    }
}
