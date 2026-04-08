<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdvancedAnalyticsDashboardController extends Controller
{
    /**
     * Main Advanced Analytics Dashboard
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $startDate = $request->input('start_date', now()->subDays(30)->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $module = $request->input('module', 'all');

        // Real-time KPIs
        $kpis = $this->getRealTimeKPIs($tenantId, $startDate, $endDate, $module);

        // Revenue trend
        $revenueTrend = $this->getRevenueTrend($tenantId, $startDate, $endDate);

        // Top metrics
        $topMetrics = $this->getTopMetrics($tenantId, $startDate, $endDate);

        return view('analytics.advanced-dashboard', compact(
            'kpis',
            'revenueTrend',
            'topMetrics',
            'startDate',
            'endDate',
            'module'
        ));
    }

    /**
     * Real-time KPI Tracking
     */
    protected function getRealTimeKPIs(int $tenantId, string $startDate, string $endDate, string $module = 'all'): array
    {
        $cacheKey = "analytics_kpis_{$tenantId}_{$startDate}_{$endDate}_{$module}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tenantId, $startDate, $endDate, $module) {
            $whereClause = ['tenant_id' => $tenantId];
            if ($module !== 'all') {
                $whereClause['module'] = $module;
            }

            return [
                'revenue' => [
                    'daily' => Invoice::where($whereClause)
                        ->whereBetween('invoice_date', [$startDate, $endDate])
                        ->sum('total_amount'),
                    'weekly' => Invoice::where($whereClause)
                        ->whereBetween('invoice_date', [now()->subDays(7)->format('Y-m-d'), now()->format('Y-m-d')])
                        ->sum('total_amount'),
                    'monthly' => Invoice::where($whereClause)
                        ->whereBetween('invoice_date', [now()->subDays(30)->format('Y-m-d'), now()->format('Y-m-d')])
                        ->sum('total_amount'),
                    'growth' => $this->calculateGrowth($tenantId, $startDate, $endDate),
                ],
                'orders' => [
                    'total' => SalesOrder::where($whereClause)
                        ->whereBetween('order_date', [$startDate, $endDate])
                        ->count(),
                    'completed' => SalesOrder::where($whereClause)
                        ->whereBetween('order_date', [$startDate, $endDate])
                        ->where('status', 'completed')
                        ->count(),
                    'conversion_rate' => $this->calculateConversionRate($tenantId, $startDate, $endDate),
                    'avg_value' => SalesOrder::where($whereClause)
                        ->whereBetween('order_date', [$startDate, $endDate])
                        ->avg('total_amount') ?? 0,
                ],
                'inventory' => [
                    'total_products' => Product::where('tenant_id', $tenantId)->count(),
                    'in_stock' => ProductStock::where('tenant_id', $tenantId)
                        ->where('quantity', '>', 0)
                        ->count(),
                    'low_stock' => ProductStock::where('tenant_id', $tenantId)
                        ->where('quantity', '<=', DB::raw('reorder_level'))
                        ->count(),
                    'out_of_stock' => ProductStock::where('tenant_id', $tenantId)
                        ->where('quantity', '<=', 0)
                        ->count(),
                    'turnover_rate' => $this->calculateInventoryTurnover($tenantId),
                ],
                'customers' => [
                    'total' => Customer::where('tenant_id', $tenantId)->count(),
                    'new_this_month' => Customer::where('tenant_id', $tenantId)
                        ->whereMonth('created_at', now()->month)
                        ->count(),
                    'active' => Customer::where('tenant_id', $tenantId)
                        ->whereHas('salesOrders', function ($q) use ($tenantId) {
                            $q->where('tenant_id', $tenantId)
                                ->where('order_date', '>=', now()->subDays(30)->format('Y-m-d'));
                        })
                        ->count(),
                    'retention_rate' => $this->calculateCustomerRetention($tenantId),
                ],
            ];
        });
    }

    /**
     * Revenue Trend Data
     */
    protected function getRevenueTrend(int $tenantId, string $startDate, string $endDate): array
    {
        $cacheKey = "revenue_trend_{$tenantId}_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($tenantId, $startDate, $endDate) {
            return Invoice::where('tenant_id', $tenantId)
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->selectRaw('DATE(invoice_date) as date, SUM(total_amount) as revenue, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($row) => [
                    'date' => $row->date,
                    'revenue' => (float) $row->revenue,
                    'orders' => (int) $row->count,
                ])
                ->toArray();
        });
    }

    /**
     * Top Metrics (Top Products, Customers, Categories)
     */
    protected function getTopMetrics(int $tenantId, string $startDate, string $endDate): array
    {
        $cacheKey = "top_metrics_{$tenantId}_{$startDate}_{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($tenantId, $startDate, $endDate) {
            return [
                'top_products' => OrderItem::where('tenant_id', $tenantId)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(total_amount) as total_revenue')
                    ->groupBy('product_id')
                    ->orderByDesc('total_revenue')
                    ->limit(10)
                    ->with('product:id,name')
                    ->get(),

                'top_customers' => Customer::where('tenant_id', $tenantId)
                    ->whereHas('salesOrders', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('order_date', [$startDate, $endDate]);
                    })
                    ->withSum([
                        'salesOrders as total_spent' => function ($q) use ($startDate, $endDate) {
                            $q->whereBetween('order_date', [$startDate, $endDate]);
                        }
                    ], 'total_amount')
                    ->orderByDesc('total_spent')
                    ->limit(10)
                    ->get(),

                'top_categories' => Product::where('tenant_id', $tenantId)
                    ->whereHas('orderItems', function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('created_at', [$startDate, $endDate]);
                    })
                    ->selectRaw('category, COUNT(*) as sales_count, SUM(order_items.total_amount) as total_revenue')
                    ->join('order_items', 'products.id', '=', 'order_items.product_id')
                    ->groupBy('category')
                    ->orderByDesc('total_revenue')
                    ->limit(10)
                    ->get(),
            ];
        });
    }

    /**
     * AI Predictive Analytics
     */
    public function predictiveAnalytics(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $predictionType = $request->input('type', 'sales');
        $horizon = $request->input('horizon', 30); // days

        $prediction = match ($predictionType) {
            'sales' => $this->predictSales($tenantId, $horizon),
            'inventory' => $this->predictInventoryDemand($tenantId, $horizon),
            'churn' => $this->predictCustomerChurn($tenantId),
            default => $this->predictSales($tenantId, $horizon),
        };

        return view('analytics.predictive', compact('prediction', 'predictionType'));
    }

    /**
     * Sales Forecasting with AI
     */
    protected function predictSales(int $tenantId, int $horizon): array
    {
        $cacheKey = "sales_forecast_{$tenantId}_{$horizon}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($tenantId, $horizon) {
            // Historical data (last 90 days)
            $historicalData = Invoice::where('tenant_id', $tenantId)
                ->where('invoice_date', '>=', now()->subDays(90)->format('Y-m-d'))
                ->selectRaw('DATE(invoice_date) as date, SUM(total_amount) as revenue')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Simple linear regression for forecast
            $forecast = $this->linearRegressionForecast($historicalData, $horizon);

            // AI Enhancement with Gemini (optional)
            $aiInsights = null;
            if (config('services.gemini.api_key')) {
                try {
                    $geminiService = app(GeminiService::class);
                    $prompt = "Analyze this sales data and provide 3 key insights with actionable recommendations:\n" .
                        json_encode($historicalData->take(30)->toArray());

                    $aiInsights = $geminiService->generateText($prompt, temperature: 0.7);
                } catch (\Throwable $e) {
                    \Log::warning("AI sales forecast failed: {$e->getMessage()}");
                }
            }

            return [
                'historical' => $historicalData,
                'forecast' => $forecast,
                'confidence_interval' => $this->calculateConfidenceInterval($historicalData),
                'ai_insights' => $aiInsights,
                'accuracy' => $this->calculateForecastAccuracy($historicalData),
            ];
        });
    }

    /**
     * Inventory Demand Prediction
     */
    protected function predictInventoryDemand(int $tenantId, int $horizon): array
    {
        $cacheKey = "inventory_demand_{$tenantId}_{$horizon}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($tenantId, $horizon) {
            // Product demand history
            $productDemand = OrderItem::where('tenant_id', $tenantId)
                ->where('created_at', '>=', now()->subDays(90)->format('Y-m-d'))
                ->selectRaw('product_id, DATE(created_at) as date, SUM(quantity) as demand')
                ->groupBy('product_id', 'date')
                ->orderBy('date')
                ->get();

            // Predict demand for next $horizon days
            $predictions = [];
            $topProducts = $productDemand->groupBy('product_id')
                ->map(fn($items) => $items->sum('demand'))
                ->sortDesc()
                ->take(20);

            foreach ($topProducts as $productId => $totalDemand) {
                $productHistory = $productDemand->where('product_id', $productId);
                $avgDailyDemand = $totalDemand / 90;

                $predictions[] = [
                    'product_id' => $productId,
                    'product_name' => Product::find($productId)?->name ?? 'Unknown',
                    'avg_daily_demand' => round($avgDailyDemand, 2),
                    'predicted_demand_30d' => round($avgDailyDemand * 30),
                    'current_stock' => ProductStock::where('tenant_id', $tenantId)
                        ->where('product_id', $productId)
                        ->first()?->quantity ?? 0,
                    'reorder_needed' => $avgDailyDemand * 30 > (ProductStock::where('tenant_id', $tenantId)
                        ->where('product_id', $productId)
                        ->first()?->quantity ?? 0),
                    'recommended_order_qty' => max(0, round($avgDailyDemand * 30 - (ProductStock::where('tenant_id', $tenantId)
                        ->where('product_id', $productId)
                        ->first()?->quantity ?? 0))),
                ];
            }

            return [
                'predictions' => $predictions,
                'total_products_analyzed' => count($predictions),
                'products_needing_reorder' => collect($predictions)->where('reorder_needed', true)->count(),
            ];
        });
    }

    /**
     * Customer Churn Prediction
     */
    protected function predictCustomerChurn(int $tenantId): array
    {
        $cacheKey = "churn_prediction_{$tenantId}";

        return Cache::remember($cacheKey, now()->addHours(12), function () use ($tenantId) {
            $customers = Customer::where('tenant_id', $tenantId)
                ->withCount([
                    'salesOrders as order_count_90d' => function ($q) {
                        $q->where('order_date', '>=', now()->subDays(90)->format('Y-m-d'));
                    }
                ])
                ->withSum([
                    'salesOrders as total_spent_90d' => function ($q) {
                        $q->where('order_date', '>=', now()->subDays(90)->format('Y-m-d'));
                    }
                ], 'total_amount')
                ->get();

            $churnPredictions = $customers->map(function ($customer) {
                $daysSinceLastOrder = $customer->salesOrders()->latest('order_date')->first()?->order_date
                    ? now()->diffInDays($customer->salesOrders()->latest('order_date')->first()->order_date)
                    : 999;

                // Simple churn risk model
                $riskScore = 0;
                if ($daysSinceLastOrder > 60)
                    $riskScore += 40;
                elseif ($daysSinceLastOrder > 30)
                    $riskScore += 20;

                if ($customer->order_count_90d == 0)
                    $riskScore += 30;
                elseif ($customer->order_count_90d < 3)
                    $riskScore += 15;

                if ($customer->total_spent_90d < 1000000)
                    $riskScore += 20;

                $riskScore = min(100, $riskScore);

                return [
                    'customer' => $customer,
                    'risk_score' => $riskScore,
                    'risk_level' => $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low'),
                    'days_since_last_order' => $daysSinceLastOrder,
                    'order_count_90d' => $customer->order_count_90d,
                    'total_spent_90d' => $customer->total_spent_90d ?? 0,
                ];
            });

            return [
                'customers' => $churnPredictions->sortByDesc('risk_score')->values(),
                'high_risk_count' => $churnPredictions->where('risk_level', 'high')->count(),
                'medium_risk_count' => $churnPredictions->where('risk_level', 'medium')->count(),
                'low_risk_count' => $churnPredictions->where('risk_level', 'low')->count(),
            ];
        });
    }

    /**
     * Custom Report Builder
     */
    public function reportBuilder(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $metrics = $request->input('metrics', []);
        $dateRange = $request->input('date_range', '30d');
        $filters = $request->only(['module', 'category', 'status']);

        return view('analytics.report-builder', compact('metrics', 'dateRange', 'filters'));
    }

    /**
     * Generate Custom Report
     */
    public function generateReport(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $validated = $request->validate([
            'metrics' => 'required|array',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        $report = $this->buildCustomReport($tenantId, $validated);

        return match ($validated['format']) {
            'pdf' => $this->exportToPdf($report),
            'excel' => $this->exportToExcel($report),
            'csv' => $this->exportToCsv($report),
        };
    }

    /**
     * Scheduled Reports
     */
    public function scheduledReports(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $schedules = \App\Models\ScheduledReport::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get();

        return view('analytics.scheduled-reports', compact('schedules'));
    }

    /**
     * Create Scheduled Report
     */
    public function createScheduledReport(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'metrics' => 'required|array',
            'frequency' => 'required|in:daily,weekly,monthly',
            'recipients' => 'required|array',
            'format' => 'required|in:pdf,excel,csv',
        ]);

        $schedule = \App\Models\ScheduledReport::create([
            'tenant_id' => $tenantId,
            'name' => $validated['name'],
            'metrics' => $validated['metrics'],
            'frequency' => $validated['frequency'],
            'recipients' => $validated['recipients'],
            'format' => $validated['format'],
            'is_active' => true,
            'next_run' => $this->calculateNextRun($validated['frequency']),
        ]);

        return redirect()->route('analytics.scheduled-reports')
            ->with('success', 'Scheduled report created successfully');
    }

    /**
     * Helper: Linear Regression Forecast
     */
    protected function linearRegressionForecast($data, int $horizon): array
    {
        $n = $data->count();
        if ($n < 2)
            return [];

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumX2 = 0;

        foreach ($data as $i => $row) {
            $x = $i;
            $y = $row->revenue;
            $sumX += $x;
            $sumY += $y;
            $sumXY += $x * $y;
            $sumX2 += $x * $x;
        }

        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        $intercept = ($sumY - $slope * $sumX) / $n;

        $forecast = [];
        $lastDate = now();

        for ($i = 0; $i < $horizon; $i++) {
            $forecast[] = [
                'date' => $lastDate->addDay()->format('Y-m-d'),
                'predicted_revenue' => max(0, $slope * ($n + $i) + $intercept),
            ];
        }

        return $forecast;
    }

    /**
     * Helper: Calculate Growth Rate
     */
    protected function calculateGrowth(int $tenantId, string $startDate, string $endDate): float
    {
        $currentPeriod = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total_amount');

        $days = now()->diffInDays(now()->parse($startDate));
        $previousStart = now()->parse($startDate)->subDays($days)->format('Y-m-d');
        $previousEnd = now()->parse($endDate)->subDays($days)->format('Y-m-d');

        $previousPeriod = Invoice::where('tenant_id', $tenantId)
            ->whereBetween('invoice_date', [$previousStart, $previousEnd])
            ->sum('total_amount');

        if ($previousPeriod == 0)
            return 0;

        return (($currentPeriod - $previousPeriod) / $previousPeriod) * 100;
    }

    /**
     * Helper: Calculate Conversion Rate
     */
    protected function calculateConversionRate(int $tenantId, string $startDate, string $endDate): float
    {
        $totalOrders = SalesOrder::where('tenant_id', $tenantId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->count();

        $completedOrders = SalesOrder::where('tenant_id', $tenantId)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->count();

        return $totalOrders > 0 ? ($completedOrders / $totalOrders) * 100 : 0;
    }

    /**
     * Helper: Calculate Inventory Turnover
     */
    protected function calculateInventoryTurnover(int $tenantId): float
    {
        $costOfGoodsSold = OrderItem::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(365)->format('Y-m-d'))
            ->sum('total_amount');

        $avgInventory = ProductStock::where('tenant_id', $tenantId)
            ->avg('quantity') ?? 0;

        return $avgInventory > 0 ? $costOfGoodsSold / $avgInventory : 0;
    }

    /**
     * Helper: Calculate Customer Retention
     */
    protected function calculateCustomerRetention(int $tenantId): float
    {
        $customersThisPeriod = Customer::where('tenant_id', $tenantId)
            ->whereHas('salesOrders', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                    ->where('order_date', '>=', now()->subDays(30)->format('Y-m-d'));
            })
            ->count();

        $customersLastPeriod = Customer::where('tenant_id', $tenantId)
            ->whereHas('salesOrders', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId)
                    ->whereBetween('order_date', [
                        now()->subDays(60)->format('Y-m-d'),
                        now()->subDays(30)->format('Y-m-d'),
                    ]);
            })
            ->count();

        return $customersLastPeriod > 0 ? ($customersThisPeriod / $customersLastPeriod) * 100 : 0;
    }

    /**
     * Helper: Calculate Confidence Interval
     */
    protected function calculateConfidenceInterval($data): array
    {
        $mean = $data->avg('revenue');
        $stdDev = $data->stdDev('revenue') ?? 0;
        $n = $data->count();

        $marginOfError = 1.96 * ($stdDev / sqrt($n)); // 95% confidence

        return [
            'lower' => max(0, $mean - $marginOfError),
            'upper' => $mean + $marginOfError,
            'mean' => $mean,
            'std_dev' => $stdDev,
        ];
    }

    /**
     * Helper: Calculate Forecast Accuracy
     */
    protected function calculateForecastAccuracy($data): float
    {
        // Simple MAPE (Mean Absolute Percentage Error)
        $errors = [];
        foreach ($data as $i => $row) {
            if ($i > 0) {
                $predicted = $data[$i - 1]->revenue;
                $actual = $row->revenue;
                if ($actual > 0) {
                    $errors[] = abs(($actual - $predicted) / $actual);
                }
            }
        }

        return count($errors) > 0 ? (1 - array_sum($errors) / count($errors)) * 100 : 0;
    }

    /**
     * Helper: Build Custom Report
     */
    protected function buildCustomReport(int $tenantId, array $params): array
    {
        return [
            'generated_at' => now(),
            'date_range' => [
                'start' => $params['start_date'],
                'end' => $params['end_date'],
            ],
            'metrics' => $params['metrics'],
            'data' => [], // Populate based on metrics
        ];
    }

    /**
     * Helper: Export to PDF
     */
    protected function exportToPdf(array $report)
    {
        $pdf = \PDF::loadView('analytics.exports.pdf-report', ['report' => $report]);
        return $pdf->download('analytics-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Helper: Export to Excel
     */
    protected function exportToExcel(array $report)
    {
        $excel = new \App\Exports\AnalyticsReportExport($report);
        return \Excel::download($excel, 'analytics-report-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Helper: Export to CSV
     */
    protected function exportToCsv(array $report)
    {
        $filename = 'analytics-report-' . now()->format('Y-m-d') . '.csv';
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, ['Metric', 'Value']);
        foreach ($report['data'] as $metric => $value) {
            fputcsv($handle, [$metric, $value]);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    /**
     * Helper: Calculate Next Run Date
     */
    protected function calculateNextRun(string $frequency): \Carbon\Carbon
    {
        return match ($frequency) {
            'daily' => now()->addDay()->startOfDay(),
            'weekly' => now()->addWeek()->startOfDay(),
            'monthly' => now()->addMonth()->startOfDay(),
        };
    }
}
