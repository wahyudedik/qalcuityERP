<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\CosmeticFormula;
use App\Models\IngredientRestriction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormulaBuilderController extends Controller
{
    /**
     * Show formula builder interface
     */
    public function create($formulaId = null)
    {
        $formula = null;
        $ingredients = collect();

        if ($formulaId) {
            $formula = CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->with('ingredients')
                ->findOrFail($formulaId);
            $ingredients = $formula->ingredients;
        }

        $rawMaterials = Product::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_raw_material', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get(['id', 'name', 'sku', 'average_cost', 'stock_quantity', 'unit']);

        $ingredientFunctions = [
            'emollient' => 'Emollient',
            'preservative' => 'Preservative',
            'active' => 'Active Ingredient',
            'fragrance' => 'Fragrance',
            'emulsifier' => 'Emulsifier',
            'thickener' => 'Thickener',
            'humectant' => 'Humectant',
            'surfactant' => 'Surfactant',
            'colorant' => 'Colorant',
            'solvent' => 'Solvent',
            'ph_adjuster' => 'pH Adjuster',
            'antioxidant' => 'Antioxidant',
        ];

        $phases = [
            'oil_phase' => 'Oil Phase',
            'water_phase' => 'Water Phase',
            'cool_down_phase' => 'Cool Down Phase',
        ];

        $units = ['g', 'ml', '%', 'drops', 'units'];

        return view('cosmetic.formulas.builder', compact(
            'formula',
            'ingredients',
            'rawMaterials',
            'ingredientFunctions',
            'phases',
            'units'
        ));
    }

    /**
     * Search ingredients from raw materials
     */
    public function searchIngredients(Request $request)
    {
        $search = $request->get('q', '');

        $materials = Product::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_raw_material', true)
            ->whereNull('deleted_at')
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'sku', 'average_cost', 'stock_quantity', 'unit']);

        return response()->json($materials);
    }

    /**
     * Validate ingredient restrictions
     */
    public function validateIngredient(Request $request)
    {
        $validated = $request->validate([
            'inci_name' => 'required|string',
            'percentage' => 'nullable|numeric|min:0|max:100',
            'function' => 'nullable|string',
        ]);

        $restrictions = IngredientRestriction::where('tenant_id', Auth::user()->tenant_id)
            ->where(function ($query) use ($validated) {
                $query->where('inci_name', $validated['inci_name'])
                    ->orWhere('cas_number', $validated['inci_name']);
            })
            ->get();

        $warnings = [];
        $errors = [];

        foreach ($restrictions as $restriction) {
            if ($restriction->restriction_type === 'banned') {
                $errors[] = [
                    'type' => 'banned',
                    'message' => "Ingredient is banned: {$restriction->reason}",
                    'severity' => 'error',
                ];
            } elseif ($restriction->restriction_type === 'limited' && $validated['percentage']) {
                if ($validated['percentage'] > $restriction->max_limit) {
                    $errors[] = [
                        'type' => 'exceeds_limit',
                        'message' => "Percentage exceeds maximum limit ({$restriction->max_limit}%)",
                        'severity' => 'error',
                    ];
                } elseif ($validated['percentage'] > $restriction->max_limit * 0.8) {
                    $warnings[] = [
                        'type' => 'approaching_limit',
                        'message' => "Approaching maximum limit ({$restriction->max_limit}%)",
                        'severity' => 'warning',
                    ];
                }
            }
        }

        // Check function-specific safe limits
        if ($validated['function'] && $validated['percentage']) {
            $safeLimits = $this->getSafeLimits();
            if (isset($safeLimits[$validated['function']])) {
                $limit = $safeLimits[$validated['function']];
                if ($validated['percentage'] > $limit) {
                    $warnings[] = [
                        'type' => 'function_limit',
                        'message' => "Percentage exceeds recommended safe limit for {$validated['function']} ({$limit}%)",
                        'severity' => 'warning',
                    ];
                }
            }
        }

        return response()->json([
            'valid' => empty($errors),
            'warnings' => $warnings,
            'errors' => $errors,
        ]);
    }

    /**
     * Calculate formula totals
     */
    public function calculateTotals(Request $request)
    {
        $validated = $request->validate([
            'ingredients' => 'required|array',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.percentage' => 'nullable|numeric|min:0|max:100',
            'batch_size' => 'required|numeric|min:0',
        ]);

        $totalQuantity = collect($validated['ingredients'])->sum('quantity');
        $totalPercentage = collect($validated['ingredients'])->sum('percentage') ?? 0;
        $totalCost = 0;

        foreach ($validated['ingredients'] as $ing) {
            if (isset($ing['product_id']) && $ing['product_id']) {
                $product = Product::find($ing['product_id']);
                if ($product) {
                    $unitCost = $product->average_cost ?? 0;
                    $quantity = $ing['quantity'];
                    $totalCost += $unitCost * ($quantity / 1000); // Convert to kg
                }
            }
        }

        $costPerUnit = $totalQuantity > 0 ? $totalCost / ($totalQuantity / 1000) : 0;

        return response()->json([
            'total_quantity' => $totalQuantity,
            'total_percentage' => $totalPercentage,
            'total_cost' => $totalCost,
            'cost_per_unit' => $costPerUnit,
            'percentage_valid' => abs($totalPercentage - 100) < 0.01,
        ]);
    }

    /**
     * Get safe limits for ingredient functions
     */
    protected function getSafeLimits()
    {
        return [
            'preservative' => 1.0,
            'fragrance' => 2.0,
            'colorant' => 5.0,
            'active' => 10.0,
            'ph_adjuster' => 2.0,
        ];
    }
}
