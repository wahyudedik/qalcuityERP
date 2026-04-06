<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\CosmeticBatchRecord;
use App\Models\CosmeticFormula;
use App\Models\BatchQualityCheck;
use App\Models\BatchReworkLog;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    /**
     * Display all batch records
     */
    public function index(Request $request)
    {
        $stats = [
            'total_batches' => CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)->count(),
            'in_progress' => CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'in_progress')->count(),
            'qc_pending' => CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'qc_pending')->count(),
            'released' => CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->where('status', 'released')->count(),
        ];

        $query = CosmeticBatchRecord::with(['formula', 'producer', 'qcInspector'])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->orderByDesc('production_date');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by formula
        if ($request->filled('formula_id')) {
            $query->where('formula_id', $request->formula_id);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('batch_number', 'like', "%{$search}%")
                    ->orWhereHas('formula', function ($q) use ($search) {
                        $q->where('formula_name', 'like', "%{$search}%");
                    });
            });
        }

        $batches = $query->paginate(20);

        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->where('status', 'approved')
            ->orderBy('formula_name')
            ->get();

        return view('cosmetic.batches.index', compact('stats', 'batches', 'formulas'));
    }

    /**
     * Show create batch form
     */
    public function create(Request $request)
    {
        $formulas = CosmeticFormula::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['approved', 'production'])
            ->orderBy('formula_name')
            ->get();

        $selectedFormula = null;
        if ($request->filled('formula_id')) {
            $selectedFormula = CosmeticFormula::find($request->formula_id);
        }

        return view('cosmetic.batches.create', compact('formulas', 'selectedFormula'));
    }

    /**
     * Store new batch record
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'required|exists:cosmetic_formulas,id',
            'batch_number' => 'nullable|unique:cosmetic_batch_records,batch_number',
            'production_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:production_date',
            'planned_quantity' => 'required|numeric|min:0',
            'production_notes' => 'nullable|string',
        ]);

        try {
            $batch = new CosmeticBatchRecord();
            $batch->tenant_id = auth()->user()->tenant_id;
            $batch->batch_number = $validated['batch_number'] ?? CosmeticBatchRecord::getNextBatchNumber();
            $batch->formula_id = $validated['formula_id'];
            $batch->production_date = $validated['production_date'];
            $batch->expiry_date = $validated['expiry_date'] ?? null;
            $batch->planned_quantity = $validated['planned_quantity'];
            $batch->production_notes = $validated['production_notes'] ?? null;
            $batch->created_by = auth()->id();
            $batch->status = 'draft';
            $batch->save();

            return redirect()->route('cosmetic.batches.show', $batch)
                ->with('success', 'Batch record created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create batch: ' . $e->getMessage());
        }
    }

    /**
     * Display batch details
     */
    public function show($id)
    {
        $batch = CosmeticBatchRecord::with([
            'formula.ingredients',
            'qualityChecks.inspector',
            'reworkLogs.initiator',
            'producer',
            'qcInspector'
        ])
            ->where('tenant_id', auth()->user()->tenant_id)
            ->findOrFail($id);

        $qualityChecks = $batch->qualityChecks;
        $reworkLogs = $batch->reworkLogs;

        // Calculate yield if actual quantity exists
        if ($batch->actual_quantity) {
            $batch->calculateYield();
        }

        return view('cosmetic.batches.show', compact(
            'batch',
            'qualityChecks',
            'reworkLogs'
        ));
    }

    /**
     * Update batch status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,in_progress,qc_pending,released,rejected,on_hold',
            'actual_quantity' => 'nullable|numeric|min:0',
            'production_notes' => 'nullable|string',
            'qc_notes' => 'nullable|string',
        ]);

        try {
            $batch = CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $batch->status = $validated['status'];

            if (isset($validated['actual_quantity'])) {
                $batch->actual_quantity = $validated['actual_quantity'];
                $batch->calculateYield();
            }

            if (isset($validated['production_notes'])) {
                $batch->production_notes = $validated['production_notes'];
            }

            if (isset($validated['qc_notes'])) {
                $batch->qc_notes = $validated['qc_notes'];
            }

            if ($validated['status'] === 'in_progress' && !$batch->produced_by) {
                $batch->produced_by = auth()->id();
            }

            $batch->save();

            return back()->with('success', 'Batch status updated to ' . $batch->status_label);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add quality check
     */
    public function addQualityCheck(Request $request, $id)
    {
        $validated = $request->validate([
            'check_point' => 'required|in:mixing,filling,packaging,final',
            'parameter' => 'required|string',
            'target_value' => 'nullable|numeric',
            'actual_value' => 'nullable|numeric',
            'lower_limit' => 'nullable|numeric',
            'upper_limit' => 'nullable|numeric',
            'observations' => 'nullable|string',
        ]);

        try {
            $batch = CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $check = new BatchQualityCheck();
            $check->tenant_id = auth()->user()->tenant_id;
            $check->batch_id = $batch->id;
            $check->check_point = $validated['check_point'];
            $check->parameter = $validated['parameter'];
            $check->target_value = $validated['target_value'] ?? null;
            $check->actual_value = $validated['actual_value'] ?? null;
            $check->lower_limit = $validated['lower_limit'] ?? null;
            $check->upper_limit = $validated['upper_limit'] ?? null;
            $check->observations = $validated['observations'] ?? null;

            // Auto-determine result based on limits
            if ($check->actual_value && $check->lower_limit && $check->upper_limit) {
                $check->result = $check->isWithinLimits() ? 'pass' : 'fail';
                $check->checked_by = auth()->id();
                $check->checked_at = now();
            }

            $check->save();

            return back()->with('success', 'Quality check added successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update quality check result
     */
    public function updateQualityCheck(Request $request, $checkId)
    {
        $validated = $request->validate([
            'result' => 'required|in:pending,pass,fail',
            'observations' => 'nullable|string',
        ]);

        try {
            $check = BatchQualityCheck::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($checkId);

            if ($validated['result'] === 'pass') {
                $check->pass(auth()->id(), $validated['observations'] ?? '');
            } elseif ($validated['result'] === 'fail') {
                $check->fail(auth()->id(), $validated['observations'] ?? '');
            }

            return back()->with('success', 'Quality check updated!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add rework log
     */
    public function addReworkLog(Request $request, $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
            'rework_action' => 'required|string',
            'quantity_before' => 'required|numeric|min:0',
        ]);

        try {
            $batch = CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $rework = new BatchReworkLog();
            $rework->tenant_id = auth()->user()->tenant_id;
            $rework->batch_id = $batch->id;
            $rework->rework_code = BatchReworkLog::getNextReworkCode();
            $rework->reason = $validated['reason'];
            $rework->rework_action = $validated['rework_action'];
            $rework->quantity_before = $validated['quantity_before'];
            $rework->initiated_by = auth()->id();
            $rework->status = 'in_progress';
            $rework->save();

            return back()->with('success', 'Rework log created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Complete rework
     */
    public function completeRework(Request $request, $reworkId)
    {
        $validated = $request->validate([
            'quantity_after' => 'nullable|numeric|min:0',
            'final_notes' => 'nullable|string',
        ]);

        try {
            $rework = BatchReworkLog::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($reworkId);

            $rework->quantity_after = $validated['quantity_after'] ?? null;
            $rework->calculateLoss();
            $rework->complete(auth()->id(), $validated['final_notes'] ?? '');

            return back()->with('success', 'Rework completed! Loss: ' . $rework->loss_quantity . ' units');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Release batch
     */
    public function releaseBatch($id)
    {
        try {
            $batch = CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            if (!$batch->canBeReleased()) {
                return back()->with('error', 'Batch cannot be released. Please ensure all QC checks passed and no open rework.');
            }

            $batch->release(auth()->id());

            return back()->with('success', 'Batch released successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete batch
     */
    public function destroy($id)
    {
        try {
            $batch = CosmeticBatchRecord::where('tenant_id', auth()->user()->tenant_id)
                ->findOrFail($id);

            $batch->delete();

            return redirect()->route('cosmetic.batches.index')
                ->with('success', 'Batch deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
