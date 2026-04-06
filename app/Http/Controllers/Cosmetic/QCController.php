<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\QCTestResult;
use App\Models\QCTestTemplate;
use App\Models\CoaCertificate;
use App\Models\OosInvestigation;
use App\Models\CosmeticBatchRecord;
use Illuminate\Http\Request;

class QCController extends Controller
{
    /**
     * Display QC Tests Dashboard
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Stats
        $stats = [
            'total_tests' => QCTestResult::where('tenant_id', $tenantId)->count(),
            'pending_tests' => QCTestResult::where('tenant_id', $tenantId)->where('status', 'draft')->count(),
            'passed_tests' => QCTestResult::where('tenant_id', $tenantId)->where('result', 'pass')->count(),
            'failed_tests' => QCTestResult::where('tenant_id', $tenantId)->where('result', 'fail')->count(),
            'open_oos' => OosInvestigation::where('tenant_id', $tenantId)->open()->count(),
            'active_templates' => QCTestTemplate::where('tenant_id', $tenantId)->active()->count(),
        ];

        // Tests with filters
        $tests = QCTestResult::where('tenant_id', $tenantId)
            ->with(['batch', 'template', 'tester'])
            ->when($request->category, fn($q) => $q->where('test_category', $request->category))
            ->when($request->result, fn($q) => $q->where('result', $request->result))
            ->when($request->search, fn($q) => $q->where('test_code', 'like', "%{$request->search}%"))
            ->latest('test_date')
            ->paginate(20);

        $templates = QCTestTemplate::where('tenant_id', $tenantId)->active()->get();
        $batches = CosmeticBatchRecord::where('tenant_id', $tenantId)->get();

        return view('cosmetic.qc.index', compact('stats', 'tests', 'templates', 'batches'));
    }

    /**
     * Show test result details
     */
    public function showTest($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $test = QCTestResult::where('tenant_id', $tenantId)
            ->with(['batch', 'template', 'tester', 'approver', 'oosInvestigations'])
            ->findOrFail($id);

        return view('cosmetic.qc.test-show', compact('test'));
    }

    /**
     * Store new test result
     */
    public function storeTest(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'nullable|exists:cosmetic_batch_records,id',
            'template_id' => 'nullable|exists:qc_test_templates,id',
            'test_name' => 'required|string|max:255',
            'test_category' => 'required|string',
            'sample_id' => 'nullable|string',
            'test_date' => 'required|date',
            'parameters' => 'nullable|array',
        ]);

        $test = QCTestResult::create([
            'tenant_id' => auth()->user()->tenant_id,
            'batch_id' => $validated['batch_id'] ?? null,
            'template_id' => $validated['template_id'] ?? null,
            'test_code' => QCTestResult::getNextTestCode(),
            'test_name' => $validated['test_name'],
            'test_category' => $validated['test_category'],
            'sample_id' => $validated['sample_id'] ?? null,
            'parameters' => $validated['parameters'] ?? [],
            'test_date' => $validated['test_date'],
        ]);

        return redirect()->route('cosmetic.qc.tests')->with('success', 'QC test created successfully!');
    }

    /**
     * Complete test with results
     */
    public function completeTest(Request $request, $id)
    {
        $tenantId = auth()->user()->tenant_id;
        $test = QCTestResult::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'result' => 'required|in:pass,fail,inconclusive',
            'parameters' => 'required|array',
            'observations' => 'nullable|string',
        ]);

        $test->complete(
            $validated['result'],
            $validated['parameters'],
            $validated['observations'] ?? ''
        );

        // Auto-create OOS if failed
        if ($test->isFailed() && $request->create_oos) {
            $test->createOOS($validated['observations'] ?? 'Test failed', $request->oos_severity ?? 'medium');
        }

        return redirect()->back()->with('success', 'Test completed successfully!');
    }

    /**
     * Approve test
     */
    public function approveTest($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $test = QCTestResult::where('tenant_id', $tenantId)->findOrFail($id);

        $test->approve(auth()->id());

        return redirect()->back()->with('success', 'Test approved!');
    }

    /**
     * Display COA Certificates
     */
    public function coaIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_coas' => CoaCertificate::where('tenant_id', $tenantId)->count(),
            'approved_coas' => CoaCertificate::where('tenant_id', $tenantId)->approved()->count(),
            'valid_coas' => CoaCertificate::where('tenant_id', $tenantId)->valid()->count(),
            'expired_coas' => CoaCertificate::where('tenant_id', $tenantId)
                ->where('status', 'approved')
                ->where('expiry_date', '<', now())
                ->count(),
        ];

        $coas = CoaCertificate::where('tenant_id', $tenantId)
            ->with(['batch', 'issuer', 'approver'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest('issue_date')
            ->paginate(20);

        $batches = CosmeticBatchRecord::where('tenant_id', $tenantId)
            ->whereHas('qualityChecks', fn($q) => $q->where('result', 'pass'))
            ->get();

        return view('cosmetic.qc.coa', compact('stats', 'coas', 'batches'));
    }

    /**
     * Generate COA from batch
     */
    public function generateCoa(Request $request, $batchId)
    {
        $tenantId = auth()->user()->tenant_id;
        $batch = CosmeticBatchRecord::where('tenant_id', $tenantId)->findOrFail($batchId);

        $coa = CoaCertificate::generateFromBatch($batchId, auth()->id());

        return redirect()->route('cosmetic.qc.coa')->with('success', 'COA generated successfully!');
    }

    /**
     * Approve COA
     */
    public function approveCoa($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $coa = CoaCertificate::where('tenant_id', $tenantId)->findOrFail($id);

        $coa->approve(auth()->id());

        return redirect()->back()->with('success', 'COA approved!');
    }

    /**
     * Display OOS Investigations
     */
    public function oosIndex(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $stats = [
            'total_oos' => OosInvestigation::where('tenant_id', $tenantId)->count(),
            'open_oos' => OosInvestigation::where('tenant_id', $tenantId)->open()->count(),
            'critical_oos' => OosInvestigation::where('tenant_id', $tenantId)->critical()->count(),
            'high_oos' => OosInvestigation::where('tenant_id', $tenantId)->high()->count(),
        ];

        $oosList = OosInvestigation::where('tenant_id', $tenantId)
            ->with(['batch', 'testResult', 'assignee', 'investigator'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->severity, fn($q) => $q->where('severity', $request->severity))
            ->latest('discovery_date')
            ->paginate(20);

        return view('cosmetic.qc.oos', compact('stats', 'oosList'));
    }

    /**
     * Store OOS investigation
     */
    public function storeOos(Request $request)
    {
        $validated = $request->validate([
            'test_result_id' => 'nullable|exists:qc_test_results,id',
            'batch_id' => 'nullable|exists:cosmetic_batch_records,id',
            'oos_type' => 'required|string',
            'description' => 'required|string',
            'severity' => 'required|in:low,medium,high,critical',
        ]);

        $oos = OosInvestigation::create([
            'tenant_id' => auth()->user()->tenant_id,
            'test_result_id' => $validated['test_result_id'] ?? null,
            'batch_id' => $validated['batch_id'] ?? null,
            'oos_number' => OosInvestigation::getNextOosNumber(),
            'oos_type' => $validated['oos_type'],
            'description' => $validated['description'],
            'severity' => $validated['severity'],
            'discovery_date' => now(),
        ]);

        return redirect()->route('cosmetic.qc.oos')->with('success', 'OOS investigation created!');
    }

    /**
     * Complete OOS investigation
     */
    public function completeOos(Request $request, $id)
    {
        $tenantId = auth()->user()->tenant_id;
        $oos = OosInvestigation::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'root_cause' => 'required|string',
            'corrective_action' => 'required|string',
            'preventive_action' => 'nullable|string',
        ]);

        $oos->updateRootCause($validated['root_cause']);
        $oos->addCorrectiveAction($validated['corrective_action']);
        if ($validated['preventive_action']) {
            $oos->addPreventiveAction($validated['preventive_action']);
        }
        $oos->complete(auth()->id());

        return redirect()->back()->with('success', 'OOS investigation completed!');
    }

    /**
     * Display QC Templates
     */
    public function templatesIndex()
    {
        $tenantId = auth()->user()->tenant_id;
        $templates = QCTestTemplate::where('tenant_id', $tenantId)
            ->latest()
            ->paginate(20);

        return view('cosmetic.qc.templates', compact('templates'));
    }

    /**
     * Store QC template
     */
    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'test_category' => 'required|string',
            'test_parameters' => 'required|array',
            'acceptance_criteria' => 'required|array',
            'procedure' => 'nullable|string',
        ]);

        QCTestTemplate::create([
            'tenant_id' => auth()->user()->tenant_id,
            'template_name' => $validated['template_name'],
            'template_code' => QCTestTemplate::getNextTemplateCode(),
            'test_category' => $validated['test_category'],
            'test_parameters' => $validated['test_parameters'],
            'acceptance_criteria' => $validated['acceptance_criteria'],
            'procedure' => $validated['procedure'] ?? null,
        ]);

        return redirect()->back()->with('success', 'QC template created!');
    }
}
