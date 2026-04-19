<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Bom;
use App\Models\QualityCheck;
use App\Models\DefectRecord;
use App\Services\MrpService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ManufacturingApiController extends ApiBaseController
{
    protected $mrpService;

    public function __construct(MrpService $mrpService)
    {
        $this->mrpService = $mrpService;
    }

    /**
     * Get work orders
     */
    public function workOrders(Request $request)
    {
        $query = WorkOrder::where('tenant_id', $this->getTenantId())
            ->with(['product', 'bom']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $workOrders = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($workOrders);
    }

    /**
     * Create work order
     */
    public function createWorkOrder(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'bom_id' => 'required|exists:boms,id',
            'target_quantity' => 'required|numeric|min:1',
            'planned_start_date' => 'required|date',
            'planned_end_date' => 'required|date|after:planned_start_date',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $workOrder = WorkOrder::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'number' => 'WO-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => 'pending',
        ]));

        return $this->success($workOrder, 'Work order created successfully', 201);
    }

    /**
     * Get BOMs
     */
    public function boms(Request $request)
    {
        $query = Bom::where('tenant_id', $this->getTenantId())
            ->with(['product', 'lines.product']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $boms = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($boms);
    }

    /**
     * Run MRP calculation
     */
    public function runMrp(Request $request)
    {
        $validated = $request->validate([
            'bom_id' => 'required|exists:boms,id',
            'quantity' => 'required|numeric|min:1',
        ]);

        $bom = Bom::with('lines.product')->findOrFail($validated['bom_id']);
        $results = $this->mrpService->calculate($bom, $validated['quantity'], $this->getTenantId());

        return $this->success($results);
    }

    /**
     * Get quality checks
     */
    public function qualityChecks(Request $request)
    {
        $query = QualityCheck::where('tenant_id', $this->getTenantId())
            ->with(['workOrder', 'product', 'inspector']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        $checks = $query->latest('inspected_at')->paginate($request->get('per_page', 20));

        return $this->success($checks);
    }

    /**
     * Submit quality check results
     */
    public function submitQualityCheck(Request $request, $id)
    {
        $qualityCheck = QualityCheck::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'results' => 'required|array',
            'sample_passed' => 'required|numeric|min:0',
            'sample_failed' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $qualityCheck->update([
            'results' => $validated['results'],
            'sample_passed' => $validated['sample_passed'],
            'sample_failed' => $validated['sample_failed'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['sample_failed'] == 0 ? 'passed' : 'failed',
            'inspected_at' => now(),
        ]);

        return $this->success($qualityCheck, 'Quality check submitted successfully');
    }

    /**
     * Get defects
     */
    public function defects(Request $request)
    {
        $query = DefectRecord::where('tenant_id', $this->getTenantId())
            ->with(['product', 'workOrder', 'reportedBy']);

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->filled('status')) {
            if ($request->status === 'open') {
                $query->whereNull('resolved_at');
            } else {
                $query->whereNotNull('resolved_at');
            }
        }

        $defects = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($defects);
    }

    /**
     * Record defect
     */
    public function recordDefect(Request $request)
    {
        $validated = $request->validate([
            'quality_check_id' => 'required|exists:quality_checks,id',
            'product_id' => 'required|exists:products,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'defect_type' => 'required|in:cosmetic,functional,dimensional,material,other',
            'severity' => 'required|in:minor,major,critical',
            'quantity_defected' => 'required|integer|min:1',
            'description' => 'required|string',
            'disposition' => 'required|in:scrap,rework,return_to_vendor,use_as_is',
            'cost_impact' => 'nullable|numeric|min:0',
        ]);

        $defect = DefectRecord::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'reported_by' => auth()->id(),
        ]));

        return $this->success($defect, 'Defect recorded successfully', 201);
    }

    /**
     * Get work order detail
     */
    public function workOrderDetail($id)
    {
        $workOrder = WorkOrder::where('tenant_id', $this->getTenantId())
            ->with(['product', 'bom', 'qualityChecks', 'defects'])
            ->findOrFail($id);

        return $this->success($workOrder);
    }

    /**
     * Update work order status
     */
    public function updateWorkOrderStatus(Request $request, $id)
    {
        $workOrder = WorkOrder::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', Rule::in(WorkOrder::STATUSES)],
        ]);

        $workOrder->update($validated);

        return $this->success($workOrder, 'Work order status updated successfully');
    }

    /**
     * Get BOM detail
     */
    public function bomDetail($id)
    {
        $bom = Bom::where('tenant_id', $this->getTenantId())
            ->with(['product', 'lines.product'])
            ->findOrFail($id);

        return $this->success($bom);
    }

    /**
     * Create BOM
     */
    public function createBom(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string',
            'lines' => 'required|array',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity' => 'required|numeric|min:0',
            'lines.*.unit' => 'required|string',
        ]);

        $bom = Bom::create([
            'tenant_id' => $this->getTenantId(),
            'product_id' => $validated['product_id'],
            'name' => $validated['name'],
            'version' => '1.0',
        ]);

        foreach ($validated['lines'] as $line) {
            $bom->lines()->create($line);
        }

        return $this->success($bom->load('lines'), 'BOM created successfully', 201);
    }

    /**
     * Get quality check detail
     */
    public function qualityCheckDetail($id)
    {
        $qualityCheck = QualityCheck::where('tenant_id', $this->getTenantId())
            ->with(['workOrder', 'product', 'inspector'])
            ->findOrFail($id);

        return $this->success($qualityCheck);
    }

    /**
     * Create quality check
     */
    public function createQualityCheck(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'product_id' => 'required|exists:products,id',
            'stage' => 'required|in:incoming,in_process,final',
            'checklist' => 'required|array',
        ]);

        $qualityCheck = QualityCheck::create(array_merge($validated, [
            'tenant_id' => $this->getTenantId(),
            'status' => 'pending',
        ]));

        return $this->success($qualityCheck, 'Quality check created successfully', 201);
    }

    /**
     * Get defect detail
     */
    public function defectDetail($id)
    {
        $defect = DefectRecord::where('tenant_id', $this->getTenantId())
            ->with(['product', 'workOrder', 'reportedBy'])
            ->findOrFail($id);

        return $this->success($defect);
    }

    /**
     * Resolve defect
     */
    public function resolveDefect(Request $request, $id)
    {
        $defect = DefectRecord::where('tenant_id', $this->getTenantId())->findOrFail($id);

        $validated = $request->validate([
            'resolution' => 'required|string',
            'resolved_by' => 'nullable|exists:users,id',
        ]);

        $defect->update(array_merge($validated, [
            'resolved_at' => now(),
            'status' => 'resolved',
        ]));

        return $this->success($defect, 'Defect resolved successfully');
    }

    /**
     * Get production output
     */
    public function productionOutput(Request $request)
    {
        $query = WorkOrder::where('tenant_id', $this->getTenantId())
            ->where('status', 'completed')
            ->with(['product']);

        if ($request->filled('date_from')) {
            $query->whereDate('planned_end_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('planned_end_date', '<=', $request->date_to);
        }

        $output = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($output);
    }

    /**
     * Record production output
     */
    public function recordProductionOutput(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => 'required|exists:work_orders,id',
            'quantity' => 'required|numeric|min:0',
            'good_quantity' => 'required|numeric|min:0',
            'defect_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $workOrder = WorkOrder::where('tenant_id', $this->getTenantId())
            ->findOrFail($validated['work_order_id']);

        $workOrder->update([
            'actual_quantity' => $validated['quantity'],
            'good_quantity' => $validated['good_quantity'],
            'defect_quantity' => $validated['defect_quantity'],
        ]);

        return $this->success($workOrder, 'Production output recorded successfully');
    }

    /**
     * Get mix designs (for concrete/chemical manufacturing)
     */
    public function mixDesigns(Request $request)
    {
        // Check if MixDesign model exists
        if (!class_exists(\App\Models\MixDesign::class)) {
            return $this->error('Mix design feature not available', 501);
        }

        $query = \App\Models\MixDesign::where('tenant_id', $this->getTenantId())
            ->with(['product']);

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $mixDesigns = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success($mixDesigns);
    }

    /**
     * Get mix design detail
     */
    public function mixDesignDetail($id)
    {
        // Check if MixDesign model exists
        if (!class_exists(\App\Models\MixDesign::class)) {
            return $this->error('Mix design feature not available', 501);
        }

        $mixDesign = \App\Models\MixDesign::where('tenant_id', $this->getTenantId())
            ->with(['product', 'components'])
            ->findOrFail($id);

        return $this->success($mixDesign);
    }

    /**
     * Calculate mix design proportions
     */
    public function calculateMixDesign(Request $request)
    {
        // Check if MixDesign model exists
        if (!class_exists(\App\Models\MixDesign::class)) {
            return $this->error('Mix design feature not available', 501);
        }

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_strength' => 'required|numeric|min:0',
            'target_volume' => 'required|numeric|min:0',
            'components' => 'required|array',
            'components.*.material_id' => 'required|exists:products,id',
            'components.*.ratio' => 'required|numeric|min:0',
        ]);

        // Simple calculation: distribute target volume based on ratios
        $totalRatio = collect($validated['components'])->sum('ratio');
        $calculations = [];

        foreach ($validated['components'] as $component) {
            $calculations[] = [
                'material_id' => $component['material_id'],
                'ratio' => $component['ratio'],
                'quantity' => ($component['ratio'] / $totalRatio) * $validated['target_volume'],
            ];
        }

        return $this->success([
            'target_strength' => $validated['target_strength'],
            'target_volume' => $validated['target_volume'],
            'components' => $calculations,
            'total_ratio' => $totalRatio,
        ], 'Mix design calculated successfully');
    }
}
