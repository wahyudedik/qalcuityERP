<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\ProductVariant;
use App\Models\VariantAttribute;
use App\Models\VariantInventory;
use App\Models\CosmeticFormula;
use Illuminate\Http\Request;

class VariantController extends Controller
{
    /**
     * Display variant attributes
     */
    public function attributes()
    {
        $tenantId = auth()->user()->tenant_id;
        $attributes = VariantAttribute::where('tenant_id', $tenantId)
            ->ordered()
            ->paginate(20);

        return view('cosmetic.variants.attributes', compact('attributes'));
    }

    /**
     * Store variant attribute
     */
    public function storeAttribute(Request $request)
    {
        $validated = $request->validate([
            'attribute_name' => 'required|string|max:255',
            'attribute_type' => 'required|in:select,color,text,number',
            'attribute_values' => 'nullable|array',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ]);

        VariantAttribute::create([
            'tenant_id' => auth()->user()->tenant_id,
            'attribute_name' => $validated['attribute_name'],
            'attribute_type' => $validated['attribute_type'],
            'attribute_values' => $validated['attribute_values'] ?? [],
            'is_required' => $validated['is_required'] ?? false,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Variant attribute created!');
    }

    /**
     * Display product variants
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        // Stats
        $stats = [
            'total_variants' => ProductVariant::where('tenant_id', $tenantId)->count(),
            'active_variants' => ProductVariant::where('tenant_id', $tenantId)->active()->count(),
            'low_stock' => ProductVariant::where('tenant_id', $tenantId)->lowStock()->count(),
            'out_of_stock' => ProductVariant::where('tenant_id', $tenantId)->outOfStock()->count(),
        ];

        // Variants with filters
        $variants = ProductVariant::where('tenant_id', $tenantId)
            ->with(['formula', 'inventoryTransactions'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($query) use ($request) {
                $query->where('variant_name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            }))
            ->when($request->stock_status, function ($q) use ($request) {
                if ($request->stock_status === 'low') {
                    $q->lowStock();
                } elseif ($request->stock_status === 'out') {
                    $q->outOfStock();
                } elseif ($request->stock_status === 'in') {
                    $q->inStock();
                }
            })
            ->latest()
            ->paginate(20);

        $formulas = CosmeticFormula::where('tenant_id', $tenantId)
            ->where('status', 'approved')
            ->get();

        $attributes = VariantAttribute::where('tenant_id', $tenantId)
            ->ordered()
            ->get();

        return view('cosmetic.variants.index', compact('stats', 'variants', 'formulas', 'attributes'));
    }

    /**
     * Store product variant
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'required|exists:cosmetic_formulas,id',
            'variant_name' => 'required|string|max:255',
            'sku' => 'nullable|unique:product_variants,sku',
            'barcode' => 'nullable|string',
            'variant_attributes' => 'required|array',
            'price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'integer|min:0',
            'reorder_level' => 'integer|min:0',
            'notes' => 'nullable|string',
        ]);

        // Auto-generate SKU if not provided
        if (!$validated['sku']) {
            $formula = CosmeticFormula::find($validated['formula_id']);
            $validated['sku'] = ProductVariant::generateSKU($formula->formula_code, $validated['variant_attributes']);
        }

        $variant = ProductVariant::create([
            'tenant_id' => auth()->user()->tenant_id,
            'formula_id' => $validated['formula_id'],
            'variant_name' => $validated['variant_name'],
            'sku' => $validated['sku'],
            'barcode' => $validated['barcode'] ?? null,
            'variant_attributes' => $validated['variant_attributes'],
            'price' => $validated['price'] ?? null,
            'cost_price' => $validated['cost_price'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? 0,
            'reorder_level' => $validated['reorder_level'] ?? 10,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Record initial stock if > 0
        if ($variant->stock_quantity > 0) {
            $variant->inventoryTransactions()->create([
                'tenant_id' => $variant->tenant_id,
                'transaction_date' => now(),
                'transaction_type' => 'in',
                'quantity' => $variant->stock_quantity,
                'balance' => $variant->stock_quantity,
                'notes' => 'Initial stock',
            ]);
        }

        return redirect()->route('cosmetic.variants.index')
            ->with('success', 'Product variant created with SKU: ' . $variant->sku);
    }

    /**
     * Update variant stock
     */
    public function updateStock(Request $request, $id)
    {
        $tenantId = auth()->user()->tenant_id;
        $variant = ProductVariant::where('tenant_id', $tenantId)->findOrFail($id);

        $validated = $request->validate([
            'action' => 'required|in:add,remove',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        try {
            if ($validated['action'] === 'add') {
                $variant->addStock($validated['quantity'], 'in', $validated['notes'] ?? '');
                $message = 'Stock added successfully';
            } else {
                $variant->removeStock($validated['quantity'], 'out', $validated['notes'] ?? '');
                $message = 'Stock removed successfully';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * View variant inventory history
     */
    public function inventory($id)
    {
        $tenantId = auth()->user()->tenant_id;
        $variant = ProductVariant::where('tenant_id', $tenantId)
            ->with(['formula'])
            ->findOrFail($id);

        $transactions = VariantInventory::where('tenant_id', $tenantId)
            ->where('variant_id', $id)
            ->with('variant')
            ->latest('transaction_date')
            ->paginate(50);

        return view('cosmetic.variants.inventory', compact('variant', 'transactions'));
    }

    /**
     * Generate variant matrix (all combinations)
     */
    public function generateMatrix(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'required|exists:cosmetic_formulas,id',
            'attributes' => 'required|array',
        ]);

        $formula = CosmeticFormula::find($validated['formula_id']);
        $attributes = $validated['attributes']; // ['color' => ['Red', 'Blue'], 'size' => ['30ml', '50ml']]

        // Generate all combinations
        $combinations = $this->generateCombinations($attributes);

        return response()->json([
            'formula' => $formula,
            'combinations' => $combinations,
            'total' => count($combinations)
        ]);
    }

    /**
     * Generate all combinations (Cartesian product)
     */
    private function generateCombinations(array $attributes): array
    {
        $result = [[]];

        foreach ($attributes as $key => $values) {
            $temp = [];
            foreach ($result as $res) {
                foreach ($values as $value) {
                    $res[$key] = $value;
                    $temp[] = $res;
                }
            }
            $result = $temp;
        }

        return $result;
    }

    /**
     * Bulk create variants from matrix
     */
    public function bulkCreate(Request $request)
    {
        $validated = $request->validate([
            'formula_id' => 'required|exists:cosmetic_formulas,id',
            'variants' => 'required|array',
            'variants.*.variant_name' => 'required|string',
            'variants.*.variant_attributes' => 'required|array',
            'variants.*.price' => 'nullable|numeric',
            'variants.*.stock_quantity' => 'integer|min:0',
        ]);

        $formula = CosmeticFormula::find($validated['formula_id']);
        $created = 0;

        foreach ($validated['variants'] as $variantData) {
            $sku = ProductVariant::generateSKU($formula->formula_code, $variantData['variant_attributes']);

            ProductVariant::create([
                'tenant_id' => auth()->user()->tenant_id,
                'formula_id' => $validated['formula_id'],
                'variant_name' => $variantData['variant_name'],
                'sku' => $sku,
                'variant_attributes' => $variantData['variant_attributes'],
                'price' => $variantData['price'] ?? null,
                'stock_quantity' => $variantData['stock_quantity'] ?? 0,
            ]);

            $created++;
        }

        return redirect()->back()->with('success', "Successfully created {$created} variants!");
    }
}
