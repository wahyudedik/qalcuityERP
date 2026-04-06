<?php

namespace App\Http\Controllers\Construction;

use App\Http\Controllers\Controller;
use App\Services\MaterialDeliveryService;
use App\Models\MaterialDelivery;
use App\Models\Project;
use Illuminate\Http\Request;

class MaterialDeliveryController extends Controller
{
    protected $deliveryService;

    public function __construct(MaterialDeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * Display material deliveries dashboard
     */
    public function index(Request $request)
    {
        $projects = Project::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['active', 'planning'])
            ->orderBy('name')
            ->get();

        $selectedProject = $request->input('project_id');
        $period = $request->input('period', 'month');

        $summary = null;
        $recentDeliveries = [];

        if ($selectedProject) {
            $summary = $this->deliveryService->getDeliverySummary($selectedProject, auth()->user()->tenant_id, $period);

            $recentDeliveries = MaterialDelivery::where('project_id', $selectedProject)
                ->where('tenant_id', auth()->user()->tenant_id)
                ->orderByDesc('created_at')
                ->paginate(20);
        }

        return view('construction.deliveries.index', compact(
            'projects',
            'selectedProject',
            'period',
            'summary',
            'recentDeliveries'
        ));
    }

    /**
     * Show create delivery form
     */
    public function create()
    {
        $projects = Project::where('tenant_id', auth()->user()->tenant_id)
            ->whereIn('status', ['active'])
            ->orderBy('name')
            ->get();

        return view('construction.deliveries.create', compact('projects'));
    }

    /**
     * Store new material delivery
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'supplier_name' => 'required|string|max:255',
            'material_name' => 'required|string|max:255',
            'material_category' => 'nullable|string|max:255',
            'quantity_ordered' => 'required|numeric|min:0',
            'unit' => 'required|string|max:50',
            'unit_price' => 'nullable|numeric|min:0',
            'expected_date' => 'required|date',
            'po_number' => 'nullable|string|max:255',
            'do_number' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:20',
            'remarks' => 'nullable|string',
        ]);

        $delivery = $this->deliveryService->createDelivery($validated, auth()->user()->tenant_id);

        return redirect()->route('construction.deliveries.show', $delivery)
            ->with('success', 'Material delivery created successfully.');
    }

    /**
     * Display delivery details
     */
    public function show(MaterialDelivery $delivery)
    {
        $this->authorize('view', $delivery);

        $delivery->load(['project', 'supplier', 'receivedBy']);

        return view('construction.deliveries.show', compact('delivery'));
    }

    /**
     * Mark delivery as in transit
     */
    public function markInTransit(MaterialDelivery $delivery)
    {
        $this->authorize('update', $delivery);

        $this->deliveryService->markInTransit($delivery->id, auth()->user()->tenant_id);

        return back()->with('success', 'Delivery marked as in transit.');
    }

    /**
     * Receive delivery
     */
    public function receive(Request $request, MaterialDelivery $delivery)
    {
        $validated = $request->validate([
            'quantity_delivered' => 'required|numeric|min:0',
            'quality_check_status' => 'nullable|in:passed,failed,pending',
            'quality_notes' => 'nullable|string',
            'photos.*' => 'nullable|image|max:5120',
            'remarks' => 'nullable|string',
        ]);

        $this->deliveryService->receiveDelivery($validated, $delivery->id, auth()->user()->tenant_id);

        return back()->with('success', 'Delivery received successfully.');
    }

    /**
     * Pass quality check
     */
    public function passQualityCheck(MaterialDelivery $delivery, Request $request)
    {
        $this->deliveryService->passQualityCheck(
            $delivery->id,
            auth()->user()->tenant_id,
            $request->input('notes')
        );

        return back()->with('success', 'Quality check passed.');
    }

    /**
     * Fail quality check
     */
    public function failQualityCheck(MaterialDelivery $delivery, Request $request)
    {
        $validated = $request->validate([
            'reason' => 'required|string',
        ]);

        $this->deliveryService->failQualityCheck(
            $delivery->id,
            auth()->user()->tenant_id,
            $validated['reason']
        );

        return back()->with('success', 'Quality check failed. Delivery cancelled.');
    }

    /**
     * Get delayed deliveries report
     */
    public function delayedReport()
    {
        $report = $this->deliveryService->getDelayedDeliveries(auth()->user()->tenant_id);

        return view('construction.deliveries.delayed-report', compact('report'));
    }

    /**
     * Get shortage report
     */
    public function shortageReport(int $projectId)
    {
        $report = $this->deliveryService->getShortageReport($projectId, auth()->user()->tenant_id);

        return response()->json($report);
    }
}
