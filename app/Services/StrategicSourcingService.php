<?php

namespace App\Services;

use App\Models\SourcingOpportunity;
use App\Models\SupplierRfqResponse;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Carbon\Carbon;

class StrategicSourcingService
{
    /**
     * Identify sourcing opportunities based on spend analysis
     */
    public function identifyOpportunities(int $tenantId): array
    {
        $opportunities = [];

        // 1. High spend categories with multiple suppliers
        $categorySpend = PurchaseOrder::where('tenant_id', $tenantId)
            ->selectRaw('category, COUNT(DISTINCT supplier_id) as supplier_count, SUM(total_amount) as total_spend')
            ->groupBy('category')
            ->having('supplier_count', '>', 1)
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get();

        foreach ($categorySpend as $category) {
            $potentialSavings = $category->total_spend * 0.10; // Assume 10% savings potential

            $opportunities[] = [
                'title' => "Consolidate {$category->category} Suppliers",
                'category' => $category->category,
                'estimated_annual_spend' => $category->total_spend,
                'potential_suppliers_count' => $category->supplier_count,
                'potential_savings' => $potentialSavings,
                'savings_percentage' => 10,
                'priority' => $potentialSavings > 100000000 ? 'high' : 'medium',
                'strategy' => 'Consolidate to 2-3 preferred suppliers for better pricing',
            ];
        }

        // 2. Single-source dependencies (risk mitigation)
        $singleSource = PurchaseOrder::where('tenant_id', $tenantId)
            ->selectRaw('category, COUNT(DISTINCT supplier_id) as supplier_count, SUM(total_amount) as total_spend')
            ->groupBy('category')
            ->having('supplier_count', '=', 1)
            ->orderByDesc('total_spend')
            ->limit(5)
            ->get();

        foreach ($singleSource as $category) {
            $opportunities[] = [
                'title' => "Diversify {$category->category} Supply Base",
                'category' => $category->category,
                'estimated_annual_spend' => $category->total_spend,
                'potential_suppliers_count' => 1,
                'potential_savings' => 0,
                'savings_percentage' => 0,
                'priority' => 'critical',
                'strategy' => 'Identify alternative suppliers to reduce dependency risk',
            ];
        }

        return $opportunities;
    }

    /**
     * Create sourcing opportunity
     */
    public function createOpportunity(array $data): SourcingOpportunity
    {
        return SourcingOpportunity::create($data);
    }

    /**
     * Get sourcing dashboard
     */
    public function getSourcingDashboard(int $tenantId): array
    {
        $activeOpportunities = SourcingOpportunity::where('tenant_id', $tenantId)
            ->whereIn('status', ['identified', 'analyzing', 'rfq_sent', 'negotiated'])
            ->get();

        $totalPotentialSpend = $activeOpportunities->sum('estimated_annual_spend');
        $totalPotentialSavings = $activeOpportunities->sum('potential_savings');
        $avgSavingsPercentage = $activeOpportunities->avg('savings_percentage') ?? 0;

        // Opportunities by status
        $byStatus = $activeOpportunities->groupBy('status')->map->count();

        // Opportunities by priority
        $byPriority = $activeOpportunities->groupBy('priority')->map->count();

        // Recent RFQ activity
        $recentRfqs = SupplierRfqResponse::where('tenant_id', $tenantId)
            ->where('submitted_at', '>=', now()->subDays(30))
            ->with(['supplier', 'rfq'])
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        // Supplier participation rate
        $totalRfqs = SupplierRfqResponse::where('tenant_id', $tenantId)
            ->where('submitted_at', '>=', now()->subDays(30))
            ->count();

        $acceptedRfqs = SupplierRfqResponse::where('tenant_id', $tenantId)
            ->where('status', 'accepted')
            ->where('accepted_at', '>=', now()->subDays(30))
            ->count();

        $participationRate = $totalRfqs > 0 ? ($acceptedRfqs / $totalRfqs) * 100 : 0;

        return [
            'active_opportunities' => $activeOpportunities->count(),
            'total_potential_spend' => $totalPotentialSpend,
            'total_potential_savings' => $totalPotentialSavings,
            'avg_savings_percentage' => round($avgSavingsPercentage, 2),
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'recent_rfqs' => $recentRfqs,
            'rfq_participation_rate' => round($participationRate, 2),
            'completed_this_month' => SourcingOpportunity::where('tenant_id', $tenantId)
                ->where('status', 'implemented')
                ->whereMonth('actual_completion_date', now()->month)
                ->count(),
        ];
    }

    /**
     * Analyze RFQ responses and compare suppliers
     */
    public function analyzeRfqResponses(int $rfqId): array
    {
        $responses = SupplierRfqResponse::where('rfq_id', $rfqId)
            ->with('supplier')
            ->orderBy('quoted_price')
            ->get();

        if ($responses->isEmpty()) {
            return ['error' => 'No responses found'];
        }

        $lowestPrice = $responses->min('quoted_price');
        $highestPrice = $responses->max('quoted_price');
        $avgPrice = $responses->avg('quoted_price');
        $avgLeadTime = $responses->avg('lead_time_days');

        // Score each response
        $scoredResponses = $responses->map(function ($response) use ($lowestPrice, $avgPrice, $avgLeadTime) {
            // Price score (lower is better) - 50% weight
            $priceScore = $lowestPrice > 0 ? ($lowestPrice / $response->quoted_price) * 100 : 0;

            // Lead time score (shorter is better) - 30% weight
            $leadTimeScore = $response->lead_time_days && $avgLeadTime > 0
                ? max(0, 100 - (($response->lead_time_days - $avgLeadTime) / $avgLeadTime * 100))
                : 50;

            // Overall score
            $overallScore = ($priceScore * 0.5) + ($leadTimeScore * 0.3) + 20; // Base 20 for participation

            return [
                'response' => $response,
                'price_score' => round($priceScore, 2),
                'lead_time_score' => round($leadTimeScore, 2),
                'overall_score' => round($overallScore, 2),
                'rank' => 0, // Will be set after sorting
            ];
        })->sortByDesc('overall_score')->values();

        // Assign ranks
        $scoredResponses->each(function (&$item, $key) {
            $item['rank'] = $key + 1;
        });

        return [
            'rfq_id' => $rfqId,
            'total_responses' => $responses->count(),
            'price_range' => [
                'lowest' => $lowestPrice,
                'highest' => $highestPrice,
                'average' => round($avgPrice, 2),
            ],
            'avg_lead_time' => round($avgLeadTime ?? 0, 2),
            'scored_responses' => $scoredResponses,
            'recommended_supplier' => $scoredResponses->first()['response']->supplier->name ?? null,
        ];
    }

    /**
     * Get supplier comparison report
     */
    public function compareSuppliers(array $supplierIds, int $months = 6): array
    {
        $startDate = now()->subMonths($months);
        $comparison = [];

        foreach ($supplierIds as $supplierId) {
            $supplier = Supplier::findOrFail($supplierId);

            // Get performance data
            $pos = PurchaseOrder::where('supplier_id', $supplierId)
                ->where('order_date', '>=', $startDate)
                ->get();

            $totalSpend = $pos->sum('total_amount');
            $onTimeDelivery = $pos->filter(fn($po) => $po->delivery_date <= $po->expected_delivery_date)->count();
            $totalDeliveries = $pos->whereNotNull('delivery_date')->count();
            $onTimeRate = $totalDeliveries > 0 ? ($onTimeDelivery / $totalDeliveries) * 100 : 0;

            $comparison[] = [
                'supplier' => $supplier,
                'total_spend' => $totalSpend,
                'total_orders' => $pos->count(),
                'on_time_delivery_rate' => round($onTimeRate, 2),
                'avg_order_value' => $pos->count() > 0 ? round($totalSpend / $pos->count(), 2) : 0,
            ];
        }

        // Sort by overall performance
        usort($comparison, function ($a, $b) {
            return $b['on_time_delivery_rate'] <=> $a['on_time_delivery_rate'];
        });

        return $comparison;
    }

    /**
     * Track opportunity progress
     */
    public function updateOpportunityStatus(int $opportunityId, string $status, array $notes = []): SourcingOpportunity
    {
        $opportunity = SourcingOpportunity::findOrFail($opportunityId);

        $updateData = ['status' => $status];

        if ($status === 'implemented') {
            $updateData['actual_completion_date'] = now();
        }

        if (!empty($notes)) {
            $updateData['strategy_notes'] = ($opportunity->strategy_notes ? $opportunity->strategy_notes . "\n\n" : '')
                . now()->format('Y-m-d') . ': ' . implode(', ', $notes);
        }

        $opportunity->update($updateData);

        return $opportunity;
    }
}
