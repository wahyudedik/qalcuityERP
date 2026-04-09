<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\LabResult;
use App\Models\LabOrder;
use App\Models\LabTestCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LabResultController extends Controller
{
    /**
     * Display a listing of lab results.
     */
    public function index(Request $request)
    {
        $query = LabResult::query()->with(['labOrder.patient', 'labOrder.labTest', 'verifiedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('is_critical')) {
            $query->where('is_critical', $request->boolean('is_critical'));
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->boolean('is_verified'));
        }

        $results = $query->orderBy('result_date', 'desc')->paginate(20);

        $statistics = [
            'total_results' => LabResult::count(),
            'pending_verification' => LabResult::where('is_verified', false)->count(),
            'critical_results' => LabResult::where('is_critical', true)->count(),
            'verified_today' => LabResult::where('is_verified', true)->whereDate('verified_at', today())->count(),
        ];

        return view('healthcare.lab-results.index', compact('results', 'statistics'));
    }

    /**
     * Display the specified lab result.
     */
    public function show(LabResult $result)
    {
        $result->load(['labOrder.patient', 'labOrder.labTest', 'labOrder.doctor', 'verifiedBy']);

        return view('healthcare.lab-results.show', compact('result'));
    }

    /**
     * Show the form for entering lab results.
     */
    public function create(LabOrder $order)
    {
        $order->load(['labTest.parameters', 'patient']);

        return view('healthcare.lab-results.create', compact('order'));
    }

    /**
     * Store lab results.
     */
    public function store(Request $request, LabOrder $order)
    {
        $validated = $request->validate([
            'result_data' => 'required|array',
            'result_data.*.parameter_id' => 'required|exists:lab_test_parameters,id',
            'result_data.*.value' => 'required|numeric',
            'result_notes' => 'nullable|string',
            'is_critical' => 'boolean',
        ]);

        $result = LabResult::create([
            'lab_order_id' => $order->id,
            'result_data' => $validated['result_data'],
            'result_notes' => $validated['result_notes'] ?? null,
            'is_critical' => $request->has('is_critical'),
            'result_date' => now(),
            'status' => 'completed',
        ]);

        if ($result->is_critical) {
            // Notify doctor immediately for critical results
            $this->notifyCriticalResult($result);
        }

        $order->update(['status' => 'completed']);

        return redirect()->route('healthcare.lab-results.show', $result)
            ->with('success', 'Lab result saved successfully');
    }

    /**
     * Verify lab result.
     */
    public function verify(LabResult $result)
    {
        $result->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lab result verified successfully',
        ]);
    }

    /**
     * Update lab result.
     */
    public function update(Request $request, LabResult $result)
    {
        $validated = $request->validate([
            'result_data' => 'required|array',
            'result_data.*.parameter_id' => 'required|exists:lab_test_parameters,id',
            'result_data.*.value' => 'required|numeric',
            'result_notes' => 'nullable|string',
            'is_critical' => 'boolean',
        ]);

        $result->update([
            'result_data' => $validated['result_data'],
            'result_notes' => $validated['result_notes'] ?? null,
            'is_critical' => $request->has('is_critical'),
            'is_verified' => false,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return redirect()->route('healthcare.lab-results.show', $result)
            ->with('success', 'Lab result updated and pending re-verification');
    }

    /**
     * Print lab result.
     */
    public function print(LabResult $result)
    {
        $result->load(['labOrder.patient', 'labOrder.labTest', 'labOrder.doctor', 'verifiedBy']);

        return view('healthcare.lab-results.print', compact('result'));
    }

    /**
     * Get critical results.
     */
    public function criticalResults()
    {
        $results = LabResult::where('is_critical', true)
            ->where('is_verified', false)
            ->with(['labOrder.patient', 'labOrder.doctor'])
            ->latest()
            ->get();

        return view('healthcare.lab-results.critical', compact('results'));
    }

    /**
     * Notify doctor of critical result.
     */
    private function notifyCriticalResult(LabResult $result)
    {
        // Implementation for critical result notification
        // Could send SMS, email, or push notification
    }
}
