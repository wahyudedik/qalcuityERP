<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\CosmeticBatchRecord;
use App\Models\QCTestResult;
use App\Models\CosmeticFormula;
use App\Models\BatchRecall;
use App\Models\ExpiryAlert;
use App\Models\Supplier;
use App\Models\SupplierIncident;
use App\Models\BatchQualityCheck;
use App\Models\ProductRegistration;
use App\Models\QcTestTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CosmeticAnalyticsController extends Controller
{
    /**
     * Analytics Dashboard - Overview of all reports
     */
    public function dashboard(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        // Quick stats
        $stats = [
            'batch_yield_avg' => CosmeticBatchRecord::where('tenant_id', $tenantId)
                ->where('status', 'released')
                ->where('production_date', '>=', $startDate)
                ->avg('yield_percentage') ?? 0,
            'qc_pass_rate' => $this->calculateQcPassRate($tenantId, $startDate),
            'active_registrations' => ProductRegistration::where('tenant_id', $tenantId)
                ->whereIn('status', ['approved', 'pending'])->count(),
            'open_recalls' => BatchRecall::where('tenant_id', $tenantId)
                ->whereIn('status', ['initiated', 'in_progress'])->count(),
            'expiry_alerts_30d' => ExpiryAlert::where('tenant_id', $tenantId)
                ->where('alert_date', '>=', now())
                ->where('alert_date', '<=', now()->addDays(30))
                ->where('is_read', false)->count(),
        ];

        return view('cosmetic.analytics.dashboard', compact('stats', 'period'));
    }

    /**
     * 1. Batch Performance Report
     */
    public function batchPerformance(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Overall metrics
        $metrics = [
            'total_batches' => CosmeticBatchRecord::where('tenant_id', $tenantId)
                ->whereBetween('production_date', [$dateFrom, $dateTo])->count(),
            'avg_yield' => CosmeticBatchRecord::where('tenant_id', $tenantId)
                ->whereBetween('production_date', [$dateFrom, $dateTo])
                ->whereNotNull('yield_percentage')->avg('yield_percentage') ?? 0,
            'qc_pass_rate' => $this->calculateQcPassRate($tenantId, Carbon::parse($dateFrom)),
            'avg_rework_count' => CosmeticBatchRecord::where('tenant_id', $tenantId)
                ->whereBetween('production_date', [$dateFrom, $dateTo])
                ->withCount('reworkLogs')->get()->avg('rework_logs_count') ?? 0,
        ];

        // Batch performance by formula
        $byFormula = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->where('status', 'released')
            ->with('formula')
            ->selectRaw('
                formula_id,
                COUNT(*) as batch_count,
                AVG(yield_percentage) as avg_yield,
                MIN(yield_percentage) as min_yield,
                MAX(yield_percentage) as max_yield
            ')
            ->groupBy('formula_id')
            ->orderByDesc('avg_yield')
            ->get();

        // Trend data for chart
        $trendData = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->whereBetween('production_date', [$dateFrom, $dateTo])
            ->where('status', 'released')
            ->selectRaw('
                DATE(production_date) as date,
                AVG(yield_percentage) as avg_yield,
                COUNT(*) as batch_count
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('cosmetic.analytics.batch-performance', compact('metrics', 'byFormula', 'trendData', 'dateFrom', 'dateTo'));
    }

    /**
     * 2. QC Trend Analysis
     */
    public function qcTrendAnalysis(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));
        $category = $request->get('category');

        // Overall QC stats
        $qcStats = [
            'total_tests' => QCTestResult::where('tenant_id', $tenantId)
                ->whereBetween('test_date', [$dateFrom, $dateTo])->count(),
            'pass_count' => QCTestResult::where('tenant_id', $tenantId)
                ->whereBetween('test_date', [$dateFrom, $dateTo])
                ->where('result', 'pass')->count(),
            'fail_count' => QCTestResult::where('tenant_id', $tenantId)
                ->whereBetween('test_date', [$dateFrom, $dateTo])
                ->where('result', 'fail')->count(),
            'pass_rate' => 0,
        ];
        $qcStats['pass_rate'] = $qcStats['total_tests'] > 0
            ? ($qcStats['pass_count'] / $qcStats['total_tests']) * 100 : 0;

        // Trend by date
        $trendByDate = QCTestResult::where('tenant_id', $tenantId)
            ->whereBetween('test_date', [$dateFrom, $dateTo])
            ->when($category, fn($q) => $q->where('test_category', $category))
            ->selectRaw('
                DATE(test_date) as date,
                COUNT(*) as total_tests,
                SUM(CASE WHEN result = "pass" THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN result = "fail" THEN 1 ELSE 0 END) as failed
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // By test category
        $byCategory = QCTestResult::where('tenant_id', $tenantId)
            ->whereBetween('test_date', [$dateFrom, $dateTo])
            ->selectRaw('
                test_category,
                COUNT(*) as total,
                SUM(CASE WHEN result = "pass" THEN 1 ELSE 0 END) as passed,
                SUM(CASE WHEN result = "fail" THEN 1 ELSE 0 END) as failed
            ')
            ->groupBy('test_category')
            ->orderByDesc('total')
            ->get();

        // Common failure points
        $failurePoints = QCTestResult::where('tenant_id', $tenantId)
            ->whereBetween('test_date', [$dateFrom, $dateTo])
            ->where('result', 'fail')
            ->selectRaw('test_name, COUNT(*) as fail_count')
            ->groupBy('test_name')
            ->orderByDesc('fail_count')
            ->limit(10)
            ->get();

        $categories = QCTestResult::where('tenant_id', $tenantId)
            ->distinct()->pluck('test_category');

        return view('cosmetic.analytics.qc-trend', compact('qcStats', 'trendByDate', 'byCategory', 'failurePoints', 'categories', 'dateFrom', 'dateTo', 'category'));
    }

    /**
     * 3. Regulatory Dashboard
     */
    public function regulatoryDashboard(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Registration status overview
        $registrationStats = [
            'total' => ProductRegistration::where('tenant_id', $tenantId)->count(),
            'approved' => ProductRegistration::where('tenant_id', $tenantId)->where('status', 'approved')->count(),
            'pending' => ProductRegistration::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'expired' => ProductRegistration::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->where('expiry_date', '<', now())->count(),
            'expiring_soon' => ProductRegistration::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->whereBetween('expiry_date', [now(), now()->addDays(90)])->count(),
        ];

        // Compliance metrics
        $complianceMetrics = [
            'products_with_valid_registration' => ProductRegistration::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->where('expiry_date', '>=', now())->count(),
            'products_missing_registration' => CosmeticFormula::where('tenant_id', $tenantId)
                ->where('status', 'production')
                ->doesntHave('registrations')->count(),
            'restricted_ingredients_in_use' => $this->countRestrictedIngredients($tenantId),
            'sds_up_to_date' => $this->countSdsCompliant($tenantId),
        ];

        // Upcoming expirations
        $upcomingExpirations = ProductRegistration::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->whereBetween('expiry_date', [now(), now()->addDays(180)])
            ->with('formula')
            ->orderBy('expiry_date')
            ->get();

        // Recent submissions
        $recentSubmissions = ProductRegistration::where('tenant_id', $tenantId)
            ->with('formula')
            ->orderByDesc('submitted_at')
            ->limit(10)
            ->get();

        return view('cosmetic.analytics.regulatory', compact('registrationStats', 'complianceMetrics', 'upcomingExpirations', 'recentSubmissions'));
    }

    /**
     * 4. Formula Cost Analysis
     */
    public function formulaCostAnalysis(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $formulaId = $request->get('formula_id');

        $formulas = CosmeticFormula::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'production'])
            ->get();

        $costAnalysis = collect();

        if ($formulaId) {
            $formula = CosmeticFormula::where('tenant_id', $tenantId)->findOrFail($formulaId);

            // Calculate ingredient costs
            $ingredients = $formula->ingredients ?? [];
            $totalCost = 0;
            $ingredientCosts = collect($ingredients)->map(function ($ingredient) use (&$totalCost) {
                $cost = ($ingredient['quantity'] ?? 0) * ($ingredient['unit_cost'] ?? 0);
                $totalCost += $cost;
                return array_merge($ingredient, ['calculated_cost' => $cost]);
            });

            $costAnalysis = [
                'formula' => $formula,
                'total_ingredient_cost' => $totalCost,
                'cost_per_unit' => $formula->planned_yield > 0 ? $totalCost / $formula->planned_yield : 0,
                'ingredient_breakdown' => $ingredientCosts,
            ];
        }

        // Cost comparison across formulas
        $costComparison = CosmeticFormula::where('tenant_id', $tenantId)
            ->whereIn('status', ['approved', 'production'])
            ->with('ingredients')
            ->get()
            ->map(function ($formula) {
                $totalCost = collect($formula->ingredients ?? [])->sum(function ($ing) {
                    return ($ing['quantity'] ?? 0) * ($ing['unit_cost'] ?? 0);
                });
                return [
                    'formula' => $formula,
                    'total_cost' => $totalCost,
                    'cost_per_unit' => $formula->planned_yield > 0 ? $totalCost / $formula->planned_yield : 0,
                ];
            })->sortByDesc('total_cost');

        return view('cosmetic.analytics.cost-analysis', compact('formulas', 'costAnalysis', 'costComparison', 'formulaId'));
    }

    /**
     * 5. Supplier Quality Report
     */
    public function supplierQualityReport(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $supplierId = $request->get('supplier_id');
        $dateFrom = $request->get('date_from', now()->subDays(90)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        $suppliers = Supplier::where('tenant_id', $tenantId)->active()->get();

        // Supplier performance scores
        $supplierScores = Supplier::where('tenant_id', $tenantId)
            ->active()
            ->with(['scorecards', 'incidents'])
            ->get()
            ->map(function ($supplier) use ($dateFrom, $dateTo) {
                $incidents = $supplier->incidents()
                    ->whereBetween('incident_date', [$dateFrom, $dateTo])
                    ->count();

                $avgScore = $supplier->scorecards()->avg('overall_score') ?? 0;

                return [
                    'supplier' => $supplier,
                    'overall_score' => $avgScore,
                    'incident_count' => $incidents,
                    'quality_rating' => $this->calculateQualityRating($avgScore, $incidents),
                ];
            })->sortByDesc('overall_score');

        // Incident analysis
        $incidentAnalysis = SupplierIncident::where('tenant_id', $tenantId)
            ->whereBetween('incident_date', [$dateFrom, $dateTo])
            ->with('supplier')
            ->selectRaw('
                supplier_id,
                severity,
                COUNT(*) as incident_count
            ')
            ->groupBy('supplier_id', 'severity')
            ->get()
            ->groupBy('supplier_id');

        return view('cosmetic.analytics.supplier-quality', compact('suppliers', 'supplierScores', 'incidentAnalysis', 'supplierId', 'dateFrom', 'dateTo'));
    }

    /**
     * 6. Product Lifecycle Report
     */
    public function productLifecycle(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $status = $request->get('status');

        // Lifecycle distribution
        $lifecycleStats = CosmeticFormula::where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Products by lifecycle stage
        $products = CosmeticFormula::where('tenant_id', $tenantId)
            ->when($status, fn($q) => $q->where('status', $status))
            ->with(['registrations', 'batches'])
            ->withCount(['batches', 'registrations'])
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($formula) {
                $firstBatch = $formula->batches()->orderBy('production_date')->first();
                $lastBatch = $formula->batches()->orderByDesc('production_date')->first();

                return [
                    'formula' => $formula,
                    'days_in_current_status' => $formula->updated_at->diffInDays(now()),
                    'total_batches' => $formula->batches_count,
                    'first_production' => $firstBatch?->production_date,
                    'last_production' => $lastBatch?->production_date,
                    'registration_status' => $formula->registrations->first()?->status ?? 'Not Submitted',
                ];
            });

        // Lifecycle transitions over time
        $transitions = CosmeticFormula::where('tenant_id', $tenantId)
            ->whereNotNull('updated_at')
            ->selectRaw('
                DATE(updated_at) as date,
                status,
                COUNT(*) as count
            ')
            ->groupBy('date', 'status')
            ->orderByDesc('date')
            ->limit(30)
            ->get();

        $statuses = ['draft', 'testing', 'approved', 'production', 'discontinued'];

        return view('cosmetic.analytics.product-lifecycle', compact('lifecycleStats', 'products', 'transitions', 'status', 'statuses'));
    }

    /**
     * 7. Recall Report
     */
    public function recallReport(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $dateFrom = $request->get('date_from', now()->subDays(180)->format('Y-m-d'));
        $dateTo = $request->get('date_to', now()->format('Y-m-d'));

        // Recall statistics
        $recallStats = [
            'total_recalls' => BatchRecall::where('tenant_id', $tenantId)
                ->whereBetween('recall_date', [$dateFrom, $dateTo])->count(),
            'active_recalls' => BatchRecall::where('tenant_id', $tenantId)
                ->whereIn('status', ['initiated', 'in_progress'])->count(),
            'completed_recalls' => BatchRecall::where('tenant_id', $tenantId)
                ->where('status', 'completed')->count(),
            'avg_completion_days' => BatchRecall::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(DATEDIFF(completed_at, recall_date)) as avg_days')
                ->value('avg_days') ?? 0,
        ];

        // Recall effectiveness
        $recalls = BatchRecall::where('tenant_id', $tenantId)
            ->whereBetween('recall_date', [$dateFrom, $dateTo])
            ->with(['batch', 'batch.formula'])
            ->orderByDesc('recall_date')
            ->get()
            ->map(function ($recall) {
                $totalDistributed = $recall->batch?->actual_quantity ?? 0;
                $recovered = $recall->quantity_recovered ?? 0;
                $recoveryRate = $totalDistributed > 0 ? ($recovered / $totalDistributed) * 100 : 0;

                return [
                    'recall' => $recall,
                    'total_distributed' => $totalDistributed,
                    'recovered' => $recovered,
                    'recovery_rate' => $recoveryRate,
                    'days_open' => $recall->completed_at
                        ? $recall->recall_date->diffInDays($recall->completed_at)
                        : $recall->recall_date->diffInDays(now()),
                ];
            });

        // Recall reasons
        $recallReasons = BatchRecall::where('tenant_id', $tenantId)
            ->whereBetween('recall_date', [$dateFrom, $dateTo])
            ->selectRaw('reason, COUNT(*) as count')
            ->groupBy('reason')
            ->orderByDesc('count')
            ->get();

        return view('cosmetic.analytics.recall-report', compact('recallStats', 'recalls', 'recallReasons', 'dateFrom', 'dateTo'));
    }

    /**
     * 8. Expiry Forecast
     */
    public function expiryForecast(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $months = $request->get('months', 6);

        // Current expiry alerts
        $currentAlerts = ExpiryAlert::where('tenant_id', $tenantId)
            ->where('alert_date', '<=', now())
            ->where('action_taken', false)
            ->with(['batch', 'batch.formula'])
            ->orderBy('alert_date')
            ->get();

        // Forecast: batches expiring in next N months
        $forecast = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->where('status', 'released')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addMonths($months))
            ->with('formula')
            ->selectRaw('
                DATE_FORMAT(expiry_date, "%Y-%m") as expiry_month,
                COUNT(*) as batch_count,
                SUM(actual_quantity) as total_quantity
            ')
            ->groupBy('expiry_month')
            ->orderBy('expiry_month')
            ->get();

        // Monthly breakdown for chart
        $monthlyBreakdown = [];
        for ($i = 0; $i < $months; $i++) {
            $month = now()->addMonths($i)->format('Y-m');
            $monthData = $forecast->firstWhere('expiry_month', $month);
            $monthlyBreakdown[] = [
                'month' => $month,
                'month_label' => now()->addMonths($i)->format('M Y'),
                'batch_count' => $monthData->batch_count ?? 0,
                'total_quantity' => $monthData->total_quantity ?? 0,
            ];
        }

        // Products at risk
        $atRiskProducts = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->where('status', 'released')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>', now())
            ->where('expiry_date', '<=', now()->addMonths(3))
            ->with('formula')
            ->selectRaw('
                formula_id,
                COUNT(*) as batch_count,
                MIN(expiry_date) as earliest_expiry,
                SUM(actual_quantity) as total_stock
            ')
            ->groupBy('formula_id')
            ->orderBy('earliest_expiry')
            ->limit(10)
            ->get();

        return view('cosmetic.analytics.expiry-forecast', compact('currentAlerts', 'forecast', 'monthlyBreakdown', 'atRiskProducts', 'months'));
    }

    /**
     * Helper: Calculate QC Pass Rate
     */
    private function calculateQcPassRate(int $tenantId, Carbon $startDate): float
    {
        $total = QCTestResult::where('tenant_id', $tenantId)
            ->where('test_date', '>=', $startDate)->count();

        if ($total === 0)
            return 0;

        $passed = QCTestResult::where('tenant_id', $tenantId)
            ->where('test_date', '>=', $startDate)
            ->where('result', 'pass')->count();

        return ($passed / $total) * 100;
    }

    /**
     * Helper: Count Restricted Ingredients
     */
    private function countRestrictedIngredients(int $tenantId): int
    {
        // Simplified - would need ingredient restriction table
        return 0;
    }

    /**
     * Helper: Count SDS Compliant Products
     */
    private function countSdsCompliant(int $tenantId): int
    {
        // Simplified - would need SDS tracking
        return CosmeticFormula::where('tenant_id', $tenantId)
            ->where('status', 'production')->count();
    }

    /**
     * Helper: Calculate Quality Rating
     */
    private function calculateQualityRating(float $score, int $incidents): string
    {
        if ($score >= 90 && $incidents === 0)
            return 'Excellent';
        if ($score >= 75 && $incidents <= 2)
            return 'Good';
        if ($score >= 60 && $incidents <= 5)
            return 'Fair';
        return 'Poor';
    }
}
