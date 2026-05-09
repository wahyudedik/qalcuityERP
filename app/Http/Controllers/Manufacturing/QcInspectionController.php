<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\QcInspection;
use App\Models\QcTestTemplate;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * QC Inspection Controller
 *
 * TASK-2.21: Build QC result recording UI
 */
class QcInspectionController extends Controller
{
    /**
     * Display a listing of QC inspections
     */
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = QcInspection::with(['workOrder', 'template', 'inspector'])
            ->where('tenant_id', $tenantId)
            ->latest('created_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $inspections = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => QcInspection::where('tenant_id', $tenantId)->count(),
            'passed' => QcInspection::where('tenant_id', $tenantId)->where('status', 'passed')->count(),
            'failed' => QcInspection::where('tenant_id', $tenantId)->where('status', 'failed')->count(),
            'pending' => QcInspection::where('tenant_id', $tenantId)->where('status', 'pending')->count(),
            'pass_rate' => 0,
        ];

        if ($stats['total'] > 0) {
            $stats['pass_rate'] = round(($stats['passed'] / $stats['total']) * 100, 2);
        }

        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('number')
            ->get();

        return view('qc.inspections.index', compact('inspections', 'stats', 'workOrders'));
    }

    /**
     * Show the form for creating a new QC inspection
     */
    public function create(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $workOrders = WorkOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('number')
            ->get();

        $templates = QcTestTemplate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        // Pre-select work order if provided
        $selectedWorkOrder = null;
        if ($request->filled('work_order_id')) {
            $selectedWorkOrder = WorkOrder::where('id', $request->work_order_id)
                ->where('tenant_id', $tenantId)
                ->first();
        }

        return view('qc.inspections.create', compact('workOrders', 'templates', 'selectedWorkOrder'));
    }

    /**
     * Store a newly created QC inspection
     */
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $data = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'template_id' => 'nullable|exists:qc_test_templates,id',
            'stage' => 'required|in:incoming,in-process,final,random',
            'sample_size' => 'required|integer|min:1',
        ]);

        // Verify work order belongs to tenant
        $workOrder = WorkOrder::where('id', $data['work_order_id'])
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $inspection = QcInspection::create([
            'tenant_id' => $tenantId,
            'work_order_id' => $data['work_order_id'],
            'template_id' => $data['template_id'] ?? null,
            'stage' => $data['stage'],
            'sample_size' => $data['sample_size'],
            'status' => 'pending',
        ]);

        return redirect()->route('qc.inspections.edit', $inspection)
            ->with('success', 'QC inspection created successfully');
    }

    /**
     * Show the form for recording test results
     */
    public function edit(QcInspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::user()->tenant_id, 403);

        $inspection->load(['workOrder', 'template']);

        return view('qc.inspections.edit', compact('inspection'));
    }

    /**
     * Update QC inspection with test results
     */
    public function update(Request $request, QcInspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::user()->tenant_id, 403);

        $data = $request->validate([
            'test_results' => 'required|array',
            'test_results.*.parameter' => 'required|string',
            'test_results.*.value' => 'required|numeric',
            'test_results.*.notes' => 'nullable|string',
            'inspector_notes' => 'nullable|string',
        ]);

        // Validate results if template exists
        if ($inspection->template) {
            $validation = $inspection->template->validateResults($data['test_results']);
            $inspection->recordTestResults($validation['results']);
        } else {
            // Manual recording without template
            $results = collect($data['test_results'])->map(function ($result) {
                return [
                    ...$result,
                    'passed' => true, // Manual mode assumes passed unless specified
                ];
            })->toArray();

            $inspection->recordTestResults($results);
        }

        $inspection->update([
            'inspector_notes' => $data['inspector_notes'] ?? $inspection->inspector_notes,
            'status' => 'in_progress',
        ]);

        return redirect()->route('qc.inspections.edit', $inspection)
            ->with('success', 'Test results recorded successfully');
    }

    /**
     * Pass the inspection
     */
    public function pass(Request $request, QcInspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::user()->tenant_id, 403);

        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $inspection->pass($data['notes'] ?? null);

        return redirect()->route('qc.inspections.show', $inspection)
            ->with('success', 'Inspection passed successfully');
    }

    /**
     * Fail the inspection
     */
    public function fail(Request $request, QcInspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::user()->tenant_id, 403);

        $data = $request->validate([
            'corrective_action' => 'required|string',
            'defects' => 'nullable|string',
        ]);

        $inspection->fail(
            $data['corrective_action'],
            $data['defects'] ?? null
        );

        return redirect()->route('qc.inspections.show', $inspection)
            ->with('success', 'Inspection failed - corrective action required');
    }

    /**
     * Conditional pass
     */
    public function conditionalPass(Request $request, QcInspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::user()->tenant_id, 403);

        $data = $request->validate([
            'notes' => 'required|string',
        ]);

        $inspection->conditionalPass($data['notes']);

        return redirect()->route('qc.inspections.show', $inspection)
            ->with('success', 'Inspection conditionally passed');
    }

    /**
     * Display the specified QC inspection
     */
    public function show(QcInspection $inspection)
    {
        abort_if($inspection->tenant_id !== Auth::user()->tenant_id, 403);

        $inspection->load(['workOrder', 'template', 'inspector']);

        return view('qc.inspections.show', compact('inspection'));
    }

    /**
     * Get inspection analytics
     */
    public function analytics()
    {
        $tenantId = Auth::user()->tenant_id;

        $analytics = [
            'by_status' => QcInspection::where('tenant_id', $tenantId)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status'),

            'by_stage' => QcInspection::where('tenant_id', $tenantId)
                ->selectRaw('stage, COUNT(*) as count')
                ->groupBy('stage')
                ->pluck('count', 'stage'),

            'by_grade' => QcInspection::where('tenant_id', $tenantId)
                ->whereNotNull('grade')
                ->selectRaw('grade, COUNT(*) as count')
                ->groupBy('grade')
                ->pluck('count', 'grade'),

            'avg_pass_rate' => QcInspection::where('tenant_id', $tenantId)
                ->whereNotNull('pass_rate')
                ->avg('pass_rate'),

            'recent_trend' => QcInspection::where('tenant_id', $tenantId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(created_at) as date, AVG(pass_rate) as avg_pass_rate, COUNT(*) as count')
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($analytics);
    }
}
