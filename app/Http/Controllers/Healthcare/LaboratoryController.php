<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\LabOrder;
use App\Models\LabResult;
use App\Models\LabTestCatalog;
use Illuminate\Http\Request;

class LaboratoryController extends Controller
{
    /**
     * Display laboratory dashboard.
     */
    public function index()
    {
        $statistics = [
            'pending_orders' => LabOrder::where('status', 'pending')->count(),
            'samples_collected' => LabOrder::where('status', 'sample_collected')->count(),
            'in_analysis' => LabOrder::where('status', 'in_analysis')->count(),
            'completed_today' => LabOrder::where('status', 'completed')->whereDate('completed_at', today())->count(),
            'critical_results' => LabResult::where('is_critical', true)->whereNull('verified_at')->count(),
        ];

        $recentOrders = LabOrder::with(['patient', 'labTest', 'doctor'])
            ->latest()
            ->limit(10)
            ->get();

        return view('healthcare.laboratory.dashboard', compact('statistics', 'recentOrders'));
    }

    /**
     * Display lab test catalog.
     */
    public function tests()
    {
        $tests = LabTestCatalog::where('is_active', true)
            ->orderBy('category')
            ->orderBy('test_name')
            ->get();

        return view('healthcare.laboratory.tests', compact('tests'));
    }

    /**
     * Store new lab order.
     */
    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'lab_test_id' => 'required|exists:lab_test_catalogs,id',
            'doctor_id' => 'required|exists:doctors,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string',
            'medical_record_id' => 'nullable|exists:patient_medical_records,id',
        ]);

        $labOrder = LabOrder::create($validated);

        return redirect()->route('healthcare.laboratory.orders.show', $labOrder)
            ->with('success', 'Lab order created successfully');
    }

    /**
     * Display lab orders.
     */
    public function orders(Request $request)
    {
        $query = LabOrder::with(['patient', 'labTest', 'doctor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('medical_record_number', 'like', "%{$search}%");
            })->orWhere('order_number', 'like', "%{$search}%");
        }

        $orders = $query->latest()->paginate(20)->withQueryString();

        $statistics = [
            'total_orders' => LabOrder::count(),
            'pending_orders' => LabOrder::where('status', 'pending')->count(),
            'in_progress_orders' => LabOrder::where('status', 'in_progress')->count(),
            'completed_today' => LabOrder::where('status', 'completed')->whereDate('completed_at', today())->count(),
        ];

        return view('healthcare.laboratory.orders', compact('orders', 'statistics'));
    }

    /**
     * Display lab order details.
     */
    public function showOrder(LabOrder $order)
    {
        $order->load(['patient', 'labTest', 'doctor', 'samples', 'results']);

        return view('healthcare.laboratory.order-show', compact('order'));
    }

    /**
     * Collect sample for lab order.
     */
    public function collectSample(LabOrder $order, Request $request)
    {
        $validated = $request->validate([
            'sample_type' => 'required|in:blood,urine,stool,sputum,swab,tissue,csf,other',
            'collection_method' => 'nullable|string|max:255',
            'collected_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $sample = $order->samples()->create([
            'patient_id' => $order->patient_id,
            'sample_type' => $validated['sample_type'],
            'collection_method' => $validated['collection_method'],
            'collected_by' => $validated['collected_by'],
            'collected_at' => now(),
            'notes' => $validated['notes'],
        ]);

        // Update order status
        $order->update(['status' => 'sample_collected']);

        return back()->with('success', 'Sample collected successfully');
    }

    /**
     * Enter lab results.
     */
    public function enterResults(LabOrder $order, Request $request)
    {
        $validated = $request->validate([
            'results' => 'required|array',
            'results.*.parameter_name' => 'required|string',
            'results.*.result_value' => 'required|string',
            'results.*.unit' => 'nullable|string',
            'results.*.reference_range' => 'nullable|string',
            'results.*.is_abnormal' => 'boolean',
            'results.*.is_critical' => 'boolean',
            'performed_by' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $result = $order->results()->create([
            'patient_id' => $order->patient_id,
            'performed_by' => $validated['performed_by'],
            'result_data' => $validated['results'],
            'notes' => $validated['notes'],
            'status' => 'preliminary',
        ]);

        // Update order status
        $order->update(['status' => 'completed']);

        // Check for critical values
        $hasCritical = collect($validated['results'])->firstWhere('is_critical', true);
        if ($hasCritical) {
            $result->flagCritical('Critical lab result detected');
        }

        return back()->with('success', 'Lab results entered successfully');
    }

    /**
     * Validate lab results.
     */
    public function validateResults(LabOrder $order, Request $request)
    {
        $validated = $request->validate([
            'validated_by' => 'required|exists:doctors,id',
            'validation_notes' => 'nullable|string',
        ]);

        $result = $order->results()->latest()->first();

        if (! $result) {
            return back()->with('error', 'No results found to validate');
        }

        $result->update([
            'status' => 'final',
            'verified_by' => $validated['validated_by'],
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Lab results validated successfully');
    }

    /**
     * Display lab results.
     */
    public function results(Request $request)
    {
        $query = LabResult::with(['order', 'patient']);

        if ($request->filled('verified')) {
            if ($request->verified === 'true') {
                $query->whereNotNull('verified_at');
            } else {
                $query->whereNull('verified_at');
            }
        }

        if ($request->filled('critical')) {
            $query->where('is_critical', true);
        }

        $results = $query->latest()->paginate(20);

        return view('healthcare.laboratory.results', compact('results'));
    }

    /**
     * Display lab result details.
     */
    public function showResult(LabResult $result)
    {
        $result->load(['order', 'patient']);

        return view('healthcare.laboratory.result-show', compact('result'));
    }

    /**
     * Display laboratory dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'pending_orders' => LabOrder::where('status', 'pending')->count(),
            'samples_collected' => LabOrder::where('status', 'sample_collected')->count(),
            'in_analysis' => LabOrder::where('status', 'in_analysis')->count(),
            'completed_today' => LabOrder::where('status', 'completed')->whereDate('completed_at', today())->count(),
            'avg_turnaround_time' => LabOrder::whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, completed_at)) as avg')
                ->value('avg'),
        ];

        return view('healthcare.laboratory.dashboard', compact('statistics'));
    }

    /**
     * Display lab equipment.
     */
    public function equipment()
    {
        $equipment = \App\Models\LabEquipment::where('is_active', true)->get();

        return view('healthcare.laboratory.equipment', compact('equipment'));
    }
}
