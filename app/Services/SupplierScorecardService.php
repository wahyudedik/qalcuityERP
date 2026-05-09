<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierIncident;
use App\Models\SupplierScorecard;
use Carbon\Carbon;

class SupplierScorecardService
{
    /**
     * Generate scorecard for a supplier
     */
    public function generateScorecard(int $supplierId, string $period, Carbon $periodStart, Carbon $periodEnd): SupplierScorecard
    {
        $supplier = Supplier::findOrFail($supplierId);
        $tenantId = $supplier->tenant_id;

        // Calculate all metrics
        $qualityMetrics = $this->calculateQualityMetrics($tenantId, $supplierId, $periodStart, $periodEnd);
        $deliveryMetrics = $this->calculateDeliveryMetrics($tenantId, $supplierId, $periodStart, $periodEnd);
        $costMetrics = $this->calculateCostMetrics($tenantId, $supplierId, $periodStart, $periodEnd);
        $serviceMetrics = $this->calculateServiceMetrics($tenantId, $supplierId, $periodStart, $periodEnd);

        // Calculate overall score (weighted average)
        $overallScore = (
            ($qualityMetrics['score'] * 0.35) +    // Quality: 35%
            ($deliveryMetrics['score'] * 0.30) +   // Delivery: 30%
            ($costMetrics['score'] * 0.20) +       // Cost: 20%
            ($serviceMetrics['score'] * 0.15)      // Service: 15%
        );

        // Create or update scorecard
        $scorecard = SupplierScorecard::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'supplier_id' => $supplierId,
                'period' => $period,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            array_merge($qualityMetrics, $deliveryMetrics, $costMetrics, $serviceMetrics, [
                'overall_score' => round($overallScore, 2),
            ])
        );

        // Update rating and status
        $scorecard->updateRatingAndStatus();

        return $scorecard;
    }

    /**
     * Calculate quality metrics
     */
    protected function calculateQualityMetrics(int $tenantId, int $supplierId, Carbon $start, Carbon $end): array
    {
        $pos = PurchaseOrder::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId)
            ->whereBetween('order_date', [$start, $end])
            ->get();

        $totalDeliveries = $pos->count();
        $defectiveItems = $pos->sum('rejected_quantity') ?? 0;
        $totalItems = $pos->sum('quantity') ?? 0;

        $defectRate = $totalItems > 0 ? ($defectiveItems / $totalItems) * 100 : 0;
        $qualityScore = max(0, 100 - ($defectRate * 2));

        return [
            'quality_score' => round($qualityScore, 2),
            'total_deliveries' => $totalDeliveries,
            'defective_items' => $defectiveItems,
            'defect_rate' => round($defectRate, 2),
        ];
    }

    /**
     * Calculate delivery metrics
     */
    protected function calculateDeliveryMetrics(int $tenantId, int $supplierId, Carbon $start, Carbon $end): array
    {
        $pos = PurchaseOrder::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId)
            ->whereBetween('order_date', [$start, $end])
            ->get();

        $totalDeliveries = $pos->count();
        $onTimeDeliveries = 0;
        $lateDeliveries = 0;
        $totalLeadTime = 0;

        foreach ($pos as $po) {
            if ($po->expected_delivery_date && $po->actual_delivery_date) {
                $leadTime = $po->expected_delivery_date->diffInDays($po->actual_delivery_date);
                $totalLeadTime += $leadTime;

                if ($po->actual_delivery_date <= $po->expected_delivery_date) {
                    $onTimeDeliveries++;
                } else {
                    $lateDeliveries++;
                }
            }
        }

        $onTimePercentage = $totalDeliveries > 0 ? ($onTimeDeliveries / $totalDeliveries) * 100 : 0;
        $avgLeadTime = $totalDeliveries > 0 ? $totalLeadTime / $totalDeliveries : 0;
        $deliveryScore = $onTimePercentage;

        return [
            'delivery_score' => round($deliveryScore, 2),
            'on_time_deliveries' => $onTimeDeliveries,
            'late_deliveries' => $lateDeliveries,
            'on_time_percentage' => round($onTimePercentage, 2),
            'avg_lead_time_days' => round($avgLeadTime, 2),
        ];
    }

    /**
     * Calculate cost metrics
     */
    protected function calculateCostMetrics(int $tenantId, int $supplierId, Carbon $start, Carbon $end): array
    {
        $pos = PurchaseOrder::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId)
            ->whereBetween('order_date', [$start, $end])
            ->get();

        $totalSpend = $pos->sum('total_amount') ?? 0;
        $costSavings = $pos->sum('discount') ?? 0;

        // Price competitiveness (simplified - lower is better)
        $priceCompetitiveness = 80; // Default good score

        // Cost score based on savings percentage
        $savingsPercentage = $totalSpend > 0 ? ($costSavings / $totalSpend) * 100 : 0;
        $costScore = min(100, 70 + ($savingsPercentage * 2));

        return [
            'cost_score' => round($costScore, 2),
            'price_competitiveness' => round($priceCompetitiveness, 2),
            'cost_savings_identified' => $costSavings,
            'total_spend' => round($totalSpend, 2),
        ];
    }

    /**
     * Calculate service metrics
     */
    protected function calculateServiceMetrics(int $tenantId, int $supplierId, Carbon $start, Carbon $end): array
    {
        $incidents = SupplierIncident::where('tenant_id', $tenantId)
            ->where('supplier_id', $supplierId)
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $totalIssues = $incidents->count();
        $resolvedIssues = $incidents->where('status', 'resolved')->count();
        $resolutionRate = $totalIssues > 0 ? ($resolvedIssues / $totalIssues) * 100 : 100;

        // Response time (simulated - would need actual communication data)
        $avgResponseTime = 24; // hours

        // Service score
        $serviceScore = ($resolutionRate * 0.6) + 40; // Base 40 + resolution rate

        return [
            'service_score' => round(min(100, $serviceScore), 2),
            'response_time_hours_avg' => $avgResponseTime,
            'issues_resolved' => $resolvedIssues,
            'total_issues' => $totalIssues,
            'issue_resolution_rate' => round($resolutionRate, 2),
        ];
    }

    /**
     * Get dashboard data
     */
    public function getDashboardData(int $tenantId, string $period = 'monthly'): array
    {
        $now = Carbon::now();

        // Determine period dates
        if ($period === 'monthly') {
            $periodStart = $now->copy()->startOfMonth();
            $periodEnd = $now->copy()->endOfMonth();
        } elseif ($period === 'quarterly') {
            $periodStart = $now->copy()->startOfQuarter();
            $periodEnd = $now->copy()->endOfQuarter();
        } else {
            $periodStart = $now->copy()->startOfYear();
            $periodEnd = $now->copy()->endOfYear();
        }

        $scorecards = SupplierScorecard::where('tenant_id', $tenantId)
            ->where('period', $period)
            ->where('period_start', '>=', $periodStart)
            ->where('period_end', '<=', $periodEnd)
            ->with('supplier')
            ->orderByDesc('overall_score')
            ->get();

        // Metrics
        $totalSuppliers = $scorecards->count();
        $avgScore = $scorecards->avg('overall_score') ?? 0;
        $topPerformers = $scorecards->where('rating', 'A')->count();
        $atRisk = $scorecards->whereIn('rating', ['D', 'F'])->count();

        // Performance by category
        $byCategory = $scorecards->groupBy('supplier.category')
            ->map(function ($group) {
                return [
                    'category' => $group->first()->supplier->category ?? 'Uncategorized',
                    'count' => $group->count(),
                    'avg_score' => round($group->avg('overall_score'), 1),
                ];
            })
            ->values();

        return [
            'period' => $period,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_suppliers' => $totalSuppliers,
            'average_score' => round($avgScore, 1),
            'top_performers' => $topPerformers,
            'at_risk' => $atRisk,
            'scorecards' => $scorecards,
            'by_category' => $byCategory,
        ];
    }

    /**
     * Get supplier performance report
     */
    public function getSupplierPerformanceReport(int $supplierId, int $months = 12): array
    {
        $supplier = Supplier::findOrFail($supplierId);

        $scorecards = SupplierScorecard::where('supplier_id', $supplierId)
            ->orderByDesc('period_end')
            ->limit($months)
            ->get()
            ->sortBy('period_end');

        $currentScorecard = $scorecards->last();
        $currentScore = $currentScorecard ? $currentScorecard->overall_score : 0;
        $currentRating = $currentScorecard ? $currentScorecard->rating : 'N/A';

        // Calculate trend
        $trend = 'stable';
        if ($scorecards->count() >= 2) {
            $scores = $scorecards->pluck('overall_score')->toArray();
            $firstHalf = array_slice($scores, 0, floor(count($scores) / 2));
            $secondHalf = array_slice($scores, floor(count($scores) / 2));

            $avgFirst = count($firstHalf) > 0 ? array_sum($firstHalf) / count($firstHalf) : 0;
            $avgSecond = count($secondHalf) > 0 ? array_sum($secondHalf) / count($secondHalf) : 0;

            if ($avgSecond > $avgFirst + 5) {
                $trend = 'improving';
            } elseif ($avgSecond < $avgFirst - 5) {
                $trend = 'declining';
            }
        }

        // Recent incidents
        $recentIncidents = SupplierIncident::where('supplier_id', $supplierId)
            ->orderByDesc('reported_at')
            ->limit(10)
            ->get();

        return [
            'supplier' => $supplier,
            'scorecards' => $scorecards,
            'current_score' => $currentScore,
            'current_rating' => $currentRating,
            'trend' => $trend,
            'total_incidents' => $recentIncidents->count(),
            'recent_incidents' => $recentIncidents,
        ];
    }

    /**
     * Generate bulk scorecards for all suppliers
     */
    public function generateBulkScorecards(int $tenantId, string $period): int
    {
        $now = Carbon::now();

        if ($period === 'monthly') {
            $periodStart = $now->copy()->startOfMonth();
            $periodEnd = $now->copy()->endOfMonth();
        } elseif ($period === 'quarterly') {
            $periodStart = $now->copy()->startOfQuarter();
            $periodEnd = $now->copy()->endOfQuarter();
        } else {
            $periodStart = $now->copy()->startOfYear();
            $periodEnd = $now->copy()->endOfYear();
        }

        $suppliers = Supplier::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        $generated = 0;
        foreach ($suppliers as $supplier) {
            try {
                $this->generateScorecard($supplier->id, $period, $periodStart, $periodEnd);
                $generated++;
            } catch (\Exception $e) {
                \Log::error("Failed to generate scorecard for supplier {$supplier->id}: ".$e->getMessage());
            }
        }

        return $generated;
    }
}
