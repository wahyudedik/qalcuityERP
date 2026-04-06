<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\AdvancedAnalyticsService;
use App\Services\ForecastService;
use Illuminate\Http\Request;

class AnalyticsDashboardController extends Controller
{
    protected $analyticsService;
    protected $forecastService;

    public function __construct(
        AdvancedAnalyticsService $analyticsService,
        ForecastService $forecastService
    ) {
        $this->analyticsService = $analyticsService;
        $this->forecastService = $forecastService;
    }

    /**
     * Main Analytics Dashboard
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        // Business Health Score
        $healthScore = $this->analyticsService->businessHealthScore($tenantId);

        // Quick stats
        $quickStats = $this->getQuickStats($tenantId);

        return view('analytics.dashboard', compact('healthScore', 'quickStats'));
    }

    /**
     * Customer Segmentation & RFM Analysis
     */
    public function customerSegmentation(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $daysBack = $request->input('days', 365);

        $rfmData = $this->analyticsService->rfmAnalysis($tenantId, $daysBack);

        return view('analytics.customer-segmentation', compact('rfmData', 'daysBack'));
    }

    /**
     * Product Profitability Matrix
     */
    public function productProfitability(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $monthsBack = $request->input('months', 6);

        $profitabilityData = $this->analyticsService->productProfitabilityMatrix($tenantId, $monthsBack);

        return view('analytics.product-profitability', compact('profitabilityData', 'monthsBack'));
    }

    /**
     * Employee Performance Metrics
     */
    public function employeePerformance(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $monthsBack = $request->input('months', 3);

        $performanceData = $this->analyticsService->employeePerformanceMetrics($tenantId, $monthsBack);

        return view('analytics.employee-performance', compact('performanceData', 'monthsBack'));
    }

    /**
     * Cashflow Forecasting
     */
    public function cashflowForecast(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $monthsHistory = $request->input('history', 6);
        $monthsForecast = $request->input('forecast', 3);

        $cashflowData = $this->forecastService->cashFlowForecast($tenantId, $monthsHistory, $monthsForecast);
        $revenueData = $this->forecastService->revenueForecast($tenantId, $monthsHistory, $monthsForecast);

        return view('analytics.cashflow-forecast', compact(
            'cashflowData',
            'revenueData',
            'monthsHistory',
            'monthsForecast'
        ));
    }

    /**
     * Churn Risk Prediction
     */
    public function churnRisk(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $daysInactive = $request->input('days', 90);

        $churnData = $this->analyticsService->churnRiskPrediction($tenantId, $daysInactive);

        return view('analytics.churn-risk', compact('churnData', 'daysInactive'));
    }

    /**
     * Seasonal Trend Analysis
     */
    public function seasonalTrends(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $yearsBack = $request->input('years', 2);

        $seasonalData = $this->analyticsService->seasonalTrendAnalysis($tenantId, $yearsBack);

        return view('analytics.seasonal-trends', compact('seasonalData', 'yearsBack'));
    }

    /**
     * API: Get all analytics data (for AJAX calls)
     */
    public function apiGetAllAnalytics()
    {
        $tenantId = auth()->user()->tenant_id;

        return response()->json([
            'health_score' => $this->analyticsService->businessHealthScore($tenantId),
            'rfm_analysis' => $this->analyticsService->rfmAnalysis($tenantId),
            'product_profitability' => $this->analyticsService->productProfitabilityMatrix($tenantId),
            'employee_performance' => $this->analyticsService->employeePerformanceMetrics($tenantId),
            'cashflow_forecast' => $this->forecastService->cashFlowForecast($tenantId),
            'churn_risk' => $this->analyticsService->churnRiskPrediction($tenantId),
            'seasonal_trends' => $this->analyticsService->seasonalTrendAnalysis($tenantId),
        ]);
    }

    /**
     * Helper: Get quick stats for dashboard
     */
    private function getQuickStats(int $tenantId): array
    {
        $today = now()->toDateString();
        $thisMonth = now()->format('Y-m');

        // Today's revenue
        $todayRevenue = \App\Models\SalesOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereDate('date', $today)
            ->sum('total');

        // Month-to-date revenue
        $mtdRevenue = \App\Models\SalesOrder::where('tenant_id', $tenantId)
            ->where('status', 'completed')
            ->whereYear('date', now()->year)
            ->whereMonth('date', now()->month)
            ->sum('total');

        // Total customers
        $totalCustomers = \App\Models\Customer::where('tenant_id', $tenantId)->count();

        // Active products
        $activeProducts = \App\Models\Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->count();

        // Outstanding invoices
        $outstandingInvoices = \App\Models\Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->sum('remaining_amount');

        return [
            'today_revenue' => $todayRevenue,
            'mtd_revenue' => $mtdRevenue,
            'total_customers' => $totalCustomers,
            'active_products' => $activeProducts,
            'outstanding_invoices' => $outstandingInvoices,
        ];
    }
}
