<?php

namespace App\Http\Controllers\Suppliers;

use App\Http\Controllers\Controller;
use App\Services\SupplierScorecardService;
use App\Services\StrategicSourcingService;
use App\Models\SourcingOpportunity;
use Illuminate\Http\Request;

class SupplierScorecardController extends Controller
{
    protected $scorecardService;
    protected $sourcingService;

    public function __construct(
        SupplierScorecardService $scorecardService,
        StrategicSourcingService $sourcingService
    ) {
        $this->scorecardService = $scorecardService;
        $this->sourcingService = $sourcingService;
    }

    /**
     * Display supplier scorecard dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $dashboard = $this->scorecardService->getDashboardData(auth()->user()->tenant_id, $period);

        return view('suppliers.scorecard-dashboard', compact('dashboard'));
    }

    /**
     * Show detailed supplier performance report
     */
    public function detail($supplierId)
    {
        // Ensure supplierId is an integer
        if (!is_numeric($supplierId)) {
            abort(404, 'Supplier tidak ditemukan');
        }

        $report = $this->scorecardService->getSupplierPerformanceReport((int) $supplierId, 12);

        return view('suppliers.scorecard-detail', compact('report'));
    }

    /**
     * Generate scorecards for all suppliers
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'period' => 'required|in:monthly,quarterly,yearly'
        ]);

        try {
            $generated = $this->scorecardService->generateBulkScorecards(
                auth()->user()->tenant_id,
                $validated['period']
            );

            return redirect()->back()
                ->with('success', "Berhasil membuat {$generated} scorecard!");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat scorecard: ' . $e->getMessage());
        }
    }

    /**
     * Display strategic sourcing dashboard
     */
    public function sourcingDashboard()
    {
        $dashboard = $this->sourcingService->getSourcingDashboard(auth()->user()->tenant_id);
        $opportunities = SourcingOpportunity::where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('suppliers.sourcing-dashboard', compact('dashboard', 'opportunities'));
    }

    /**
     * Analyze RFQ responses
     */
    public function analyzeRfq($rfqId)
    {
        $analysis = $this->sourcingService->analyzeRfqResponses($rfqId);

        if (isset($analysis['error'])) {
            return redirect()->back()->with('error', $analysis['error']);
        }

        return view('suppliers.rfq-analysis', compact('analysis'));
    }

    /**
     * Create new sourcing opportunity
     */
    public function createOpportunity(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string',
            'estimated_annual_spend' => 'required|numeric|min:0',
            'priority' => 'required|in:low,medium,high,critical',
            'target_completion_date' => 'nullable|date',
        ]);

        try {
            $validated['tenant_id'] = auth()->user()->tenant_id;
            $validated['status'] = 'identified';

            // Calculate potential savings (estimate 10% for new opportunities)
            $validated['potential_savings'] = $validated['estimated_annual_spend'] * 0.10;
            $validated['savings_percentage'] = 10;

            $opportunity = $this->sourcingService->createOpportunity($validated);

            return redirect()->route('suppliers.sourcing')
                ->with('success', 'Opportunity berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal membuat opportunity: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update opportunity status
     */
    public function updateOpportunityStatus(Request $request, $opportunityId)
    {
        $validated = $request->validate([
            'status' => 'required|in:identified,analyzing,rfq_sent,negotiated,implemented',
            'notes' => 'nullable|string',
        ]);

        try {
            $opportunity = $this->sourcingService->updateOpportunityStatus(
                $opportunityId,
                $validated['status'],
                $validated['notes'] ? [$validated['notes']] : []
            );

            return redirect()->back()
                ->with('success', 'Status opportunity berhasil diupdate!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal update status: ' . $e->getMessage());
        }
    }

    /**
     * Compare multiple suppliers
     */
    public function compareSuppliers(Request $request)
    {
        $validated = $request->validate([
            'supplier_ids' => 'required|array|min:2',
            'supplier_ids.*' => 'exists:suppliers,id',
            'months' => 'nullable|integer|min:1|max:24',
        ]);

        $months = $validated['months'] ?? 6;
        $comparison = $this->sourcingService->compareSuppliers(
            $validated['supplier_ids'],
            $months
        );

        return view('suppliers.supplier-comparison', compact('comparison', 'months'));
    }

    /**
     * Export scorecards to Excel/CSV
     */
    public function export(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $dashboard = $this->scorecardService->getDashboardData(auth()->user()->tenant_id, $period);

        // For now, return as CSV - can be enhanced with Laravel Excel package
        $csv = "Supplier,Overall Score,Rating,Quality,Delivery,Cost,Service\n";

        foreach ($dashboard['scorecards'] as $scorecard) {
            $csv .= sprintf(
                "%s,%.1f,%s,%.1f,%.1f,%.1f,%.1f\n",
                $scorecard->supplier->name,
                $scorecard->overall_score,
                $scorecard->rating,
                $scorecard->quality_score,
                $scorecard->delivery_score,
                $scorecard->cost_score,
                $scorecard->service_score
            );
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="supplier_scorecards_' . date('Y-m-d') . '.csv"');
    }
}
