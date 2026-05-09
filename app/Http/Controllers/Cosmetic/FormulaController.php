<?php

namespace App\Http\Controllers\Cosmetic;

use App\Http\Controllers\Controller;
use App\Models\CosmeticFormula;
use App\Models\FormulaIngredient;
use App\Models\FormulaVersion;
use App\Models\Product;
use App\Models\StabilityTest;
use App\Services\CosmeticFormulaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormulaController extends Controller
{
    protected $formulaService;

    public function __construct(CosmeticFormulaService $formulaService)
    {
        $this->formulaService = $formulaService;
    }

    /**
     * Display all cosmetic formulas
     */
    public function index(Request $request)
    {
        $stats = [
            'total_formulas' => CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)->count(),
            'in_testing' => CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->where('status', 'testing')->count(),
            'approved' => CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->where('status', 'approved')->count(),
            'in_production' => CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->where('status', 'production')->count(),
        ];

        $query = CosmeticFormula::with(['creator', 'approver', 'ingredients'])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->orderByDesc('created_at');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by product type
        if ($request->filled('product_type')) {
            $query->where('product_type', $request->product_type);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('formula_code', 'like', "%{$search}%")
                    ->orWhere('formula_name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $formulas = $query->paginate(20);

        $productTypes = CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
            ->distinct()
            ->pluck('product_type')
            ->sort()
            ->values();

        return view('cosmetic.formulas.index', compact('stats', 'formulas', 'productTypes'));
    }

    /**
     * Show create formula form
     */
    public function create()
    {
        $products = Product::where('tenant_id', Auth::user()->tenant_id)
            ->where('is_raw_material', true)
            ->orderBy('name')
            ->get();

        return view('cosmetic.formulas.create', compact('products'));
    }

    /**
     * Store new formula
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'formula_name' => 'required|string|max:255',
            'product_type' => 'required|string',
            'brand' => 'nullable|string|max:255',
            'target_ph' => 'nullable|numeric|min:0|max:14',
            'shelf_life_months' => 'nullable|integer|min:1',
            'batch_size' => 'required|numeric|min:0',
            'batch_unit' => 'required|in:grams,ml,units',
            'notes' => 'nullable|string',
            'ingredients' => 'required|array|min:1',
            'ingredients.*.inci_name' => 'required|string',
            'ingredients.*.common_name' => 'nullable|string',
            'ingredients.*.cas_number' => 'nullable|string',
            'ingredients.*.product_id' => 'nullable|exists:products,id',
            'ingredients.*.quantity' => 'required|numeric|min:0',
            'ingredients.*.unit' => 'required|string',
            'ingredients.*.percentage' => 'nullable|numeric|min:0|max:100',
            'ingredients.*.function' => 'nullable|string',
            'ingredients.*.phase' => 'nullable|string',
            'ingredients.*.sort_order' => 'nullable|integer',
        ]);

        try {
            $formula = new CosmeticFormula;
            $formula->tenant_id = Auth::user()->tenant_id;
            $formula->formula_code = CosmeticFormula::getNextFormulaCode();
            $formula->formula_name = $validated['formula_name'];
            $formula->product_type = $validated['product_type'];
            $formula->brand = $validated['brand'] ?? null;
            $formula->target_ph = $validated['target_ph'] ?? null;
            $formula->shelf_life_months = $validated['shelf_life_months'] ?? null;
            $formula->batch_size = $validated['batch_size'];
            $formula->batch_unit = $validated['batch_unit'];
            $formula->notes = $validated['notes'] ?? null;
            $formula->created_by = Auth::id();
            $formula->save();

            // Add ingredients
            foreach ($validated['ingredients'] as $index => $ingredientData) {
                $ingredient = new FormulaIngredient;
                $ingredient->tenant_id = Auth::user()->tenant_id;
                $ingredient->formula_id = $formula->id;
                $ingredient->inci_name = $ingredientData['inci_name'];
                $ingredient->common_name = $ingredientData['common_name'] ?? null;
                $ingredient->cas_number = $ingredientData['cas_number'] ?? null;
                $ingredient->product_id = $ingredientData['product_id'] ?? null;
                $ingredient->quantity = $ingredientData['quantity'];
                $ingredient->unit = $ingredientData['unit'];
                $ingredient->percentage = $ingredientData['percentage'] ?? null;
                $ingredient->function = $ingredientData['function'] ?? null;
                $ingredient->phase = $ingredientData['phase'] ?? null;
                $ingredient->sort_order = $ingredientData['sort_order'] ?? ($index + 1);
                $ingredient->save();
            }

            // Calculate total cost
            $formula->calculateTotalCost();

            return redirect()->route('cosmetic.formulas.show', $formula)
                ->with('success', 'Formula created successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Failed to create formula: '.$e->getMessage());
        }
    }

    /**
     * Display formula details
     */
    public function show($id)
    {
        $formula = CosmeticFormula::with([
            'ingredients.product',
            'versions.changer',
            'stabilityTests.tester',
        ])
            ->where('tenant_id', Auth::user()->tenant_id)
            ->findOrFail($id);

        $ingredients = $formula->ingredients;
        $versions = $formula->versions;
        $stabilityTests = $formula->stabilityTests;

        // Calculate totals
        $totalQuantity = $ingredients->sum('quantity');
        $totalCost = $formula->total_cost;

        return view('cosmetic.formulas.show', compact(
            'formula',
            'ingredients',
            'versions',
            'stabilityTests',
            'totalQuantity',
            'totalCost'
        ));
    }

    /**
     * Update formula status
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,testing,approved,production,discontinued',
            'approval_notes' => 'nullable|string',
        ]);

        try {
            $formula = CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($id);

            $formula->status = $validated['status'];

            if ($validated['status'] === 'approved') {
                $formula->approved_by = Auth::id();
                $formula->approved_at = now();
            }

            $formula->save();

            // Create version record if status changed to approved
            if ($validated['status'] === 'approved') {
                $version = new FormulaVersion;
                $version->tenant_id = Auth::user()->tenant_id;
                $version->formula_id = $formula->id;
                $version->version_number = 'v1.0';
                $version->changes_summary = 'Initial approval';
                $version->reason_for_change = 'Formula met all requirements';
                $version->changed_by = Auth::id();
                $version->approval_notes = $validated['approval_notes'] ?? null;
                $version->save();
            }

            return back()->with('success', 'Formula status updated to '.$formula->status_label);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Add stability test
     */
    public function addStabilityTest(Request $request, $id)
    {
        $validated = $request->validate([
            'test_type' => 'required|in:accelerated,real_time,freeze_thaw,photostability',
            'start_date' => 'required|date',
            'expected_end_date' => 'nullable|date|after:start_date',
            'storage_conditions' => 'required|string',
            'initial_ph' => 'nullable|numeric|min:0|max:14',
            'initial_appearance' => 'nullable|string',
            'initial_viscosity' => 'nullable|numeric|min:0',
        ]);

        try {
            $formula = CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($id);

            $test = new StabilityTest;
            $test->tenant_id = Auth::user()->tenant_id;
            $test->formula_id = $formula->id;
            $test->test_code = StabilityTest::getNextTestCode();
            $test->test_type = $validated['test_type'];
            $test->start_date = $validated['start_date'];
            $test->expected_end_date = $validated['expected_end_date'] ?? null;
            $test->storage_conditions = $validated['storage_conditions'];
            $test->initial_ph = $validated['initial_ph'] ?? null;
            $test->initial_appearance = $validated['initial_appearance'] ?? null;
            $test->initial_viscosity = $validated['initial_viscosity'] ?? null;
            $test->tested_by = Auth::id();
            $test->save();

            return back()->with('success', 'Stability test added successfully!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Update stability test results
     */
    public function updateStabilityTest(Request $request, $testId)
    {
        $validated = $request->validate([
            'final_ph' => 'nullable|numeric|min:0|max:14',
            'final_appearance' => 'nullable|string',
            'final_viscosity' => 'nullable|numeric|min:0',
            'microbial_results' => 'nullable|string',
            'color_change' => 'nullable|string',
            'odor_change' => 'nullable|string',
            'separation' => 'nullable|string',
            'overall_result' => 'required|in:Pass,Fail,Inconclusive',
            'observations' => 'nullable|string',
        ]);

        try {
            $test = StabilityTest::where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($testId);

            $test->fill($validated);
            $test->complete($validated['overall_result'], $validated['observations'] ?? '');

            return back()->with('success', 'Stability test results updated!');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete formula
     */
    public function destroy($id)
    {
        try {
            $formula = CosmeticFormula::where('tenant_id', Auth::user()->tenant_id)
                ->findOrFail($id);

            $formula->delete();

            return redirect()->route('cosmetic.formulas.index')
                ->with('success', 'Formula deleted successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
