<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * QC Test Template Model
 * 
 * TASK-2.20: Reusable test templates for quality inspections
 */
class QcTestTemplate extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'product_type',
        'stage',
        'test_parameters',
        'sample_size_formula',
        'acceptance_quality_limit',
        'is_active',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'test_parameters' => 'array',
            'sample_size_formula' => 'integer',
            'acceptance_quality_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(QcInspection::class, 'template_id');
    }

    /**
     * Get sample size based on lot size
     */
    public function calculateSampleSize(int $lotSize): int
    {
        // Simple AQL-based sampling
        // Formula 1: sqrt(lot_size)
        // Formula 2: lot_size * 0.1 (10%)
        // Formula 3: Fixed from template

        return match ($this->sample_size_formula) {
            1 => (int) ceil(sqrt($lotSize)),
            2 => (int) ceil($lotSize * 0.1),
            default => max(3, (int) ceil($lotSize * 0.05)), // Default 5%, min 3
        };
    }

    /**
     * Validate test results against parameters
     */
    public function validateResults(array $results): array
    {
        $validated = [];
        $allPassed = true;

        foreach ($results as $result) {
            $parameter = collect($this->test_parameters)->firstWhere('name', $result['parameter']);

            if (!$parameter) {
                $validated[] = [
                    ...$result,
                    'passed' => false,
                    'error' => 'Parameter not found in template',
                ];
                $allPassed = false;
                continue;
            }

            $value = $result['value'];
            $min = $parameter['min'] ?? null;
            $max = $parameter['max'] ?? null;

            $passed = true;
            $error = null;

            if ($min !== null && $value < $min) {
                $passed = false;
                $error = "Value {$value} below minimum {$min}";
            }

            if ($max !== null && $value > $max) {
                $passed = false;
                $error = "Value {$value} above maximum {$max}";
            }

            $validated[] = [
                ...$result,
                'passed' => $passed,
                'unit' => $parameter['unit'] ?? '',
                'critical' => $parameter['critical'] ?? false,
                'error' => $error,
            ];

            if (!$passed) {
                $allPassed = false;
            }
        }

        return [
            'results' => $validated,
            'all_passed' => $allPassed,
            'passed_count' => collect($validated)->where('passed', true)->count(),
            'failed_count' => collect($validated)->where('passed', false)->count(),
        ];
    }

    /**
     * Create inspection from template
     */
    public function createInspection(
        WorkOrder $workOrder,
        int $lotSize,
        string $stage,
        array $testResults = []
    ): QcInspection {
        $sampleSize = $this->calculateSampleSize($lotSize);

        $inspection = QcInspection::create([
            'tenant_id' => $this->tenant_id,
            'work_order_id' => $workOrder->id,
            'template_id' => $this->id,
            'stage' => $stage,
            'sample_size' => $sampleSize,
            'status' => 'pending',
        ]);

        if (!empty($testResults)) {
            $validation = $this->validateResults($testResults);
            $inspection->recordTestResults($validation['results']);
        }

        return $inspection;
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
            default => ucfirst($this->stage),
        };
    }
}
