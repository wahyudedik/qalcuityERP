<?php

namespace App\Services\Manufacturing;

use App\Models\WorkOrder;
use Carbon\Carbon;

/**
 * Production Scheduling Service
 *
 * TASK-2.14: Advanced production scheduling with capacity planning,
 * conflict detection, and optimization.
 */
class ProductionSchedulingService
{
    /**
     * Schedule a work order
     */
    public function scheduleWorkOrder(
        int $workOrderId,
        int $tenantId,
        Carbon $startDate,
        Carbon $endDate,
        int $priority = 3,
        ?string $productionLine = null
    ): array {
        $workOrder = WorkOrder::where('id', $workOrderId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $workOrder) {
            return ['success' => false, 'message' => 'Work Order not found'];
        }

        if ($workOrder->status === 'completed' || $workOrder->status === 'cancelled') {
            return ['success' => false, 'message' => 'Cannot schedule completed or cancelled work order'];
        }

        // Check for scheduling conflicts
        $conflicts = $this->detectSchedulingConflicts($tenantId, $startDate, $endDate, $workOrderId);

        if (! empty($conflicts) && $priority <= 2) {
            // High priority can override conflicts
            return [
                'success' => false,
                'message' => 'Scheduling conflict detected',
                'conflicts' => $conflicts,
            ];
        }

        // Update schedule
        $workOrder->update([
            'planned_start_date' => $startDate,
            'planned_end_date' => $endDate,
            'priority' => $priority,
            'production_line' => $productionLine,
            'schedule_variance' => 0,
        ]);

        return [
            'success' => true,
            'message' => 'Work Order scheduled successfully',
            'work_order' => $workOrder,
            'conflicts' => $conflicts,
            'warnings' => ! empty($conflicts) ? ['Scheduling conflict exists but overridden'] : [],
        ];
    }

    /**
     * Detect scheduling conflicts
     */
    public function detectSchedulingConflicts(
        int $tenantId,
        Carbon $startDate,
        Carbon $endDate,
        ?int $excludeWorkOrderId = null
    ): array {
        $query = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('planned_start_date')
            ->whereNotNull('planned_end_date')
            ->where(function ($q) use ($startDate, $endDate) {
                // Overlap detection
                $q->whereBetween('planned_start_date', [$startDate, $endDate])
                    ->orWhereBetween('planned_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('planned_start_date', '<=', $startDate)
                            ->where('planned_end_date', '>=', $endDate);
                    });
            });

        if ($excludeWorkOrderId) {
            $query->where('id', '!=', $excludeWorkOrderId);
        }

        $conflicts = $query->get(['id', 'number', 'product_id', 'planned_start_date', 'planned_end_date', 'priority']);

        return $conflicts->map(function ($wo) {
            return [
                'work_order_id' => $wo->id,
                'work_order_number' => $wo->number,
                'product_name' => $wo->product?->name ?? 'Unknown',
                'start_date' => $wo->planned_start_date->format('Y-m-d'),
                'end_date' => $wo->planned_end_date->format('Y-m-d'),
                'priority' => $wo->priority,
                'priority_label' => $wo->priority_label,
            ];
        })->toArray();
    }

    /**
     * Get production schedule for date range
     */
    public function getProductionSchedule(int $tenantId, Carbon $startDate, Carbon $endDate): array
    {
        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('planned_start_date')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('planned_start_date', [$startDate, $endDate])
                    ->orWhereBetween('planned_end_date', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('planned_start_date', '<=', $startDate)
                            ->where('planned_end_date', '>=', $endDate);
                    });
            })
            ->with(['product'])
            ->orderBy('planned_start_date')
            ->orderBy('priority')
            ->get();

        return [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'total_work_orders' => $workOrders->count(),
            'work_orders' => $workOrders->map(function ($wo) {
                return $wo->getGanttData();
            }),
            'summary' => [
                'urgent' => $workOrders->where('priority', 1)->count(),
                'high' => $workOrders->where('priority', 2)->count(),
                'normal' => $workOrders->where('priority', 3)->count(),
                'low' => $workOrders->where('priority', 4)->count(),
                'overdue' => $workOrders->filter(function ($wo) {
                    return $wo->isOverdue();
                })->count(),
            ],
        ];
    }

    /**
     * Optimize schedule based on priority and dependencies
     */
    public function optimizeSchedule(int $tenantId): array
    {
        $pendingOrders = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->whereNotNull('planned_start_date')
            ->orderBy('priority')
            ->orderBy('planned_start_date')
            ->get();

        $optimizations = [];
        $currentDate = now();

        foreach ($pendingOrders as $order) {
            $optimization = [
                'work_order_id' => $order->id,
                'work_order_number' => $order->number,
                'current_start' => $order->planned_start_date?->format('Y-m-d'),
                'current_end' => $order->planned_end_date?->format('Y-m-d'),
                'recommended_start' => null,
                'recommended_end' => null,
                'reason' => null,
            ];

            // If overdue, recommend immediate start
            if ($order->isOverdue()) {
                $optimization['recommended_start'] = $currentDate->format('Y-m-d');
                $optimization['recommended_end'] = $currentDate->copy()->addDays(7)->format('Y-m-d');
                $optimization['reason'] = 'Work order is overdue - immediate start recommended';
            }

            // If high priority and not scheduled optimally
            if ($order->priority <= 2 && $order->planned_start_date->gt($currentDate->copy()->addDays(3))) {
                $optimization['recommended_start'] = $currentDate->copy()->addDay()->format('Y-m-d');
                $optimization['recommended_end'] = $order->planned_end_date?->format('Y-m-d');
                $optimization['reason'] = 'High priority order should start sooner';
            }

            if ($optimization['recommended_start']) {
                $optimizations[] = $optimization;
            }
        }

        return [
            'total_optimizations' => count($optimizations),
            'optimizations' => $optimizations,
        ];
    }

    /**
     * Get capacity utilization by production line
     */
    public function getCapacityUtilization(int $tenantId, Carbon $date): array
    {
        $productionLines = WorkOrder::where('tenant_id', $tenantId)
            ->whereNotNull('production_line')
            ->distinct()
            ->pluck('production_line');

        $utilization = [];

        foreach ($productionLines as $line) {
            $activeOrders = WorkOrder::where('tenant_id', $tenantId)
                ->where('production_line', $line)
                ->where('status', 'in_progress')
                ->count();

            $scheduledOrders = WorkOrder::where('tenant_id', $tenantId)
                ->where('production_line', $line)
                ->where('status', 'pending')
                ->whereDate('planned_start_date', '<=', $date)
                ->whereDate('planned_end_date', '>=', $date)
                ->count();

            $utilization[] = [
                'production_line' => $line,
                'active_orders' => $activeOrders,
                'scheduled_orders' => $scheduledOrders,
                'total_load' => $activeOrders + $scheduledOrders,
                'utilization_percent' => min(100, (($activeOrders + $scheduledOrders) / 10) * 100), // Assume max 10 orders per line
            ];
        }

        return $utilization;
    }

    /**
     * Reschedule overdue work orders
     */
    public function rescheduleOverdue(int $tenantId): array
    {
        $overdueOrders = WorkOrder::where('tenant_id', $tenantId)
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('planned_end_date')
            ->where('planned_end_date', '<', now())
            ->get();

        $rescheduled = 0;

        foreach ($overdueOrders as $order) {
            $daysOverdue = now()->diffInDays($order->planned_end_date);
            $newEndDate = now()->copy()->addDays($daysOverdue + 7); // Add 7 days buffer

            $order->update([
                'planned_end_date' => $newEndDate,
                'schedule_variance' => $daysOverdue * -1,
            ]);

            $rescheduled++;
        }

        return [
            'total_overdue' => $overdueOrders->count(),
            'rescheduled' => $rescheduled,
            'message' => "Rescheduled {$rescheduled} overdue work orders",
        ];
    }

    /**
     * Get scheduling analytics
     */
    public function getSchedulingAnalytics(int $tenantId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->where(function ($q) use ($startDate) {
                $q->whereNotNull('planned_start_date')
                    ->where('planned_start_date', '>=', $startDate);
            })
            ->get();

        $completedOnTime = $workOrders->where('status', 'completed')
            ->filter(function ($wo) {
                return $wo->actual_end_date && $wo->planned_end_date &&
                    $wo->actual_end_date->lte($wo->planned_end_date);
            })->count();

        $totalCompleted = $workOrders->where('status', 'completed')->count();
        $onTimeDeliveryRate = $totalCompleted > 0 ? ($completedOnTime / $totalCompleted) * 100 : 0;

        $avgScheduleVariance = $workOrders->where('status', 'completed')
            ->avg('schedule_variance') ?? 0;

        return [
            'period_days' => $days,
            'total_scheduled' => $workOrders->count(),
            'completed' => $totalCompleted,
            'in_progress' => $workOrders->where('status', 'in_progress')->count(),
            'pending' => $workOrders->where('status', 'pending')->count(),
            'overdue' => $workOrders->filter(function ($wo) {
                return $wo->isOverdue();
            })->count(),
            'on_time_delivery_rate' => round($onTimeDeliveryRate, 1),
            'avg_schedule_variance_days' => round($avgScheduleVariance, 1),
            'priority_distribution' => [
                'urgent' => $workOrders->where('priority', 1)->count(),
                'high' => $workOrders->where('priority', 2)->count(),
                'normal' => $workOrders->where('priority', 3)->count(),
                'low' => $workOrders->where('priority', 4)->count(),
            ],
        ];
    }
}
