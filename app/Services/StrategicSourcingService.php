<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\SourcingOpportunity;
use App\Models\Supplier;
use App\Models\SupplierRfqResponse;

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

        // Count RFQs this month
        $rfqsThisMonth = SupplierRfqResponse::where('tenant_id', $tenantId)
            ->where('submitted_at', '>=', now()->startOfMonth())
            ->count();

        // Calculate average response time (in hours)
        $avgResponseTime = SupplierRfqResponse::where('tenant_id', $tenantId)
            ->where('submitted_at', '>=', now()->subDays(30))
            ->whereNotNull('submitted_at')
            ->whereNotNull('created_at')
            ->get()
            ->map(function ($rfq) {
                return $rfq->created_at->diffInHours($rfq->submitted_at);
            })
            ->avg() ?? 0;

        // Calculate completion rate
        $totalOpportunities = SourcingOpportunity::where('tenant_id', $tenantId)->count();
        $completedOpportunities = SourcingOpportunity::where('tenant_id', $tenantId)
            ->where('status', 'implemented')
            ->count();
        $completionRate = $totalOpportunities > 0 ? round(($completedOpportunities / $totalOpportunities) * 100, 2) : 0;

        return [
            'active_opportunities' => $activeOpportunities->count(),
            'total_potential_spend' => $totalPotentialSpend,
            'total_potential_savings' => $totalPotentialSavings,
            'avg_savings_percentage' => round($avgSavingsPercentage, 2),
            'by_status' => $byStatus,
            'by_priority' => $byPriority,
            'recent_rfqs' => $recentRfqs,
            'rfq_participation_rate' => round($participationRate, 2),
            'rfqs_this_month' => $rfqsThisMonth,
            'potential_savings' => $totalPotentialSavings,
            'avg_response_time' => round($avgResponseTime, 1),
            'completion_rate' => $completionRate,
            'completed_this_month' => SourcingOpportunity::where('tenant_id', $tenantId)
                ->where('status', 'implemented')
                ->whereMonth('actual_completion_date', now()->month)
                ->count(),
        ];
    }

    /**
     * Analyze RFQ responses and compare suppliers
     * BUG-PO-003 FIX: Add comprehensive evaluation criteria beyond just price
     */
    public function analyzeRfqResponses(int $rfqId): array
    {
        $responses = SupplierRfqResponse::where('rfq_id', $rfqId)
            ->with(['supplier', 'supplier.scorecards'])
            ->orderBy('quoted_price')
            ->get();

        if ($responses->isEmpty()) {
            return ['error' => 'No responses found'];
        }

        $lowestPrice = $responses->min('quoted_price');
        $highestPrice = $responses->max('quoted_price');
        $avgPrice = $responses->avg('quoted_price');
        $avgLeadTime = $responses->avg('lead_time_days');

        // Score each response with comprehensive criteria
        $scoredResponses = $responses->map(function ($response) use ($lowestPrice, $avgLeadTime) {
            // 1. Price score (lower is better) - 40% weight (reduced from 50%)
            $priceScore = $lowestPrice > 0 ? ($lowestPrice / $response->quoted_price) * 100 : 0;

            // 2. Lead time score (shorter is better) - 25% weight (reduced from 30%)
            $leadTimeScore = $response->lead_time_days && $avgLeadTime > 0
                ? max(0, 100 - (($response->lead_time_days - $avgLeadTime) / $avgLeadTime * 100))
                : 50;

            // BUG-PO-003 FIX: 3. Supplier rating/scorecard score - 15% weight
            $supplierRatingScore = $this->calculateSupplierRatingScore($response->supplier);

            // BUG-PO-003 FIX: 4. Delivery performance score - 10% weight
            $deliveryPerformanceScore = $this->calculateDeliveryPerformanceScore($response->supplier);

            // BUG-PO-003 FIX: 5. Payment terms score - 10% weight
            $paymentTermsScore = $this->calculatePaymentTermsScore($response);

            // Overall score with new weighted criteria
            $overallScore = (
                ($priceScore * 0.40) +                    // Price: 40%
                ($leadTimeScore * 0.25) +                 // Lead Time: 25%
                ($supplierRatingScore * 0.15) +          // Supplier Rating: 15%
                ($deliveryPerformanceScore * 0.10) +     // Delivery: 10%
                ($paymentTermsScore * 0.10)              // Payment Terms: 10%
            );

            return [
                'response' => $response,
                'price_score' => round($priceScore, 2),
                'lead_time_score' => round($leadTimeScore, 2),
                'supplier_rating_score' => round($supplierRatingScore, 2),
                'delivery_performance_score' => round($deliveryPerformanceScore, 2),
                'payment_terms_score' => round($paymentTermsScore, 2),
                'overall_score' => round($overallScore, 2),
                'rank' => 0, // Will be set after sorting
                'evaluation_criteria' => [
                    'price' => 40,
                    'lead_time' => 25,
                    'supplier_rating' => 15,
                    'delivery_performance' => 10,
                    'payment_terms' => 10,
                ],
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
            'evaluation_methodology' => [
                'price' => '40% - Lower prices receive higher scores',
                'lead_time' => '25% - Shorter delivery times preferred',
                'supplier_rating' => '15% - Based on historical scorecard ratings',
                'delivery_performance' => '10% - On-time delivery track record',
                'payment_terms' => '10% - Better payment terms score higher',
            ],
        ];
    }

    /**
     * BUG-PO-003 FIX: Calculate supplier rating score from scorecards
     */
    protected function calculateSupplierRatingScore($supplier): float
    {
        if (! $supplier || ! $supplier->scorecards || $supplier->scorecards->isEmpty()) {
            return 70; // Default neutral score if no rating data
        }

        // Get latest scorecard
        $latestScorecard = $supplier->scorecards
            ->sortByDesc('period_end')
            ->first();

        if (! $latestScorecard) {
            return 70;
        }

        // Convert overall_score (0-100) to rating score
        return min(100, max(0, $latestScorecard->overall_score ?? 70));
    }

    /**
     * BUG-PO-003 FIX: Calculate delivery performance score
     */
    protected function calculateDeliveryPerformanceScore($supplier): float
    {
        if (! $supplier || ! $supplier->scorecards || $supplier->scorecards->isEmpty()) {
            return 75; // Default good score if no data
        }

        // Get average on-time percentage from scorecards
        $avgOnTimePercentage = $supplier->scorecards
            ->where('on_time_percentage', '>', 0)
            ->avg('on_time_percentage');

        return $avgOnTimePercentage ? min(100, max(0, $avgOnTimePercentage)) : 75;
    }

    /**
     * BUG-PO-003 FIX: Calculate payment terms score
     */
    protected function calculatePaymentTermsScore($response): float
    {
        // If no terms specified, return neutral score
        if (! $response->terms_and_conditions) {
            return 70;
        }

        $terms = strtolower($response->terms_and_conditions);

        // Score based on payment terms (better terms = higher score)
        if (strpos($terms, 'cod') !== false || strpos($terms, 'cash') !== false) {
            return 90; // Cash on delivery - best for buyer
        }

        if (preg_match('/net\s*(\d+)/', $terms, $matches)) {
            $days = (int) $matches[1];
            // Longer payment terms are better for buyer
            if ($days >= 60) {
                return 95; // NET 60+ - excellent
            } elseif ($days >= 45) {
                return 85; // NET 45 - very good
            } elseif ($days >= 30) {
                return 75; // NET 30 - good
            } elseif ($days >= 15) {
                return 65; // NET 15 - acceptable
            } else {
                return 50; // NET <15 - poor for buyer
            }
        }

        // Default score if terms not recognized
        return 70;
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
            $onTimeDelivery = $pos->filter(fn ($po) => $po->delivery_date <= $po->expected_delivery_date)->count();
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

        if (! empty($notes)) {
            $updateData['strategy_notes'] = ($opportunity->strategy_notes ? $opportunity->strategy_notes."\n\n" : '')
                .now()->format('Y-m-d').': '.implode(', ', $notes);
        }

        $opportunity->update($updateData);

        return $opportunity;
    }
}
