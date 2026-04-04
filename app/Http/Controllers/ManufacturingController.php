<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomLine;
use App\Models\Product;
use App\Models\WorkCenter;
use App\Models\WorkOrder;
use App\Services\GlPostingService;
use App\Services\MrpService;
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
}
