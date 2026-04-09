<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomLine;
use App\Models\Product;
use App\Models\WorkCenter;
use App\Models\WorkOrder;
use App\Models\QualityCheck;
use App\Models\QualityCheckStandard;
use App\Models\DefectRecord;
use App\Services\GlPostingService;
use App\Services\MrpService;
use App\Services\Manufacturing\QualityControlService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturingController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── BOM CRUD ──────────────────────────────────────────────────

    public function bom(Request $request)
    {
        $query = Bom::with(['product', 'lines.product', 'lines.childBom'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%$s%")));
        }

        $boms = $query->latest()->paginate(20)->withQueryString();
        $products = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $allBoms = Bom::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('manufacturing.bom', compact('boms', 'products', 'allBoms'));
    }

    public function storeBom(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'batch_size' => 'required|numeric|min:0.001',
            'batch_unit' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_per_batch' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.child_bom_id' => 'nullable|exists:boms,id',
        ]);

        // BUG-MFG-001 FIX: Validate no circular references before creating
        // Note: BOM belum ada, jadi kita validate struktur lines saja
        $this->validateBomLinesForCircularReference(null, $data['lines']);

        DB::transaction(function () use ($data) {
            $bom = Bom::create([
                'tenant_id' => $this->tid(),
                'product_id' => $data['product_id'],
                'name' => $data['name'],
                'batch_size' => $data['batch_size'],
                'batch_unit' => $data['batch_unit'],
                'notes' => $data['notes'] ?? null,
                'is_active' => true,
            ]);

            foreach ($data['lines'] as $i => $line) {
                BomLine::create([
                    'bom_id' => $bom->id,
                    'product_id' => $line['product_id'],
                    'quantity_per_batch' => $line['quantity_per_batch'],
                    'unit' => $line['unit'],
                    'child_bom_id' => $line['child_bom_id'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        });

        return back()->with('success', 'BOM berhasil dibuat.');
    }

    /**
     * BUG-MFG-001 FIX: Added circular reference validation before save
     */
    public function updateBom(Request $request, Bom $bom)
    {
        abort_if($bom->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'batch_size' => 'required|numeric|min:0.001',
            'batch_unit' => 'required|string|max:20',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.quantity_per_batch' => 'required|numeric|min:0.001',
            'lines.*.unit' => 'required|string|max:20',
            'lines.*.child_bom_id' => 'nullable|exists:boms,id',
        ]);

        // BUG-MFG-001 FIX: Validate no circular references before saving
        $this->validateBomCircularReference($bom->id, $data['lines']);

        DB::transaction(function () use ($bom, $data) {
            $bom->update([
                'name' => $data['name'],
                'batch_size' => $data['batch_size'],
                'batch_unit' => $data['batch_unit'],
                'is_active' => $data['is_active'] ?? true,
                'notes' => $data['notes'] ?? null,
            ]);

            $bom->lines()->delete();
            foreach ($data['lines'] as $i => $line) {
                BomLine::create([
                    'bom_id' => $bom->id,
                    'product_id' => $line['product_id'],
                    'quantity_per_batch' => $line['quantity_per_batch'],
                    'unit' => $line['unit'],
                    'child_bom_id' => $line['child_bom_id'] ?? null,
                    'sort_order' => $i,
                ]);
            }
        });

        return back()->with('success', 'BOM berhasil diperbarui.');
    }

    public function destroyBom(Bom $bom)
    {
        abort_if($bom->tenant_id !== $this->tid(), 403);
        $bom->delete();
        return back()->with('success', 'BOM berhasil dihapus.');
    }

    /**
     * BUG-MFG-001 FIX: Validate BOM for circular references
     */
    private function validateBomCircularReference(int $bomId, array $lines): void
    {
        // Extract child_bom_ids from lines
        $childBomIds = array_filter(
            array_column($lines, 'child_bom_id'),
            fn($id) => $id !== null
        );

        if (empty($childBomIds)) {
            return; // No child BOMs, no circular reference possible
        }

        // Check each child BOM for circular reference
        foreach ($childBomIds as $childBomId) {
            // Check self-reference
            if ($childBomId == $bomId) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [
                        'lines' => "BOM tidak boleh refer ke dirinya sendiri (BOM #{$bomId})."
                    ])
                );
            }

            // Check if child BOM eventually references back to parent
            if ($this->createsCircularReference($bomId, $childBomId, [$bomId])) {
                $childBom = Bom::find($childBomId);
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [
                        'lines' => "Circular reference terdeteksi! BOM #{$bomId} → ... → BOM #{$childBomId} ({$childBom->name}) → BOM #{$bomId}."
                    ])
                );
            }
        }
    }

    /**
     * BUG-MFG-001 FIX: Check if adding childBomId to parentBomId creates circular reference
     */
    private function createsCircularReference(int $parentBomId, int $childBomId, array $visitedIds): bool
    {
        if (in_array($childBomId, $visitedIds)) {
            return true;
        }

        $visitedIds[] = $childBomId;

        // Get all child BOMs of this child
        $childBom = Bom::with('lines')->find($childBomId);
        if (!$childBom) {
            return false;
        }

        foreach ($childBom->lines as $line) {
            if ($line->child_bom_id) {
                if ($line->child_bom_id == $parentBomId) {
                    return true; // Direct circular reference found
                }

                if (in_array($line->child_bom_id, $visitedIds)) {
                    continue; // Already visited, skip
                }

                // Recurse
                if ($this->createsCircularReference($parentBomId, $line->child_bom_id, $visitedIds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * BUG-MFG-001 FIX: Validate BOM lines for circular references (for new BOMs)
     */
    private function validateBomLinesForCircularReference(?int $bomId, array $lines): void
    {
        // Extract child_bom_ids from lines
        $childBomIds = array_filter(
            array_column($lines, 'child_bom_id'),
            fn($id) => $id !== null
        );

        if (empty($childBomIds)) {
            return; // No child BOMs
        }

        // Check each child BOM
        foreach ($childBomIds as $childBomId) {
            // For new BOMs, we can't check circular reference yet
            // But we can validate that child BOM exists and is active
            $childBom = Bom::where('id', $childBomId)
                ->where('tenant_id', $this->tid())
                ->where('is_active', true)
                ->first();

            if (!$childBom) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [
                        'lines' => "Child BOM #{$childBomId} tidak ditemukan atau tidak aktif."
                    ])
                );
            }

            // Check if child BOM references itself
            $childBomLines = $childBom->lines()->where('child_bom_id', $childBomId)->exists();
            if ($childBomLines) {
                throw new \Illuminate\Validation\ValidationException(
                    validator([], [
                        'lines' => "Child BOM #{$childBomId} ({$childBom->name}) sudah memiliki circular reference internal."
                    ])
                );
            }
        }
    }

    // ── Work Centers ──────────────────────────────────────────────

    public function workCenters(Request $request)
    {
        $workCenters = WorkCenter::where('tenant_id', $this->tid())
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('code', 'like', "%$s%"))
            ->latest()->paginate(20)->withQueryString();

        return view('manufacturing.work-centers', compact('workCenters'));
    }

    public function storeWorkCenter(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'cost_per_hour' => 'nullable|numeric|min:0',
            'capacity_per_day' => 'nullable|integer|min:1|max:24',
            'notes' => 'nullable|string|max:1000',
        ]);

        WorkCenter::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'cost_per_hour' => $data['cost_per_hour'] ?? 0,
            'capacity_per_day' => $data['capacity_per_day'] ?? 8,
            'is_active' => true,
        ]));

        return back()->with('success', 'Work Center berhasil dibuat.');
    }

    public function updateWorkCenter(Request $request, WorkCenter $workCenter)
    {
        abort_if($workCenter->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'cost_per_hour' => 'nullable|numeric|min:0',
            'capacity_per_day' => 'nullable|integer|min:1|max:24',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $workCenter->update($data);
        return back()->with('success', 'Work Center berhasil diperbarui.');
    }

    public function destroyWorkCenter(WorkCenter $workCenter)
    {
        abort_if($workCenter->tenant_id !== $this->tid(), 403);
        $workCenter->delete();
        return back()->with('success', 'Work Center berhasil dihapus.');
    }

    // ── MRP ───────────────────────────────────────────────────────

    public function mrp(Request $request, MrpService $mrpService)
    {
        $boms = Bom::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $results = null;
        $selectedBom = null;
        $quantity = $request->quantity ?? 1;

        if ($request->filled('bom_id')) {
            $selectedBom = Bom::with('lines.product')->where('tenant_id', $this->tid())->find($request->bom_id);
            if ($selectedBom) {
                $results = $mrpService->calculate($selectedBom, (float) $quantity, $this->tid());
            }
        }

        // Full MRP — all pending WOs
        $fullMrp = null;
        if ($request->has('full_mrp')) {
            $fullMrp = $mrpService->runFullMrp($this->tid());
        }

        return view('manufacturing.mrp', compact('boms', 'results', 'selectedBom', 'quantity', 'fullMrp'));
    }

    // ── Material Consumption (called from ProductionController) ──

    public function consumeMaterials(WorkOrder $workOrder, MrpService $mrpService, GlPostingService $glService)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);

        if ($workOrder->materials_consumed) {
            return back()->with('error', 'Material sudah dikonsumsi sebelumnya.');
        }

        $result = $mrpService->consumeMaterials($workOrder);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // GL Posting: Dr WIP (1106) / Cr Persediaan (1105)
        if ($result['material_cost'] > 0) {
            $glResult = $glService->postProductionConsumption(
                $workOrder->tenant_id,
                auth()->id(),
                $workOrder->number,
                $workOrder->id,
                $result['material_cost']
            );

            if ($glResult->isSuccess()) {
                $workOrder->update(['journal_entry_id' => $glResult->journal->id]);
            }
            if ($glResult->isFailed()) {
                session()->flash('gl_warning', $glResult->warningMessage());
            }
        }

        $msg = 'Material berhasil dikonsumsi. Total biaya: Rp ' . number_format($result['material_cost'], 0, ',', '.');
        if (!empty($result['shortages'])) {
            $msg .= ' ⚠️ Kekurangan stok: ' . implode(', ', $result['shortages']);
        }

        return back()->with('success', $msg);
    }

    /**
     * Show barcode scanning interface for material consumption
     */
    public function scanMaterials(WorkOrder $workOrder)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);

        $workOrder->load(['product', 'bom.lines.product']);

        // Calculate required materials based on target quantity
        $requiredMaterials = [];
        if ($workOrder->bom) {
            $explodedBom = $workOrder->bom->explode($workOrder->target_quantity);

            // Group by product_id and sum quantities
            foreach ($explodedBom as $item) {
                $productId = $item['product_id'];
                if (!isset($requiredMaterials[$productId])) {
                    $product = Product::find($productId);
                    $requiredMaterials[$productId] = [
                        'product_id' => $productId,
                        'product' => $product,
                        'barcode' => $product?->barcode ?? $product?->sku ?? '',
                        'quantity_required' => 0,
                        'quantity_scanned' => 0,
                        'unit' => $item['unit'],
                    ];
                }
                $requiredMaterials[$productId]['quantity_required'] += $item['quantity'];
            }
        }

        return view('manufacturing.work-orders.scan-materials', compact('workOrder', 'requiredMaterials'));
    }

    /**
     * Process scanned material consumption
     */
    public function consumeScannedMaterials(Request $request, WorkOrder $workOrder, MrpService $mrpService, GlPostingService $glService)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);

        if ($workOrder->materials_consumed) {
            return back()->with('error', 'Material sudah dikonsumsi sebelumnya.');
        }

        $data = $request->validate([
            'scanned_materials' => 'required|array',
            'scanned_materials.*.product_id' => 'required|exists:products,id',
            'scanned_materials.*.quantity' => 'required|numeric|min:0.001',
        ]);

        // Validate that all scanned materials match BOM requirements
        $workOrder->load('bom');
        $bomProductIds = [];
        if ($workOrder->bom) {
            $explodedBom = $workOrder->bom->explode($workOrder->target_quantity);
            $bomProductIds = array_unique(array_column($explodedBom, 'product_id'));
        }

        foreach ($data['scanned_materials'] as $material) {
            if (!in_array((int) $material['product_id'], $bomProductIds)) {
                return back()->with('error', "Produk ID {$material['product_id']} tidak ada di BOM.");
            }
        }

        // Call existing MrpService::consumeMaterials() - it will handle the actual consumption
        $result = $mrpService->consumeMaterials($workOrder);

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        // GL Posting
        if ($result['material_cost'] > 0) {
            $glResult = $glService->postProductionConsumption(
                $workOrder->tenant_id,
                auth()->id(),
                $workOrder->number,
                $workOrder->id,
                $result['material_cost']
            );

            if ($glResult->isSuccess()) {
                $workOrder->update(['journal_entry_id' => $glResult->journal->id]);
            }
            if ($glResult->isFailed()) {
                session()->flash('gl_warning', $glResult->warningMessage());
            }
        }

        $msg = 'Material dari scan berhasil dikonsumsi. Total biaya: Rp ' . number_format($result['material_cost'], 0, ',', '.');
        if (!empty($result['shortages'])) {
            $msg .= ' ⚠️ Kekurangan stok: ' . implode(', ', $result['shortages']);
        }

        return redirect()->route('production.index')->with('success', $msg);
    }

    // ── Quality Control ───────────────────────────────────────────

    public function qualityDashboard(QualityControlService $qcService)
    {
        $statistics = $qcService->getStatistics();
        $defectAnalysis = $qcService->getDefectAnalysis();

        $recentChecks = QualityCheck::where('tenant_id', $this->tid())
            ->with(['workOrder', 'product', 'inspector', 'defects'])
            ->latest('inspected_at')
            ->limit(20)
            ->get();

        $openDefects = DefectRecord::where('tenant_id', $this->tid())
            ->whereNull('resolved_at')
            ->with(['product', 'workOrder', 'reportedBy'])
            ->orderBy('severity')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $standards = QualityCheckStandard::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('manufacturing.quality.dashboard', compact(
            'statistics',
            'defectAnalysis',
            'recentChecks',
            'openDefects',
            'standards'
        ));
    }

    public function qualityChecks(Request $request, QualityControlService $qcService)
    {
        $query = QualityCheck::with(['workOrder', 'product', 'standard', 'inspector', 'defects'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('inspected_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('inspected_at', '<=', $request->date_to);
        }

        $qualityChecks = $query->latest('inspected_at')->paginate(20)->withQueryString();

        $statistics = $qcService->getStatistics();

        return view('manufacturing.quality.checks', compact('qualityChecks', 'statistics'));
    }

    public function createQualityCheck()
    {
        $workOrders = WorkOrder::where('tenant_id', $this->tid())
            ->whereIn('status', ['in_progress', 'completed'])
            ->whereNull('quality_status')
            ->orderByDesc('created_at')
            ->get();

        $products = Product::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $standards = QualityCheckStandard::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('manufacturing.quality.create', compact('workOrders', 'products', 'standards'));
    }

    public function storeQualityCheck(Request $request, QualityControlService $qcService)
    {
        $validated = $request->validate([
            'work_order_id' => 'nullable|exists:work_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'standard_id' => 'nullable|exists:quality_check_standards,id',
            'stage' => 'required|in:incoming,in_process,final',
            'sample_size' => 'required|numeric|min:1',
            'inspector_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $qualityCheck = $qcService->createQualityCheck($validated);

        return redirect()->route('manufacturing.quality.checks.edit', $qualityCheck)
            ->with('success', 'Quality check created: ' . $qualityCheck->check_number);
    }

    public function editQualityCheck(QualityCheck $qualityCheck)
    {
        abort_if($qualityCheck->tenant_id !== $this->tid(), 403);

        $qualityCheck->load(['workOrder', 'product', 'standard', 'inspector', 'defects']);

        return view('manufacturing.quality.edit', compact('qualityCheck'));
    }

    public function updateQualityCheck(Request $request, QualityCheck $qualityCheck, QualityControlService $qcService)
    {
        abort_if($qualityCheck->tenant_id !== $this->tid(), 403);

        $validated = $request->validate([
            'results' => 'required|array',
            'sample_passed' => 'required|numeric|min:0',
            'sample_failed' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
            'corrective_action' => 'nullable|string',
        ]);

        $qualityCheck = $qcService->submitResults(
            $qualityCheck,
            $validated['results'],
            [
                'sample_passed' => $validated['sample_passed'],
                'sample_failed' => $validated['sample_failed'],
                'notes' => $validated['notes'],
                'corrective_action' => $validated['corrective_action'],
            ]
        );

        $message = $qualityCheck->status === 'passed'
            ? 'Quality check passed!'
            : 'Quality check ' . str_replace('_', ' ', $qualityCheck->status);

        return redirect()->route('manufacturing.quality.checks')
            ->with('success', $message);
    }

    public function recordDefect(Request $request, QualityControlService $qcService)
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

        $defect = $qcService->recordDefect($validated);

        return back()->with('success', 'Defect recorded: ' . $defect->defect_code);
    }

    public function resolveDefect(Request $request, DefectRecord $defect, QualityControlService $qcService)
    {
        abort_if($defect->tenant_id !== $this->tid(), 403);

        $validated = $request->validate([
            'root_cause' => 'required|string',
            'corrective_action' => 'required|string',
            'preventive_action' => 'nullable|string',
        ]);

        $qcService->resolveDefect($defect, $validated);

        return back()->with('success', 'Defect resolved: ' . $defect->defect_code);
    }

    public function defectRecords(Request $request)
    {
        $query = DefectRecord::with(['qualityCheck', 'product', 'workOrder', 'reportedBy', 'resolvedBy'])
            ->where('tenant_id', $this->tid());

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

        if ($request->filled('defect_type')) {
            $query->where('defect_type', $request->defect_type);
        }

        $defects = $query->latest()->paginate(20)->withQueryString();

        return view('manufacturing.quality.defects', compact('defects'));
    }

    public function qualityStandards(Request $request)
    {
        $standards = QualityCheckStandard::where('tenant_id', $this->tid())
            ->withCount('qualityChecks')
            ->orderBy('name')
            ->paginate(20);

        return view('manufacturing.quality.standards', compact('standards'));
    }

    public function storeQualityStandard(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:quality_check_standards,code',
            'description' => 'nullable|string',
            'stage' => 'required|in:incoming,in_process,final',
            'parameters' => 'required|array',
            'parameters.*.name' => 'required|string',
            'parameters.*.min_value' => 'nullable|numeric',
            'parameters.*.max_value' => 'nullable|numeric',
            'parameters.*.unit' => 'nullable|string',
            'parameters.*.critical' => 'boolean',
        ]);

        QualityCheckStandard::create([
            'tenant_id' => $this->tid(),
            'name' => $validated['name'],
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'stage' => $validated['stage'],
            'parameters' => $validated['parameters'],
            'is_active' => true,
        ]);

        return back()->with('success', 'Quality standard created: ' . $validated['code']);
    }
}
