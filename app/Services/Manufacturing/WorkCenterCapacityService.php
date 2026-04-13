<?php

namespace App\Services\Manufacturing;

use App\Models\WorkCenter;
use App\Models\WorkOrder;
use App\Models\WorkOrderOperation;
use Carbon\Carbon;

/**
 * Work Center Capacity Planning Service
 * 
 * Features:
 * - Daily/weekly/monthly capacity planning
 * - Utilization tracking and forecasting
 * - Work order scheduling
 * - Bottleneck detection
 * - Maintenance scheduling
 */
class WorkCenterCapacityService
{
    /**
     * Get capacity overview for all work centers
     * 
     * @param int $tenantId Tenant ID
     * @param string|null $date Specific date (default: today)
     * @return array Capacity overview
     */
    public function getCapacityOverview(int $tenantId, ?string $date = null): array
    {
        $targetDate = $date ? Carbon::parse($date) : now();

        $workCenters = WorkCenter::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        $overview = [];
        $totalAvailable = 0;
        $totalPlanned = 0;
        $totalRemaining = 0;

        /** @var WorkCenter $wc */
        foreach ($workCenters as $wc) {
            $available = $wc->getAvailableCapacity();
            $planned = $wc->planned_hours_today;
            $remaining = max(0, $available - $planned);
            $utilization = $available > 0 ? ($planned / $available) * 100 : 0;

            $totalAvailable += $available;
            $totalPlanned += $planned;
            $totalRemaining += $remaining;

            $overview[] = [
                'work_center_id' => $wc->id,
                'code' => $wc->code,
                'name' => $wc->name,
                'available_hours' => round((float) $available, 2),
                'planned_hours' => round((float) $planned, 2),
                'remaining_hours' => round((float) $remaining, 2),
                'utilization_percent' => round((float) $utilization, 2),
                'utilization_status' => $wc->getUtilizationStatus(),
                'is_operational' => $wc->isOperational(),
                'maintenance_due' => $wc->isMaintenanceDue(),
            ];
        }

        $overallUtilization = $totalAvailable > 0 ? ($totalPlanned / $totalAvailable) * 100 : 0;

        return [
            'date' => $targetDate->format('Y-m-d'),
            'work_centers' => $overview,
            'summary' => [
                'total_work_centers' => count($overview),
                'operational_work_centers' => collect($overview)->where('is_operational', true)->count(),
                'total_available_hours' => round($totalAvailable, 2),
                'total_planned_hours' => round($totalPlanned, 2),
                'total_remaining_hours' => round($totalRemaining, 2),
                'overall_utilization' => round($overallUtilization, 2),
                'bottlenecks' => collect($overview)->where('utilization_percent', '>=', 90)->count(),
            ],
        ];
    }

    /**
     * Find available work center for scheduling
     * 
     * @param int $tenantId Tenant ID
     * @param float $requiredHours Hours needed
     * @param string|null $preferredDate Preferred date
     * @return WorkCenter|null Best available work center
     */
    public function findAvailableWorkCenter(int $tenantId, float $requiredHours, ?string $preferredDate = null): ?WorkCenter
    {
        $workCenters = WorkCenter::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $bestOption = null;
        $mostRemaining = -1;

        /** @var WorkCenter $wc */
        foreach ($workCenters as $wc) {
            $remaining = $wc->getRemainingCapacity();

            if ($remaining >= $requiredHours && $remaining > $mostRemaining) {
                $bestOption = $wc;
                $mostRemaining = $remaining;
            }
        }

        return $bestOption;
    }

    /**
     * Schedule work order operation
     * 
     * @param WorkOrder $workOrder Work order
     * @param int $workCenterId Work center ID
     * @param float $estimatedHours Estimated hours
     * @param string|null $scheduledDate Scheduled date
     * @return array Scheduling result
     */
    public function scheduleOperation(
        WorkOrder $workOrder,
        int $workCenterId,
        float $estimatedHours,
        ?string $scheduledDate = null
    ): array {
        $workCenter = WorkCenter::find($workCenterId);

        if (!$workCenter || $workCenter->tenant_id !== $workOrder->tenant_id) {
            return [
                'success' => false,
                'error' => 'Work center not found or unauthorized',
            ];
        }

        $remaining = $workCenter->getRemainingCapacity();

        if ($remaining < $estimatedHours) {
            return [
                'success' => false,
                'error' => "Insufficient capacity. Available: {$remaining}h, Required: {$estimatedHours}h",
                'available_capacity' => $remaining,
                'required_capacity' => $estimatedHours,
            ];
        }

        // Create operation
        $operation = WorkOrderOperation::create([
            'tenant_id' => $workOrder->tenant_id,
            'work_order_id' => $workOrder->id,
            'work_center_id' => $workCenter->id,
            'operation_name' => "Operation at {$workCenter->name}",
            'estimated_hours' => $estimatedHours,
            'actual_hours' => 0,
            'scheduled_date' => $scheduledDate ? Carbon::parse($scheduledDate) : now(),
            'status' => 'scheduled',
        ]);

        // Update work center planned hours
        $workCenter->addPlannedHours($estimatedHours);

        return [
            'success' => true,
            'operation' => $operation,
            'work_center' => $workCenter,
            'remaining_capacity' => $workCenter->getRemainingCapacity(),
        ];
    }

    /**
     * Detect bottlenecks in work centers
     * 
     * @param int $tenantId Tenant ID
     * @param int $daysAhead Days to look ahead
     * @return array Bottleneck analysis
     */
    public function detectBottlenecks(int $tenantId, int $daysAhead = 7): array
    {
        $workCenters = WorkCenter::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $bottlenecks = [];
        $forecast = [];

        /** @var WorkCenter $wc */
        foreach ($workCenters as $wc) {
            $wcForecast = $wc->getCapacityForecast($daysAhead);
            $overloadedDays = collect($wcForecast)->where('utilization_percent', '>', 90);

            if ($overloadedDays->count() > 0) {
                $bottlenecks[] = [
                    'work_center_id' => $wc->id,
                    'code' => $wc->code,
                    'name' => $wc->name,
                    'overloaded_days' => $overloadedDays->count(),
                    'max_utilization' => $overloadedDays->max('utilization_percent'),
                    'critical_dates' => $overloadedDays->pluck('date')->toArray(),
                ];
            }

            $forecast[] = [
                'work_center_id' => $wc->id,
                'code' => $wc->code,
                'name' => $wc->name,
                'forecast' => $wcForecast,
            ];
        }

        return [
            'bottlenecks' => $bottlenecks,
            'forecast' => $forecast,
            'total_bottlenecks' => count($bottlenecks),
            'severity' => count($bottlenecks) > 3 ? 'high' : (count($bottlenecks) > 0 ? 'medium' : 'low'),
        ];
    }

    /**
     * Generate maintenance schedule
     * 
     * @param int $tenantId Tenant ID
     * @param int $daysAhead Days to look ahead
     * @return array Maintenance schedule
     */
    public function generateMaintenanceSchedule(int $tenantId, int $daysAhead = 30): array
    {
        $workCenters = WorkCenter::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $dueNow = [];
        $upcoming = [];

        /** @var WorkCenter $wc */
        foreach ($workCenters as $wc) {
            if ($wc->isMaintenanceDue()) {
                $dueNow[] = [
                    'work_center_id' => $wc->id,
                    'code' => $wc->code,
                    'name' => $wc->name,
                    'last_maintenance' => $wc->last_maintenance_date,
                    'was_due' => $wc->next_maintenance_date,
                    'overdue_days' => $wc->next_maintenance_date ? now()->diffInDays($wc->next_maintenance_date) : 0,
                ];
            } elseif ($wc->next_maintenance_date) {
                $daysUntil = now()->diffInDays($wc->next_maintenance_date, false);

                if ($daysUntil <= $daysAhead) {
                    $upcoming[] = [
                        'work_center_id' => $wc->id,
                        'code' => $wc->code,
                        'name' => $wc->name,
                        'scheduled_date' => $wc->next_maintenance_date,
                        'days_until' => $daysUntil,
                    ];
                }
            }
        }

        return [
            'due_now' => $dueNow,
            'upcoming' => collect($upcoming)->sortBy('days_until')->values()->toArray(),
            'total_due' => count($dueNow),
            'total_upcoming' => count($upcoming),
        ];
    }

    /**
     * Calculate work center efficiency
     * 
     * @param WorkCenter $workCenter Work center
     * @param string|null $startDate Start date
     * @param string|null $endDate End date
     * @return array Efficiency metrics
     */
    public function calculateEfficiency(
        WorkCenter $workCenter,
        ?string $startDate = null,
        ?string $endDate = null
    ): array {
        $start = $startDate ? Carbon::parse($startDate) : now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate) : now()->endOfMonth();

        // Get all operations in date range
        $operations = WorkOrderOperation::where('work_center_id', $workCenter->id)
            ->whereBetween('scheduled_date', [$start, $end])
            ->get();

        $totalEstimated = $operations->sum('estimated_hours');
        $totalActual = $operations->sum('actual_hours');
        $completedCount = $operations->where('status', 'completed')->count();
        $totalCount = $operations->count();

        $efficiency = $totalActual > 0 ? ($totalEstimated / $totalActual) * 100 : 100;
        $completionRate = $totalCount > 0 ? ($completedCount / $totalCount) * 100 : 0;

        return [
            'work_center_id' => $workCenter->id,
            'work_center_name' => $workCenter->name,
            'period' => [
                'start' => $start->format('Y-m-d'),
                'end' => $end->format('Y-m-d'),
            ],
            'efficiency_percent' => round($efficiency, 2),
            'completion_rate_percent' => round($completionRate, 2),
            'total_operations' => $totalCount,
            'completed_operations' => $completedCount,
            'total_estimated_hours' => round($totalEstimated, 2),
            'total_actual_hours' => round($totalActual, 2),
            'variance_hours' => round($totalEstimated - $totalActual, 2),
        ];
    }

    /**
     * Reset daily capacity tracking
     * 
     * @param int $tenantId Tenant ID
     */
    public function resetDailyTracking(int $tenantId): void
    {
        WorkCenter::where('tenant_id', $tenantId)
            ->update([
                'planned_hours_today' => 0,
                'actual_hours_today' => 0,
                'current_utilization' => 0,
            ]);
    }
}
