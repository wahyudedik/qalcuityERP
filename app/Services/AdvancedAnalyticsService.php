<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdvancedAnalyticsService
{
    /**
     * Business Health Score (0-100)
     * Composite score dari berbagai metrik bisnis
     */
    public function businessHealthScore(int $tenantId): array
    {
        // Revenue Growth (25%)
        $revenueScore = $this->calculateRevenueGrowthScore($tenantId);

        // Profitability (20%)
        $profitScore = $this->calculateProfitabilityScore($tenantId);

        // Cash Flow Health (20%)
        $cashflowScore = $this->calculateCashFlowScore($tenantId);

        // Customer Retention (15%)
        $retentionScore = $this->calculateCustomerRetentionScore($tenantId);

        // Inventory Health (10%)
        $inventoryScore = $this->calculateInventoryHealthScore($tenantId);

        // Employee Productivity (10%)
        $employeeScore = $this->calculateEmployeeProductivityScore($tenantId);

        // Weighted total
        $totalScore = round(
            ($revenueScore * 0.25) +
            ($profitScore * 0.20) +
            ($cashflowScore * 0.20) +
            ($retentionScore * 0.15) +
            ($inventoryScore * 0.10) +
            ($employeeScore * 0.10),
            1
        );

        return [
            'overall_score' => $totalScore,
            'grade' => $this->scoreToGrade($totalScore),
            'components' => [
                'revenue_growth' => ['score' => $revenueScore, 'weight' => 25],
                'profitability' => ['score' => $profitScore, 'weight' => 20],
                'cash_flow' => ['score' => $cashflowScore, 'weight' => 20],
                'customer_retention' => ['score' => $retentionScore, 'weight' => 15],
                'inventory_health' => ['score' => $inventoryScore, 'weight' => 10],
                'employee_productivity' => ['score' => $employeeScore, 'weight' => 10],
            ],
            'recommendations' => $this->generateHealthRecommendations($tenantId, $totalScore),
        ];
    }

    /**
     * RFM Analysis - Recency, Frequency, Monetary
     */
    public function rfmAnalysis(int $tenantId, int $daysBack = 365): array
    {
        $cutoffDate = now()->subDays($daysBack);

        // Get customer RFM data
        $rfmData = DB::table('sales_orders')
            ->join('customers', 'customers.id', '=', 'sales_orders.customer_id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', 'completed')
            ->where('sales_orders.date', '>=', $cutoffDate)
            ->selectRaw('
                customers.id as customer_id,
                customers.name as customer_name,
                customers.email,
                customers.phone,
                MAX(sales_orders.date) as last_purchase_date,
                DATEDIFF(CURDATE(), MAX(sales_orders.date)) as recency_days,
                COUNT(DISTINCT sales_orders.id) as frequency,
                SUM(sales_orders.total) as monetary,
                AVG(sales_orders.total) as avg_order_value
            ')
            ->groupBy('customers.id', 'customers.name', 'customers.email', 'customers.phone')
            ->get();

        if ($rfmData->isEmpty()) {
            return ['segments' => [], 'summary' => []];
        }

        // Calculate R, F, M scores (1-5 scale)
        $recencyScores = $this->calculateQuintileScores($rfmData->pluck('recency_days')->sort()->values(), true);
        $frequencyScores = $this->calculateQuintileScores($rfmData->pluck('frequency')->sort()->values(), false);
        $monetaryScores = $this->calculateQuintileScores($rfmData->pluck('monetary')->sort()->values(), false);

        // Assign segments
        $segments = [];
        foreach ($rfmData as $customer) {
            $rScore = $recencyScores[$customer->recency_days] ?? 3;
            $fScore = $frequencyScores[$customer->frequency] ?? 3;
            $mScore = $monetaryScores[$customer->monetary] ?? 3;

            $segment = $this->determineRFMSegment($rScore, $fScore, $mScore);

            $segments[] = [
                'customer_id' => $customer->customer_id,
                'customer_name' => $customer->customer_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'recency_days' => $customer->recency_days,
                'frequency' => $customer->frequency,
                'monetary' => $customer->monetary,
                'avg_order_value' => round($customer->avg_order_value, 0),
                'r_score' => $rScore,
                'f_score' => $fScore,
                'm_score' => $mScore,
                'rfm_score' => "{$rScore}{$fScore}{$mScore}",
                'segment' => $segment,
            ];
        }

        // Segment summary
        $totalCustomers = count($segments);
        $segmentSummary = collect($segments)->groupBy('segment')->map(function ($group) use ($totalCustomers) {
            return [
                'count' => $group->count(),
                'percentage' => round($group->count() / $totalCustomers * 100, 1),
                'total_revenue' => $group->sum('monetary'),
                'avg_monetary' => round($group->avg('monetary'), 0),
                'avg_frequency' => round($group->avg('frequency'), 1),
            ];
        })->sortByDesc('total_revenue');

        return [
            'segments' => $segments,
            'summary' => $segmentSummary->toArray(),
            'total_customers' => count($segments),
        ];
    }

    /**
     * Product Profitability Matrix
     */
    public function productProfitabilityMatrix(int $tenantId, int $monthsBack = 6): array
    {
        $startDate = now()->subMonths($monthsBack);

        // Get product profitability data
        $products = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('products', 'products.id', '=', 'sales_order_items.product_id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', 'completed')
            ->where('sales_orders.date', '>=', $startDate)
            ->selectRaw('
                products.id as product_id,
                products.name as product_name,
                products.cost_price,
                products.selling_price,
                SUM(sales_order_items.quantity) as total_qty_sold,
                SUM(sales_order_items.quantity * sales_order_items.unit_price) as total_revenue,
                SUM(sales_order_items.quantity * COALESCE(products.cost_price, 0)) as total_cost,
                COUNT(DISTINCT sales_orders.id) as order_count,
                AVG(sales_order_items.unit_price) as avg_selling_price
            ')
            ->groupBy('products.id', 'products.name', 'products.cost_price', 'products.selling_price')
            ->having('total_qty_sold', '>', 0)
            ->get();

        if ($products->isEmpty()) {
            return ['matrix' => [], 'quadrants' => []];
        }

        // Calculate metrics and assign to quadrants
        $matrix = [];
        foreach ($products as $product) {
            $profit = $product->total_revenue - $product->total_cost;
            $profitMargin = $product->total_revenue > 0
                ? ($profit / $product->total_revenue) * 100
                : 0;

            $revenueContribution = $product->total_revenue;
            $volumeVelocity = $product->total_qty_sold / $monthsBack; // per month

            $quadrant = $this->determineProfitabilityQuadrant($profitMargin, $volumeVelocity, $products);

            $matrix[] = [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'total_revenue' => $product->total_revenue,
                'total_cost' => $product->total_cost,
                'total_profit' => $profit,
                'profit_margin' => round($profitMargin, 1),
                'total_qty_sold' => $product->total_qty_sold,
                'order_count' => $product->order_count,
                'avg_selling_price' => round($product->avg_selling_price, 0),
                'revenue_contribution' => $revenueContribution,
                'volume_velocity' => round($volumeVelocity, 0),
                'quadrant' => $quadrant,
            ];
        }

        // Quadrant summary
        $quadrants = collect($matrix)->groupBy('quadrant')->map(function ($group) {
            return [
                'count' => $group->count(),
                'total_revenue' => $group->sum('total_revenue'),
                'total_profit' => $group->sum('total_profit'),
                'avg_margin' => round($group->avg('profit_margin'), 1),
            ];
        });

        return [
            'matrix' => $matrix,
            'quadrants' => $quadrants->toArray(),
            'total_products' => count($matrix),
        ];
    }

    /**
     * Employee Performance Metrics
     */
    public function employeePerformanceMetrics(int $tenantId, int $monthsBack = 3): array
    {
        $startDate = now()->subMonths($monthsBack);

        // Sales performance by employee
        $salesPerformance = DB::table('sales_orders')
            ->join('users', 'users.id', '=', 'sales_orders.created_by')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', 'completed')
            ->where('sales_orders.date', '>=', $startDate)
            ->selectRaw('
                users.id as employee_id,
                users.name as employee_name,
                COUNT(sales_orders.id) as total_orders,
                SUM(sales_orders.total) as total_revenue,
                AVG(sales_orders.total) as avg_order_value,
                COUNT(DISTINCT sales_orders.customer_id) as unique_customers,
                MIN(sales_orders.date) as first_sale,
                MAX(sales_orders.date) as last_sale
            ')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Calculate performance scores
        $metrics = [];
        foreach ($salesPerformance as $emp) {
            $performanceScore = $this->calculateEmployeeScore($emp, $salesPerformance);

            $metrics[] = [
                'employee_id' => $emp->employee_id,
                'employee_name' => $emp->employee_name,
                'total_orders' => $emp->total_orders,
                'total_revenue' => $emp->total_revenue,
                'avg_order_value' => round($emp->avg_order_value, 0),
                'unique_customers' => $emp->unique_customers,
                'conversion_rate' => $emp->total_orders > 0
                    ? round(($emp->unique_customers / $emp->total_orders) * 100, 1)
                    : 0,
                'performance_score' => $performanceScore,
                'rank' => 0, // Will be set after sorting
                'trend' => $this->calculateSalesTrend($tenantId, $emp->employee_id, $monthsBack),
            ];
        }

        // Sort by revenue and assign ranks
        usort($metrics, function ($a, $b) {
            return $b['total_revenue'] <=> $a['total_revenue'];
        });

        foreach ($metrics as $i => &$metric) {
            $metric['rank'] = $i + 1;
        }

        return [
            'employees' => $metrics,
            'summary' => [
                'total_employees' => count($metrics),
                'top_performer' => $metrics[0] ?? null,
                'avg_revenue_per_employee' => count($metrics) > 0
                    ? round(array_sum(array_column($metrics, 'total_revenue')) / count($metrics), 0)
                    : 0,
            ],
        ];
    }

    /**
     * Churn Risk Prediction
     */
    public function churnRiskPrediction(int $tenantId, int $daysInactive = 90): array
    {
        $cutoffDate = now()->subDays($daysInactive);

        // Get all customers with purchase history
        $customers = Customer::where('tenant_id', $tenantId)
            ->withCount([
                'salesOrders' => function ($query) use ($cutoffDate) {
                    $query->where('status', 'completed')
                        ->where('date', '>=', $cutoffDate);
                }
            ])
            ->withSum([
                'salesOrders' => function ($query) {
                    $query->where('status', 'completed')
                        ->where('date', '>=', now()->subDays(365));
                }
            ], 'total')
            ->get();

        $atRiskCustomers = [];
        foreach ($customers as $customer) {
            $churnRisk = $this->calculateChurnRisk($customer, $daysInactive);

            if ($churnRisk['risk_level'] !== 'low') {
                $atRiskCustomers[] = array_merge([
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'last_purchase_date' => $customer->sales_orders_max_date ?? null,
                    'days_since_last_purchase' => $customer->sales_orders_max_date
                        ? now()->diffInDays($customer->sales_orders_max_date)
                        : null,
                    'total_orders_last_year' => $customer->sales_orders_count,
                    'total_revenue_last_year' => $customer->sales_orders_sum_total ?? 0,
                ], $churnRisk);
            }
        }

        // Sort by risk score
        usort($atRiskCustomers, function ($a, $b) {
            return $b['risk_score'] <=> $a['risk_score'];
        });

        // Risk level summary
        $riskSummary = collect($atRiskCustomers)->groupBy('risk_level')->map->count();

        return [
            'at_risk_customers' => $atRiskCustomers,
            'summary' => [
                'total_analyzed' => $customers->count(),
                'high_risk' => $riskSummary['high'] ?? 0,
                'medium_risk' => $riskSummary['medium'] ?? 0,
                'low_risk' => $riskSummary['low'] ?? 0,
                'total_revenue_at_risk' => collect($atRiskCustomers)->sum('total_revenue_last_year'),
            ],
        ];
    }

    /**
     * Seasonal Trend Analysis
     */
    public function seasonalTrendAnalysis(int $tenantId, int $yearsBack = 2): array
    {
        $startDate = now()->subYears($yearsBack);

        // Monthly revenue trends
        $monthlyTrends = DB::table('sales_orders')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->where('date', '>=', $startDate)
            ->selectRaw('
                YEAR(date) as year,
                MONTH(date) as month,
                MONTHNAME(date) as month_name,
                COUNT(*) as order_count,
                SUM(total) as total_revenue,
                AVG(total) as avg_order_value
            ')
            ->groupBy('year', 'month', 'month_name')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Calculate seasonality index
        $seasonalIndex = $this->calculateSeasonalIndex($monthlyTrends);

        // Year-over-year comparison
        $yoyComparison = $this->calculateYoYComparison($monthlyTrends);

        // Peak seasons
        $peakSeasons = $this->identifyPeakSeasons($monthlyTrends);

        return [
            'monthly_trends' => $monthlyTrends,
            'seasonal_index' => $seasonalIndex,
            'yoy_comparison' => $yoyComparison,
            'peak_seasons' => $peakSeasons,
            'insights' => $this->generateSeasonalInsights($monthlyTrends, $peakSeasons),
        ];
    }

    // ==================== HELPER METHODS ====================

    private function calculateRevenueGrowthScore(int $tenantId): float
    {
        $thisMonth = SalesOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');

        $lastMonth = SalesOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereMonth('date', now()->subMonth()->month)
            ->whereYear('date', now()->subMonth()->year)
            ->sum('total');

        if ($lastMonth <= 0)
            return 50;

        $growthRate = (($thisMonth - $lastMonth) / $lastMonth) * 100;

        // Score: 0-100 based on growth rate
        return min(100, max(0, 50 + ($growthRate * 2)));
    }

    private function calculateProfitabilityScore(int $tenantId): float
    {
        $revenue = SalesOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereMonth('date', now()->month)
            ->sum('total');

        $cost = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_orders.id', '=', 'sales_order_items.sales_order_id')
            ->join('products', 'products.id', '=', 'sales_order_items.product_id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', 'completed')
            ->whereMonth('sales_orders.date', now()->month)
            ->sum(DB::raw('sales_order_items.quantity * COALESCE(products.cost_price, 0)'));

        if ($revenue <= 0)
            return 0;

        $margin = (($revenue - $cost) / $revenue) * 100;

        return min(100, max(0, $margin * 2)); // Normalize to 0-100
    }

    private function calculateCashFlowScore(int $tenantId): float
    {
        $forecastService = app(ForecastService::class);
        $forecast = $forecastService->cashFlowForecast($tenantId, 3, 3);

        $recentNetCash = collect($forecast)->filter(fn($item) => $item['type'] === 'actual')
            ->take(3)->avg('net');

        if ($recentNetCash >= 0) {
            return min(100, 70 + ($recentNetCash > 10000000 ? 30 : 20));
        } else {
            return max(0, 50 + ($recentNetCash / 1000000)); // Penalty for negative cash flow
        }
    }

    private function calculateCustomerRetentionScore(int $tenantId): float
    {
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();

        $repeatCustomers = DB::table('sales_orders')
            ->where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->groupBy('customer_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        if ($totalCustomers <= 0)
            return 50;

        $retentionRate = ($repeatCustomers / $totalCustomers) * 100;

        return min(100, $retentionRate);
    }

    private function calculateInventoryHealthScore(int $tenantId): float
    {
        $totalProducts = Product::where('tenant_id', $tenantId)->count();

        $lowStock = DB::table('product_stocks')
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->where('products.tenant_id', $tenantId)
            ->whereColumn('product_stocks.quantity', '<=', 'products.minimum_stock')
            ->count();

        if ($totalProducts <= 0)
            return 50;

        $healthyPercentage = (($totalProducts - $lowStock) / $totalProducts) * 100;

        return min(100, $healthyPercentage);
    }

    private function calculateEmployeeProductivityScore(int $tenantId): float
    {
        $employeeMetrics = $this->employeePerformanceMetrics($tenantId, 3);

        if (empty($employeeMetrics['employees']))
            return 50;

        $avgRevenue = $employeeMetrics['summary']['avg_revenue_per_employee'];

        // Score based on average revenue per employee
        if ($avgRevenue >= 50000000)
            return 100;
        if ($avgRevenue >= 30000000)
            return 80;
        if ($avgRevenue >= 20000000)
            return 60;
        if ($avgRevenue >= 10000000)
            return 40;

        return 20;
    }

    private function scoreToGrade(float $score): string
    {
        if ($score >= 90)
            return 'A';
        if ($score >= 80)
            return 'B+';
        if ($score >= 70)
            return 'B';
        if ($score >= 60)
            return 'C+';
        if ($score >= 50)
            return 'C';
        if ($score >= 40)
            return 'D';
        return 'F';
    }

    private function generateHealthRecommendations(int $tenantId, float $totalScore): array
    {
        $recommendations = [];

        if ($totalScore < 60) {
            $recommendations[] = 'Fokus pada peningkatan revenue dan profitabilitas';
        }

        $revenueScore = $this->calculateRevenueGrowthScore($tenantId);
        if ($revenueScore < 50) {
            $recommendations[] = 'Revenue menurun - pertimbangkan strategi marketing baru';
        }

        $cashflowScore = $this->calculateCashFlowScore($tenantId);
        if ($cashflowScore < 50) {
            $recommendations[] = 'Arus kas negatif - percepat penagihan piutang';
        }

        return $recommendations ?: ['Bisnis dalam kondisi sehat - pertahankan performa'];
    }

    private function calculateQuintileScores($values, bool $lowerIsBetter = false): array
    {
        $scores = [];
        $count = count($values);

        if ($count === 0)
            return [];

        foreach ($values as $value) {
            $percentile = array_search($value, $values) / $count;

            if ($lowerIsBetter) {
                $score = 5 - floor($percentile * 5);
            } else {
                $score = floor($percentile * 5) + 1;
            }

            $scores[$value] = max(1, min(5, $score));
        }

        return $scores;
    }

    private function determineRFMSegment(int $r, int $f, int $m): string
    {
        if ($r >= 4 && $f >= 4 && $m >= 4)
            return 'Champions';
        if ($r >= 4 && $f >= 3 && $m >= 3)
            return 'Loyal Customers';
        if ($r >= 4 && $f <= 2)
            return 'New Customers';
        if ($r >= 3 && $f >= 3 && $m >= 3)
            return 'Potential Loyalists';
        if ($r >= 3 && $f >= 1 && $m >= 3)
            return 'Promising';
        if ($r <= 2 && $f >= 3 && $m >= 3)
            return 'Need Attention';
        if ($r <= 2 && $f >= 3 && $m <= 2)
            return 'About to Sleep';
        if ($r <= 2 && $f <= 2 && $m >= 3)
            return 'At Risk';
        if ($r <= 2 && $f <= 2 && $m <= 2)
            return 'Lost';

        return 'Others';
    }

    private function determineProfitabilityQuadrant(float $margin, float $velocity, $allProducts): string
    {
        $avgMargin = $allProducts->avg(fn($p) => ($p->total_revenue - $p->total_cost) / $p->total_revenue * 100);
        $avgVelocity = $allProducts->avg('total_qty_sold');

        if ($margin >= $avgMargin && $velocity >= $avgVelocity)
            return 'Stars';
        if ($margin >= $avgMargin && $velocity < $avgVelocity)
            return 'Cash Cows';
        if ($margin < $avgMargin && $velocity >= $avgVelocity)
            return 'Question Marks';
        return 'Dogs';
    }

    private function calculateEmployeeScore($emp, $allEmployees): float
    {
        $maxRevenue = $allEmployees->max('total_revenue');
        if ($maxRevenue <= 0)
            return 50;

        $revenueScore = ($emp->total_revenue / $maxRevenue) * 100;

        return round(min(100, $revenueScore), 1);
    }

    private function calculateSalesTrend(int $tenantId, int $employeeId, int $monthsBack): string
    {
        $thisMonth = SalesOrder::where('tenant_id', $tenantId)
            ->where('created_by', $employeeId)
            ->where('status', 'completed')
            ->whereMonth('date', now()->month)
            ->sum('total');

        $lastMonth = SalesOrder::where('tenant_id', $tenantId)
            ->where('created_by', $employeeId)
            ->where('status', 'completed')
            ->whereMonth('date', now()->subMonth()->month)
            ->sum('total');

        if ($lastMonth <= 0)
            return 'stable';

        $change = (($thisMonth - $lastMonth) / $lastMonth) * 100;

        if ($change > 10)
            return 'up';
        if ($change < -10)
            return 'down';
        return 'stable';
    }

    private function calculateChurnRisk($customer, int $daysInactive): array
    {
        $daysSinceLastPurchase = $customer->sales_orders_max_date
            ? now()->diffInDays($customer->sales_orders_max_date)
            : 999;

        $riskScore = 0;

        // Recency factor
        if ($daysSinceLastPurchase > $daysInactive) {
            $riskScore += 40;
        } elseif ($daysSinceLastPurchase > $daysInactive / 2) {
            $riskScore += 20;
        }

        // Frequency factor
        if ($customer->sales_orders_count <= 1) {
            $riskScore += 30;
        }

        // Monetary factor
        if (($customer->sales_orders_sum_total ?? 0) < 1000000) {
            $riskScore += 30;
        }

        $riskLevel = match (true) {
            $riskScore >= 70 => 'high',
            $riskScore >= 40 => 'medium',
            default => 'low',
        };

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'risk_factors' => $this->getChurnRiskFactors($riskScore),
        ];
    }

    private function getChurnRiskFactors(int $riskScore): array
    {
        $factors = [];

        if ($riskScore >= 70) {
            $factors[] = 'Tidak ada pembelian dalam waktu lama';
            $factors[] = 'Frekuensi pembelian rendah';
        } elseif ($riskScore >= 40) {
            $factors[] = 'Aktivitas pembelian menurun';
        }

        return $factors;
    }

    private function calculateSeasonalIndex($monthlyTrends): array
    {
        // Group by month across years
        $byMonth = $monthlyTrends->groupBy('month');

        $index = [];
        foreach ($byMonth as $month => $data) {
            $avgRevenue = $data->avg('total_revenue');
            $overallAvg = $monthlyTrends->avg('total_revenue');

            $index[] = [
                'month' => $month,
                'month_name' => $data->first()->month_name,
                'avg_revenue' => round($avgRevenue, 0),
                'seasonal_index' => $overallAvg > 0 ? round($avgRevenue / $overallAvg, 2) : 1,
            ];
        }

        return $index;
    }

    private function calculateYoYComparison($monthlyTrends): array
    {
        $byYear = $monthlyTrends->groupBy('year');

        if ($byYear->count() < 2)
            return [];

        $years = $byYear->keys()->sort()->values();
        $currentYear = $years->last();
        $previousYear = $years[$years->count() - 2];

        $currentData = $byYear[$currentYear];
        $previousData = $byYear[$previousYear];

        $comparison = [];
        foreach ($currentData as $month => $current) {
            $prev = $previousData->firstWhere('month', $month);

            if ($prev) {
                $growth = $prev->total_revenue > 0
                    ? (($current->total_revenue - $prev->total_revenue) / $prev->total_revenue) * 100
                    : 0;

                $comparison[] = [
                    'month' => $month,
                    'month_name' => $current->month_name,
                    'current_year_revenue' => $current->total_revenue,
                    'previous_year_revenue' => $prev->total_revenue,
                    'growth_percentage' => round($growth, 1),
                ];
            }
        }

        return $comparison;
    }

    private function identifyPeakSeasons($monthlyTrends): array
    {
        $avgRevenue = $monthlyTrends->avg('total_revenue');
        $stdDev = sqrt($monthlyTrends->avg(fn($t) => pow($t->total_revenue - $avgRevenue, 2)));

        $threshold = $avgRevenue + ($stdDev * 0.5);

        return $monthlyTrends->filter(fn($t) => $t->total_revenue >= $threshold)
            ->map(fn($t) => [
                'year' => $t->year,
                'month' => $t->month,
                'month_name' => $t->month_name,
                'revenue' => $t->total_revenue,
                'orders' => $t->order_count,
            ])
            ->values()
            ->toArray();
    }

    private function generateSeasonalInsights($monthlyTrends, $peakSeasons): array
    {
        $insights = [];

        if (!empty($peakSeasons)) {
            $peakMonths = collect($peakSeasons)->pluck('month_name')->unique();
            $insights[] = "Peak season terjadi di bulan: " . $peakMonths->join(', ');
        }

        $latestMonth = $monthlyTrends->last();
        $previousMonth = $monthlyTrends->slice(-2, 1)->first();

        if ($latestMonth && $previousMonth && $previousMonth->total_revenue > 0) {
            $momGrowth = (($latestMonth->total_revenue - $previousMonth->total_revenue) / $previousMonth->total_revenue) * 100;

            if ($momGrowth > 10) {
                $insights[] = "Revenue bulan ini naik " . round($momGrowth, 1) . "% vs bulan lalu";
            } elseif ($momGrowth < -10) {
                $insights[] = "Revenue bulan ini turun " . abs(round($momGrowth, 1)) . "% vs bulan lalu";
            }
        }

        return $insights;
    }
}
