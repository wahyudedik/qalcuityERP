<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\ProductionOutput;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Production Dashboard Controller
 *
 * TASK-2.18: Comprehensive production analytics dashboard
 */
class ProductionDashboardController extends Controller
{
    private function tid(): int
    {
        return Auth::user()->tenant_id;
    }

    /**
     * Display production dashboard
     */
    public function index()
    {
        $tenantId = $this->tid();
        $now = now();
        $thisMonth = now()->startOfMonth();

        // Overall Statistics
        $stats = [
            'total_work_orders' => WorkOrder::where('tenant_id', $tenantId)->count(),
            'pending' => WorkOrder::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'in_progress' => WorkOrder::where('tenant_id', $tenantId)->where('status', 'in_progress')->count(),
            'completed' => WorkOrder::where('tenant_id', $tenantId)->where('status', 'completed')->count(),
            'overdue' => WorkOrder::where('tenant_id', $tenantId)
                ->whereIn('status', ['pending', 'in_progress'])
                ->whereNotNull('planned_end_date')
                ->where('planned_end_date', '<', $now)
                ->count(),
            'this_month_completed' => WorkOrder::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->where('completed_at', '>=', $thisMonth)
                ->count(),
        ];

        // Performance Metrics
        $completedOrders = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->get();

        $avgYieldRate = $completedOrders->avg(function ($wo) {
            return $wo->yieldRate() ?? 0;
        });

        $avgEfficiency = $completedOrders->avg(function ($wo) {
            return $wo->efficiency_rate ?? 0;
        });

        $totalScrapCost = WorkOrder::where('tenant_id', $tenantId)->sum('scrap_cost');
        $totalReworkCost = WorkOrder::where('tenant_id', $tenantId)->sum('rework_cost');

        $performance = [
            'avg_yield_rate' => round($avgYieldRate ?? 0, 1),
            'avg_efficiency' => round($avgEfficiency ?? 0, 1),
            'total_scrap_cost' => round($totalScrapCost, 2),
            'total_rework_cost' => round($totalReworkCost, 2),
            'total_waste_cost' => round($totalScrapCost + $totalReworkCost, 2),
        ];

        // Recent Work Orders
        $recentOrders = WorkOrder::where('tenant_id', $tenantId)
            ->with(['product'])
            ->latest()
            ->limit(10)
            ->get();

        // Overdue Work Orders
        $overdueOrders = WorkOrder::where('tenant_id', $tenantId)
            ->with(['product'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->whereNotNull('planned_end_date')
            ->where('planned_end_date', '<', $now)
            ->orderBy('planned_end_date')
            ->get();

        // Production Trend (Last 7 days)
        $productionTrend = ProductionOutput::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('DATE(created_at) as date, SUM(good_qty) as total_good, SUM(reject_qty) as total_reject')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Products by Volume
        $topProducts = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->select('product_id', DB::raw('SUM(target_quantity) as total_quantity'), DB::raw('COUNT(*) as order_count'))
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

        // Priority Distribution
        $priorityDist = [
            'urgent' => WorkOrder::where('tenant_id', $tenantId)->where('priority', 1)->whereIn('status', ['pending', 'in_progress'])->count(),
            'high' => WorkOrder::where('tenant_id', $tenantId)->where('priority', 2)->whereIn('status', ['pending', 'in_progress'])->count(),
            'normal' => WorkOrder::where('tenant_id', $tenantId)->where('priority', 3)->whereIn('status', ['pending', 'in_progress'])->count(),
            'low' => WorkOrder::where('tenant_id', $tenantId)->where('priority', 4)->whereIn('status', ['pending', 'in_progress'])->count(),
        ];

        return view('production.dashboard', compact(
            'stats',
            'performance',
            'recentOrders',
            'overdueOrders',
            'productionTrend',
            'topProducts',
            'priorityDist'
        ));
    }

    /**
     * Get production analytics (API)
     */
    public function analytics()
    {
        $tenantId = $this->tid();

        $analytics = [
            'summary' => [
                'total_orders' => WorkOrder::where('tenant_id', $tenantId)->count(),
                'completion_rate' => $this->calculateCompletionRate($tenantId),
                'avg_yield' => $this->calculateAverageYield($tenantId),
                'total_revenue' => WorkOrder::where('tenant_id', $tenantId)->where('status', 'completed')->sum('total_cost'),
            ],
            'waste_analysis' => [
                'total_scrap_cost' => WorkOrder::where('tenant_id', $tenantId)->sum('scrap_cost'),
                'total_rework_cost' => WorkOrder::where('tenant_id', $tenantId)->sum('rework_cost'),
                'scrap_percentage' => $this->calculateScrapPercentage($tenantId),
            ],
            'efficiency' => [
                'avg_efficiency_rate' => WorkOrder::where('tenant_id', $tenantId)->whereNotNull('efficiency_rate')->avg('efficiency_rate'),
                'on_time_delivery_rate' => $this->calculateOnTimeDeliveryRate($tenantId),
            ],
        ];

        return response()->json($analytics);
    }

    /**
     * Calculate completion rate
     */
    private function calculateCompletionRate(int $tenantId): float
    {
        $total = WorkOrder::where('tenant_id', $tenantId)->count();
        $completed = WorkOrder::where('tenant_id', $tenantId)->where('status', 'completed')->count();

        return $total > 0 ? round(($completed / $total) * 100, 1) : 0;
    }

    /**
     * Calculate average yield rate
     */
    private function calculateAverageYield(int $tenantId): float
    {
        $completed = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->get();

        $avgYield = $completed->avg(function ($wo) {
            return $wo->yieldRate() ?? 0;
        });

        return round($avgYield ?? 0, 1);
    }

    /**
     * Calculate scrap percentage
     */
    private function calculateScrapPercentage(int $tenantId): float
    {
        $totalOutput = ProductionOutput::where('tenant_id', $tenantId)
            ->selectRaw('SUM(good_qty) as good, SUM(reject_qty) as reject')
            ->first();

        $total = ($totalOutput->good ?? 0) + ($totalOutput->reject ?? 0);

        return $total > 0 ? round(($totalOutput->reject / $total) * 100, 2) : 0;
    }

    /**
     * Calculate on-time delivery rate
     */
    private function calculateOnTimeDeliveryRate(int $tenantId): float
    {
        $completed = WorkOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereNotNull('planned_end_date')
            ->whereNotNull('actual_end_date')
            ->get();

        if ($completed->isEmpty()) {
            return 0;
        }

        $onTime = $completed->filter(function ($wo) {
            /** @var Carbon $actualEnd */
            $actualEnd = $wo->actual_end_date;
            /** @var Carbon $plannedEnd */
            $plannedEnd = $wo->planned_end_date;

            return $actualEnd->lte($plannedEnd);
        })->count();

        return round(($onTime / $completed->count()) * 100, 1);
    }
}
