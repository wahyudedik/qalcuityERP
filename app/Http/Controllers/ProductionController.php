<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\Product;
use App\Models\ProductionOutput;
use App\Models\ProductStock;
use App\Models\Recipe;
use App\Models\RecipeIngredient;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WorkOrder;
use App\Services\GlPostingService;
use App\Services\ProductionCostingService; // BUG-MFG-003 FIX
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductionController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Work Orders ───────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = WorkOrder::with(['product', 'recipe'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhereHas('product', fn($p) => $p->where('name', 'like', "%$s%")));
        }

        $workOrders = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'pending' => WorkOrder::where('tenant_id', $this->tid())->where('status', 'pending')->count(),
            'in_progress' => WorkOrder::where('tenant_id', $this->tid())->where('status', 'in_progress')->count(),
            'completed' => WorkOrder::where('tenant_id', $this->tid())->where('status', 'completed')->count(),
        ];

        $products = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $recipes = Recipe::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $boms = Bom::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('production.index', compact('workOrders', 'stats', 'products', 'recipes', 'boms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'recipe_id' => 'nullable|exists:recipes,id',
            'bom_id' => 'nullable|exists:boms,id',
            'target_quantity' => 'required|numeric|min:0.001',
            'labor_cost' => 'nullable|numeric|min:0',
            'overhead_cost' => 'nullable|numeric|min:0',
            // BUG-MFG-003 FIX: Add overhead calculation method
            'overhead_method' => 'nullable|in:manual,work_center,percentage_of_labor,percentage_of_material',
            'overhead_rate' => 'nullable|numeric|min:0|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = Product::findOrFail($data['product_id']);

        WorkOrder::create([
            'tenant_id' => $this->tid(),
            'product_id' => $data['product_id'],
            'recipe_id' => $data['recipe_id'] ?? null,
            'bom_id' => $data['bom_id'] ?? null,
            'user_id' => auth()->id(),
            'number' => 'WO-' . date('Ymd') . '-' . strtoupper(Str::random(4)),
            'target_quantity' => $data['target_quantity'],
            'unit' => $product->unit,
            'status' => 'pending',
            'labor_cost' => $data['labor_cost'] ?? 0,
            'overhead_cost' => $data['overhead_cost'] ?? 0,
            // BUG-MFG-003 FIX: Set overhead calculation method
            'overhead_method' => $data['overhead_method'] ?? 'manual',
            'overhead_rate' => $data['overhead_rate'] ?? 0,
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Work Order berhasil dibuat.');
    }

    public function updateStatus(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'status' => 'required|in:in_progress,completed,cancelled',
            'notes' => 'nullable|string|max:500',
        ]);

        if (!$workOrder->canTransitionTo($data['status'])) {
            return back()->with('error', "Tidak bisa mengubah status dari {$workOrder->status} ke {$data['status']}.");
        }

        $updates = ['status' => $data['status']];

        if ($data['status'] === 'in_progress' && !$workOrder->started_at) {
            $updates['started_at'] = now();
        }
        if ($data['status'] === 'completed') {
            $updates['completed_at'] = now();

            // BUG-MFG-003 FIX: Auto-calculate total cost with overhead
            $costingService = app(ProductionCostingService::class);
            $costData = $costingService->autoCalculateCosts($workOrder);

            $updates['total_cost'] = $costData['total_cost'];
            $updates['overhead_cost'] = $costData['overhead_cost'];
        }
        if ($data['notes']) {
            $updates['notes'] = $data['notes'];
        }

        $workOrder->update($updates);

        return back()->with('success', 'Status Work Order diperbarui.');
    }

    public function show(WorkOrder $workOrder)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);
        $workOrder->load(['product', 'recipe.ingredients.product', 'bom.lines.product', 'outputs', 'user', 'operations.workCenter', 'journalEntry']);
        return view('production.show', compact('workOrder'));
    }

    public function recordOutput(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->tenant_id !== $this->tid(), 403);

        if ($workOrder->status !== 'in_progress') {
            return back()->with('error', 'Work Order harus dalam status "Sedang Dikerjakan" untuk mencatat output.');
        }

        $data = $request->validate([
            'good_qty' => 'required|numeric|min:0',
            'reject_qty' => 'nullable|numeric|min:0',
            'reject_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:500',
            'auto_complete' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($workOrder, $data) {
            ProductionOutput::create([
                'work_order_id' => $workOrder->id,
                'tenant_id' => $this->tid(),
                'user_id' => auth()->id(),
                'good_qty' => $data['good_qty'],
                'reject_qty' => $data['reject_qty'] ?? 0,
                'reject_reason' => $data['reject_reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if (!empty($data['auto_complete'])) {
                // BUG-MFG-003 FIX: Auto-calculate costs with overhead
                $costingService = app(ProductionCostingService::class);
                $costData = $costingService->autoCalculateCosts($workOrder);
                $totalCost = $costData['total_cost'];

                $workOrder->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'total_cost' => $totalCost,
                    'overhead_cost' => $costData['overhead_cost'],
                ]);

                // Tambah stok produk jadi
                $warehouse = Warehouse::where('tenant_id', $this->tid())->where('is_active', true)->first();
                if ($warehouse && $data['good_qty'] > 0) {
                    $stock = ProductStock::firstOrCreate(
                        ['product_id' => $workOrder->product_id, 'warehouse_id' => $warehouse->id],
                        ['quantity' => 0]
                    );
                    $before = $stock->quantity;
                    $stock->increment('quantity', $data['good_qty']);

                    StockMovement::create([
                        'tenant_id' => $this->tid(),
                        'product_id' => $workOrder->product_id,
                        'warehouse_id' => $warehouse->id,
                        'user_id' => auth()->id(),
                        'type' => 'in',
                        'quantity' => $data['good_qty'],
                        'quantity_before' => $before,
                        'quantity_after' => $before + $data['good_qty'],
                        'reference' => $workOrder->number,
                        'notes' => "Output produksi {$workOrder->number}",
                    ]);
                }

                // GL: Transfer WIP → Persediaan Barang Jadi
                if ($totalCost > 0) {
                    $glService = app(GlPostingService::class);
                    $glResult = $glService->postProductionOutput(
                        $workOrder->tenant_id,
                        auth()->id(),
                        $workOrder->number,
                        $workOrder->id,
                        $totalCost
                    );
                    if ($glResult->isFailed()) {
                        session()->flash('gl_warning', $glResult->warningMessage());
                    }
                }
            }
        });

        return back()->with('success', 'Output produksi berhasil dicatat.');
    }

    // ── Recipes ───────────────────────────────────────────────────

    public function recipes(Request $request)
    {
        $recipes = Recipe::with(['product', 'ingredients.product'])
            ->where('tenant_id', $this->tid())
            ->latest()->paginate(20);
        $products = Product::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('production.recipes', compact('recipes', 'products'));
    }

    public function storeRecipe(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'name' => 'required|string|max:255',
            'batch_size' => 'required|numeric|min:0.001',
            'batch_unit' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.product_id' => 'required|exists:products,id',
            'ingredients.*.quantity_per_batch' => 'required|numeric|min:0.001',
            'ingredients.*.unit' => 'required|string|max:20',
        ]);

        DB::transaction(function () use ($data) {
            $recipe = Recipe::create([
                'tenant_id' => $this->tid(),
                'product_id' => $data['product_id'],
                'name' => $data['name'],
                'batch_size' => $data['batch_size'],
                'batch_unit' => $data['batch_unit'],
                'notes' => $data['notes'] ?? null,
                'is_active' => true,
            ]);

            foreach ($data['ingredients'] as $ing) {
                RecipeIngredient::create([
                    'recipe_id' => $recipe->id,
                    'product_id' => $ing['product_id'],
                    'quantity_per_batch' => $ing['quantity_per_batch'],
                    'unit' => $ing['unit'],
                ]);
            }
        });

        return back()->with('success', 'Resep/BOM berhasil disimpan.');
    }

    // BUG-MFG-003 FIX: Cost Analysis endpoint
    public function costAnalysis(Request $request)
    {
        $months = $request->input('months', 3);
        $costingService = app(ProductionCostingService::class);

        $analysis = $costingService->getCostAnalysis($this->tid(), $months);

        return view('production.cost-analysis', compact('analysis', 'months'));
    }
}
