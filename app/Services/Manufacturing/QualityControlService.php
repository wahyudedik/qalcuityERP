<?php

namespace App\Services\Manufacturing;

use App\Models\DefectRecord;
use App\Models\QualityCheck;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
     * Create quality check with workflow stage enforcement
     */
    public function createQualityCheck(array $data): QualityCheck
    {
        return DB::transaction(function () use ($data) {
            // Validate QC stage against work order status
            if (isset($data['work_order_id'])) {
                $workOrder = WorkOrder::findOrFail($data['work_order_id']);
                $this->validateQCStage($workOrder, $data['stage']);
            }

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
     * Validate QC stage against work order status
     */
    protected function validateQCStage(WorkOrder $workOrder, string $stage): void
    {
        $allowedStages = [
            'planned' => ['pre_production'],
            'in_progress' => ['pre_production', 'in_process'],
            'completed' => ['pre_production', 'in_process', 'post_production', 'final'],
        ];

        $allowed = $allowedStages[$workOrder->status] ?? [];

        if (! in_array($stage, $allowed)) {
            throw new \InvalidArgumentException(
                "QC stage '{$stage}' is not allowed for work order with status '{$workOrder->status}'"
            );
        }
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
            $allPassed = collect($results)->every(fn ($r) => $r['passed']);
            $hasCriticalFail = collect($results)->contains(fn ($r) => $r['critical'] && ! $r['passed']);

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

            Log::info('Quality check submitted', [
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
                'reported_by' => $data['reported_by'] ?? Auth::id(),
            ]);

            Log::warning('Defect recorded', [
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
            $data['resolved_by'] ?? Auth::id()
        );

        Log::info('Defect resolved', [
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

    /**
     * Create CAPA (Corrective and Preventive Action) record
     */
    public function createCAPA(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $capa = [
                'capa_number' => 'CAPA-'.date('Ymd').'-'.str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'defect_id' => $data['defect_id'],
                'type' => $data['type'], // corrective, preventive
                'root_cause' => $data['root_cause'],
                'root_cause_category' => $data['root_cause_category'] ?? null, // 5-whys, fishbone, fta
                'corrective_action' => $data['corrective_action'],
                'preventive_action' => $data['preventive_action'] ?? null,
                'responsible_person_id' => $data['responsible_person_id'],
                'target_date' => $data['target_date'],
                'status' => 'open',
                'priority' => $data['priority'] ?? 'medium',
                'created_at' => now(),
            ];

            // Store in defect record or separate CAPA table
            if (isset($data['defect_id'])) {
                $defect = DefectRecord::findOrFail($data['defect_id']);
                $defect->update([
                    'root_cause' => $data['root_cause'],
                    'corrective_action' => $data['corrective_action'],
                    'preventive_action' => $data['preventive_action'] ?? null,
                ]);
            }

            Log::info('CAPA created', [
                'capa_number' => $capa['capa_number'],
                'type' => $capa['type'],
            ]);

            return $capa;
        });
    }

    /**
     * Get root cause analysis templates
     */
    public function getRootCauseTemplates(): array
    {
        return [
            '5_whys' => [
                'name' => '5 Whys Analysis',
                'description' => 'Iterative interrogative technique to explore cause-and-effect relationships',
                'template' => [
                    'problem_statement' => '',
                    'why_1' => '',
                    'why_2' => '',
                    'why_3' => '',
                    'why_4' => '',
                    'why_5' => '',
                    'root_cause' => '',
                ],
            ],
            'fishbone' => [
                'name' => 'Fishbone (Ishikawa) Diagram',
                'description' => 'Cause-and-effect diagram for quality problems',
                'template' => [
                    'problem_statement' => '',
                    'categories' => [
                        'manpower' => [],
                        'methods' => [],
                        'machines' => [],
                        'materials' => [],
                        'measurements' => [],
                        'environment' => [],
                    ],
                    'root_cause' => '',
                ],
            ],
            'fta' => [
                'name' => 'Fault Tree Analysis',
                'description' => 'Top-down deductive failure analysis',
                'template' => [
                    'top_event' => '',
                    'intermediate_events' => [],
                    'basic_events' => [],
                    'logic_gates' => [],
                    'root_cause' => '',
                ],
            ],
            'fmea' => [
                'name' => 'FMEA (Failure Mode and Effects Analysis)',
                'description' => 'Systematic approach for identifying potential failures',
                'template' => [
                    'failure_mode' => '',
                    'potential_effects' => '',
                    'severity' => 0,
                    'potential_causes' => '',
                    'occurrence' => 0,
                    'current_controls' => '',
                    'detection' => 0,
                    'rpn' => 0, // Risk Priority Number
                    'recommended_actions' => '',
                ],
            ],
        ];
    }

    /**
     * Generate Certificate of Analysis (COA)
     */
    public function generateCOA(int $qualityCheckId): array
    {
        $qualityCheck = QualityCheck::with(['workOrder', 'product', 'standard', 'inspector', 'defects'])
            ->findOrFail($qualityCheckId);

        if ($qualityCheck->status !== 'passed' && $qualityCheck->status !== 'conditional_pass') {
            throw new \InvalidArgumentException("Cannot generate COA for {$qualityCheck->status} quality check");
        }

        $coa = [
            'coa_number' => 'COA-'.date('Ymd').'-'.str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'quality_check_id' => $qualityCheck->id,
            'check_number' => $qualityCheck->check_number,
            'product' => [
                'name' => $qualityCheck->product?->name,
                'sku' => $qualityCheck->product?->sku,
                'batch_number' => $qualityCheck->workOrder?->batch_number,
            ],
            'work_order' => $qualityCheck->workOrder?->number,
            'inspection_date' => $qualityCheck->inspected_at?->format('Y-m-d H:i:s'),
            'inspector' => $qualityCheck->inspector?->name,
            'stage' => ucfirst(str_replace('_', ' ', $qualityCheck->stage)),
            'sample_size' => $qualityCheck->sample_size,
            'results' => $qualityCheck->results,
            'summary' => [
                'passed' => $qualityCheck->sample_passed,
                'failed' => $qualityCheck->sample_failed,
                'pass_rate' => $qualityCheck->pass_rate,
            ],
            'status' => ucfirst(str_replace('_', ' ', $qualityCheck->status)),
            'defects' => $qualityCheck->defects->map(function ($defect) {
                return [
                    'code' => $defect->defect_code,
                    'type' => $defect->defect_type,
                    'severity' => $defect->severity,
                    'quantity' => $defect->quantity_defected,
                ];
            }),
            'conclusion' => $this->generateCOAConclusion($qualityCheck),
            'authorized_by' => $qualityCheck->inspector?->name,
            'signature_date' => now()->format('Y-m-d'),
        ];

        return $coa;
    }

    /**
     * Generate COA conclusion text
     */
    protected function generateCOAConclusion(QualityCheck $qualityCheck): string
    {
        if ($qualityCheck->status === 'passed') {
            return 'The product has been inspected and tested in accordance with the specified quality standards. All test results meet the acceptance criteria. The batch is hereby approved for release.';
        } elseif ($qualityCheck->status === 'conditional_pass') {
            return 'The product has been inspected and meets most quality requirements with minor deviations noted. The batch is conditionally approved subject to the corrective actions specified.';
        }

        return 'The product does not meet the quality standards and is rejected.';
    }

    /**
     * Get comprehensive QC dashboard data
     */
    public function getDashboardData(): array
    {
        return Cache::remember("qc_dashboard_{$this->tenantId}", 300, function () {
            $stats = $this->getStatistics();
            $defectAnalysis = $this->getDefectAnalysis();

            // QC by stage
            $qcByStage = QualityCheck::where('tenant_id', $this->tenantId)
                ->selectRaw('stage, COUNT(*) as count')
                ->groupBy('stage')
                ->get()
                ->keyBy('stage');

            // Recent quality checks
            $recentChecks = QualityCheck::where('tenant_id', $this->tenantId)
                ->with(['workOrder', 'product', 'inspector'])
                ->latest('inspected_at')
                ->limit(10)
                ->get();

            // Open CAPAs
            $openCAPAs = DefectRecord::where('tenant_id', $this->tenantId)
                ->whereNull('resolved_at')
                ->whereNotNull('root_cause')
                ->with(['product', 'workOrder'])
                ->latest()
                ->limit(5)
                ->get();

            // Trend data (last 7 days)
            $trendData = QualityCheck::where('tenant_id', $this->tenantId)
                ->where('inspected_at', '>=', now()->subDays(7))
                ->selectRaw('DATE(inspected_at) as date, 
                            COUNT(*) as total,
                            SUM(CASE WHEN status = "passed" THEN 1 ELSE 0 END) as passed,
                            SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            return [
                'statistics' => $stats,
                'defect_analysis' => $defectAnalysis,
                'qc_by_stage' => $qcByStage,
                'recent_checks' => $recentChecks,
                'open_capas' => $openCAPAs,
                'trend_data' => $trendData,
            ];
        });
    }

    /**
     * Clear dashboard cache
     */
    public function clearDashboardCache(): void
    {
        Cache::forget("qc_dashboard_{$this->tenantId}");
    }
}
