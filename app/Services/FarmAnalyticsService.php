<?php

namespace App\Services;

use App\Models\FarmPlot;
use App\Models\FarmPlotActivity;
use App\Models\HarvestLog;
use Illuminate\Support\Facades\DB;

class FarmAnalyticsService
{
    /**
     * Full cost breakdown for a single plot (all time or per cycle).
     */
    public function plotCostBreakdown(int $plotId, ?int $cycleId = null): array
    {
        $query = FarmPlotActivity::where('farm_plot_id', $plotId);
        if ($cycleId) {
            $query->where('crop_cycle_id', $cycleId);
        }

        $byType = $query->selectRaw('activity_type, SUM(cost) as total_cost, SUM(input_quantity) as total_input, COUNT(*) as sessions')
            ->groupBy('activity_type')
            ->orderByDesc('total_cost')
            ->get();

        $totalCost = $byType->sum('total_cost');

        return $byType->map(fn ($row) => [
            'activity' => $row->activity_type,
            'label' => FarmPlotActivity::ACTIVITY_TYPES[$row->activity_type] ?? $row->activity_type,
            'cost' => (float) $row->total_cost,
            'pct' => $totalCost > 0 ? round($row->total_cost / $totalCost * 100, 1) : 0,
            'sessions' => $row->sessions,
        ])->toArray();
    }

    /**
     * Cost per hectare for a plot.
     */
    public function costPerHectare(FarmPlot $plot, ?int $cycleId = null): float
    {
        $query = FarmPlotActivity::where('farm_plot_id', $plot->id);
        if ($cycleId) {
            $query->where('crop_cycle_id', $cycleId);
        }
        $totalCost = (float) $query->sum('cost');

        // Add rent cost if applicable
        if ($plot->ownership === 'rented' && $plot->rent_cost > 0) {
            $totalCost += (float) $plot->rent_cost;
        }

        return $plot->area_size > 0 ? round($totalCost / $plot->area_size, 0) : 0;
    }

    /**
     * HPP (cost per kg harvested) for a plot.
     */
    public function hppPerKg(FarmPlot $plot, ?int $cycleId = null): ?float
    {
        $query = FarmPlotActivity::where('farm_plot_id', $plot->id);
        if ($cycleId) {
            $query->where('crop_cycle_id', $cycleId);
        }
        $totalCost = (float) $query->sum('cost');

        if ($plot->ownership === 'rented') {
            $totalCost += (float) $plot->rent_cost;
        }

        // Get harvest from harvest_logs (more accurate) or activities
        $harvestQuery = HarvestLog::where('farm_plot_id', $plot->id);
        if ($cycleId) {
            $harvestQuery->where('crop_cycle_id', $cycleId);
        }
        $totalHarvest = (float) $harvestQuery->sum(DB::raw('total_qty - reject_qty'));

        if ($totalHarvest <= 0) {
            // Fallback to activities
            $actQuery = FarmPlotActivity::where('farm_plot_id', $plot->id)->where('activity_type', 'harvesting');
            if ($cycleId) {
                $actQuery->where('crop_cycle_id', $cycleId);
            }
            $totalHarvest = (float) $actQuery->sum('harvest_qty');
        }

        return $totalHarvest > 0 ? round($totalCost / $totalHarvest, 2) : null;
    }

    /**
     * Yield per hectare for a plot.
     */
    public function yieldPerHectare(FarmPlot $plot, ?int $cycleId = null): float
    {
        $harvestQuery = HarvestLog::where('farm_plot_id', $plot->id);
        if ($cycleId) {
            $harvestQuery->where('crop_cycle_id', $cycleId);
        }
        $totalHarvest = (float) $harvestQuery->sum(DB::raw('total_qty - reject_qty'));

        if ($totalHarvest <= 0) {
            $actQuery = FarmPlotActivity::where('farm_plot_id', $plot->id)->where('activity_type', 'harvesting');
            if ($cycleId) {
                $actQuery->where('crop_cycle_id', $cycleId);
            }
            $totalHarvest = (float) $actQuery->sum('harvest_qty');
        }

        return $plot->area_size > 0 ? round($totalHarvest / $plot->area_size, 1) : 0;
    }

    /**
     * Compare all plots side by side.
     */
    public function comparePlots(int $tenantId): array
    {
        $plots = FarmPlot::where('tenant_id', $tenantId)->where('is_active', true)->orderBy('code')->get();

        return $plots->map(function ($plot) {
            $totalCost = (float) FarmPlotActivity::where('farm_plot_id', $plot->id)->sum('cost');
            if ($plot->ownership === 'rented') {
                $totalCost += (float) $plot->rent_cost;
            }

            $totalHarvest = (float) HarvestLog::where('farm_plot_id', $plot->id)->sum(DB::raw('total_qty - reject_qty'));
            if ($totalHarvest <= 0) {
                $totalHarvest = (float) FarmPlotActivity::where('farm_plot_id', $plot->id)->where('activity_type', 'harvesting')->sum('harvest_qty');
            }

            $totalReject = (float) HarvestLog::where('farm_plot_id', $plot->id)->sum('reject_qty');
            $harvestSessions = HarvestLog::where('farm_plot_id', $plot->id)->count();

            $costPerHa = $plot->area_size > 0 ? round($totalCost / $plot->area_size, 0) : 0;
            $yieldPerHa = $plot->area_size > 0 ? round($totalHarvest / $plot->area_size, 1) : 0;
            $hpp = $totalHarvest > 0 ? round($totalCost / $totalHarvest, 2) : null;

            return [
                'code' => $plot->code,
                'name' => $plot->name,
                'area' => $plot->area_size.' '.$plot->area_unit,
                'crop' => $plot->current_crop ?? '-',
                'status' => $plot->statusLabel(),
                'total_cost' => $totalCost,
                'total_harvest' => $totalHarvest,
                'total_reject' => $totalReject,
                'harvest_sessions' => $harvestSessions,
                'cost_per_ha' => $costPerHa,
                'yield_per_ha' => $yieldPerHa,
                'hpp_per_kg' => $hpp,
                'reject_pct' => ($totalHarvest + $totalReject) > 0 ? round($totalReject / ($totalHarvest + $totalReject) * 100, 1) : 0,
            ];
        })->toArray();
    }

    /**
     * Monthly cost trend for a plot.
     */
    public function monthlyCostTrend(int $plotId, int $months = 6): array
    {
        return FarmPlotActivity::where('farm_plot_id', $plotId)
            ->where('date', '>=', now()->subMonths($months))
            ->selectRaw("DATE_FORMAT(date, '%Y-%m') as month, activity_type, SUM(cost) as total")
            ->groupBy('month', 'activity_type')
            ->orderBy('month')
            ->get()
            ->groupBy('month')
            ->map(fn ($items, $month) => [
                'month' => $month,
                'costs' => $items->pluck('total', 'activity_type')->toArray(),
                'total' => $items->sum('total'),
            ])
            ->values()
            ->toArray();
    }
}
