<?php

namespace App\Services\Manufacturing;

use App\Models\QualityCheck;
use App\Models\QualityCheckStandard;
use App\Models\DefectRecord;
use App\Models\WorkOrder;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QualityControlService
{
    protected int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Create quality check for work order
     */
    public function createQualityCheck(array $data): QualityCheck
    {
        return DB::transaction(function () use ($data) {
            $qualityCheck = QualityCheck::create([
                'tenant_id' => $this->tenantId,
                'work_order_id' => $data['work_order_id'] ?? null,
                'product_id' => $data['product_id'] ?? null,
                'standard_id' => $data['standard_id'] ?? null,
                'inspector_id' => $data['inspector_id'],
                'stage' => $data['stage'],
                'sample_size' => $data['sample_size'],
                'sample_passed' => 0,
                'sample_failed' => 0,
                'status' => 'pending',
                'notes' => $data['notes'] ?? null,
            ]);

            // If standard is provided, load default parameters
            if ($qualityCheck->standard) {
                $qualityCheck->update([
                    'results' => array_map(function ($param) {
                        return [
                            'parameter' => $param['name'],
                            'value' => null,
                            'min_value' => $param['min_value'] ?? null,
                            'max_value' => $param['max_value'] ?? null,
                            'unit' => $param['unit'] ?? null,
                            'critical' => $param['critical'] ?? false,
                            'passed' => null,
                        ];
                    }, $qualityCheck->standard->parameters),
                ]);
            }

            return $qualityCheck;
        });
    }

    /**
     * Submit quality check results
     */
    public function submitResults(QualityCheck $qualityCheck, array $results, array $summary): QualityCheck
    {
        return DB::transaction(function () use ($qualityCheck, $results, $summary) {
            $samplePassed = $summary['sample_passed'] ?? 0;
            $sampleFailed = $summary['sample_failed'] ?? 0;

            // Determine pass/fail based on results
            $allPassed = collect($results)->every(fn($r) => $r['passed']);
            $hasCriticalFail = collect($results)->contains(fn($r) => $r['critical'] && !$r['passed']);

            if ($hasCriticalFail) {
                $status = 'failed';
            } elseif ($allPassed && $sampleFailed == 0) {
                $status = 'passed';
            } else {
                $status = 'conditional_pass';
            }

            $qualityCheck->update([
                'results' => $results,
                'sample_passed' => $samplePassed,
                'sample_failed' => $sampleFailed,
                'status' => $status,
                'notes' => $summary['notes'] ?? null,
                'corrective_action' => $summary['corrective_action'] ?? null,
                'inspected_at' => now(),
            ]);

            // Update work order quality status
            if ($qualityCheck->workOrder) {
                $this->updateWorkOrderQualityStatus($qualityCheck->workOrder, $status);
            }

            Log::info("Quality check submitted", [
                'check_number' => $qualityCheck->check_number,
                'status' => $status,
                'pass_rate' => $qualityCheck->pass_rate,
            ]);

            return $qualityCheck;
        });
    }

    /**
     * Record defect
     */
    public function recordDefect(array $data): DefectRecord
    {
        return DB::transaction(function () use ($data) {
            $defect = DefectRecord::create([
                'tenant_id' => $this->tenantId,
                'quality_check_id' => $data['quality_check_id'],
                'product_id' => $data['product_id'],
                'work_order_id' => $data['work_order_id'] ?? null,
                'defect_type' => $data['defect_type'],
                'severity' => $data['severity'],
                'quantity_defected' => $data['quantity_defected'],
                'description' => $data['description'],
                'disposition' => $data['disposition'],
                'cost_impact' => $data['cost_impact'] ?? 0,
                'reported_by' => $data['reported_by'] ?? auth()->id(),
            ]);

            Log::warning("Defect recorded", [
                'defect_code' => $defect->defect_code,
                'severity' => $defect->severity,
                'product_id' => $defect->product_id,
            ]);

            return $defect;
        });
    }

    /**
     * Resolve defect
     */
    public function resolveDefect(DefectRecord $defect, array $data): DefectRecord
    {
        $defect->resolve(
            $data['root_cause'],
            $data['corrective_action'],
            $data['preventive_action'] ?? null,
            $data['resolved_by'] ?? auth()->id()
        );

        Log::info("Defect resolved", [
            'defect_code' => $defect->defect_code,
            'root_cause' => $defect->root_cause,
        ]);

        return $defect;
    }

    /**
     * Get QC statistics
     */
    public function getStatistics($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $query = QualityCheck::where('tenant_id', $this->tenantId)
            ->whereBetween('inspected_at', [$startDate, $endDate]);

        $total = $query->count();
        $passed = (clone $query)->where('status', 'passed')->count();
        $failed = (clone $query)->where('status', 'failed')->count();
        $conditional = (clone $query)->where('status', 'conditional_pass')->count();
        $pending = (clone $query)->where('status', 'pending')->count();

        $defects = DefectRecord::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $totalDefects = $defects->count();
        $criticalDefects = (clone $defects)->where('severity', 'critical')->count();
        $majorDefects = (clone $defects)->where('severity', 'major')->count();
        $openDefects = (clone $defects)->whereNull('resolved_at')->count();
        $totalCostImpact = (clone $defects)->sum('cost_impact');

        return [
            'quality_checks' => [
                'total' => $total,
                'passed' => $passed,
                'failed' => $failed,
                'conditional_pass' => $conditional,
                'pending' => $pending,
                'pass_rate' => $total > 0 ? ($passed / $total) * 100 : 0,
                'fail_rate' => $total > 0 ? ($failed / $total) * 100 : 0,
            ],
            'defects' => [
                'total' => $totalDefects,
                'critical' => $criticalDefects,
                'major' => $majorDefects,
                'open' => $openDefects,
                'resolved' => $totalDefects - $openDefects,
                'total_cost_impact' => $totalCostImpact,
            ],
        ];
    }

    /**
     * Get defect analysis by type
     */
    public function getDefectAnalysis($startDate = null, $endDate = null): array
    {
        $startDate = $startDate ?? now()->subDays(30);
        $endDate = $endDate ?? now();

        $defectsByType = DefectRecord::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('defect_type, COUNT(*) as count, SUM(cost_impact) as total_cost')
            ->groupBy('defect_type')
            ->get()
            ->keyBy('defect_type');

        $defectsBySeverity = DefectRecord::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->get()
            ->keyBy('severity');

        $defectsByProduct = DefectRecord::where('tenant_id', $this->tenantId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->with('product')
            ->selectRaw('product_id, COUNT(*) as count, SUM(quantity_defected) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'by_type' => $defectsByType,
            'by_severity' => $defectsBySeverity,
            'by_product' => $defectsByProduct,
        ];
    }

    /**
     * Update work order quality status
     */
    protected function updateWorkOrderQualityStatus(WorkOrder $workOrder, string $qcStatus): void
    {
        $statusMap = [
            'passed' => ['quality_status' => 'passed', 'quality_passed_at' => now()],
            'failed' => ['quality_status' => 'failed', 'quality_failed_at' => now()],
            'conditional_pass' => ['quality_status' => 'conditional_pass', 'quality_passed_at' => now()],
        ];

        if (isset($statusMap[$qcStatus])) {
            $workOrder->update($statusMap[$qcStatus]);
        }
    }
}
