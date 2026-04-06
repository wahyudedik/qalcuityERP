<?php

namespace App\Http\Controllers\Fnb;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Recipe;
use App\Services\RecipeCostCalculatorService;
use Illuminate\Http\Request;

class RecipeCostController extends Controller
{
    protected $costCalculator;

    public function __construct(RecipeCostCalculatorService $costCalculator)
    {
        $this->costCalculator = $costCalculator;
    }

    /**
     * Display recipe cost calculator dashboard
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $recipes = Recipe::where('tenant_id', $tenantId)
            ->with(['menuItem'])
            ->orderBy('name')
            ->get();

        $lowMarginRecipes = $this->costCalculator->getLowMarginRecipes($tenantId);

        return view('fnb.recipes.index', compact('recipes', 'lowMarginRecipes'));
    }

    /**
     * Calculate and display recipe cost details
     */
    public function calculate(Recipe $recipe)
    {
        $this->authorizeAccess($recipe);

        $costData = $this->costCalculator->calculateRecipeCost($recipe);

        return view('fnb.recipes.calculate', compact('recipe', 'costData'));
    }

    /**
     * Get recipe cost via API (for real-time updates)
     */
    public function apiCalculate(Recipe $recipe)
    {
        $this->authorizeAccess($recipe);

        $costData = $this->costCalculator->calculateRecipeCost($recipe);

        return response()->json($costData);
    }

    /**
     * Store new recipe
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_item_id' => 'required|exists:menu_items,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:0.1',
            'yield_unit' => 'required|string|max:50',
            'preparation_time_minutes' => 'nullable|integer|min:1',
            'cooking_time_minutes' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;

        Recipe::create($validated);

        return redirect()->route('fnb.recipes.index')
            ->with('success', 'Recipe created successfully');
    }

    /**
     * Update recipe
     */
    public function update(Request $request, Recipe $recipe)
    {
        $this->authorizeAccess($recipe);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'yield_quantity' => 'required|numeric|min:0.1',
            'yield_unit' => 'required|string|max:50',
            'preparation_time_minutes' => 'nullable|integer|min:1',
            'cooking_time_minutes' => 'nullable|integer|min:1',
            'instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $recipe->update($validated);

        return back()->with('success', 'Recipe updated successfully');
    }

    /**
     * Add ingredient to recipe
     */
    public function addIngredient(Request $request, Recipe $recipe)
    {
        $this->authorizeAccess($recipe);

        $validated = $request->validate([
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'ingredient_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|min:0.001',
            'unit' => 'required|string|max:50',
            'cost_per_unit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $validated['tenant_id'] = auth()->user()->tenant_id;
        $validated['recipe_id'] = $recipe->id;

        \App\Models\RecipeIngredient::create($validated);

        return back()->with('success', 'Ingredient added successfully');
    }

    /**
     * Update recipe ingredient
     */
    public function updateIngredient(Request $request, \App\Models\RecipeIngredient $ingredient)
    {
        $this->authorizeAccess($ingredient);

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:0.001',
            'cost_per_unit' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $ingredient->update($validated);

        return back()->with('success', 'Ingredient updated successfully');
    }

    /**
     * Delete recipe ingredient
     */
    public function deleteIngredient(\App\Models\RecipeIngredient $ingredient)
    {
        $this->authorizeAccess($ingredient);

        $ingredient->delete();

        return back()->with('success', 'Ingredient deleted');
    }

    /**
     * Bulk update all recipe costs from inventory prices
     */
    public function bulkUpdateCosts()
    {
        $tenantId = auth()->user()->tenant_id;
        $updated = $this->costCalculator->bulkUpdateCostsFromInventory($tenantId);

        return redirect()->route('fnb.recipes.index')
            ->with('success', "Updated {$updated} ingredient costs from current inventory prices");
    }

    /**
     * Show low margin recipes report
     */
    public function lowMarginReport(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $threshold = $request->input('threshold', 30);

        $lowMarginRecipes = $this->costCalculator->getLowMarginRecipes($tenantId, $threshold);

        return view('fnb.recipes.low-margin', compact('lowMarginRecipes', 'threshold'));
    }

    private function authorizeAccess($model): void
    {
        if ($model->tenant_id !== auth()->user()->tenant_id) {
            abort(403, 'Unauthorized access');
        }
    }
}
