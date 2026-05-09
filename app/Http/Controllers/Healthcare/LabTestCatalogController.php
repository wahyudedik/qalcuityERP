<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\LabTestCatalog;
use App\Models\LabTestParameter;
use Illuminate\Http\Request;

class LabTestCatalogController extends Controller
{
    /**
     * Display a listing of lab tests.
     */
    public function index(Request $request)
    {
        $query = LabTestCatalog::query()->withCount('parameters');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('test_code', 'like', "%{$search}%")
                    ->orWhere('test_name', 'like', "%{$search}%");
            });
        }

        $tests = $query->orderBy('category')->orderBy('test_name')->paginate(20);

        $categories = LabTestCatalog::distinct()->pluck('category')->sort();

        $statistics = [
            'total_tests' => LabTestCatalog::count(),
            'active_tests' => LabTestCatalog::where('is_active', true)->count(),
            'categories' => LabTestCatalog::distinct('category')->count(),
        ];

        return view('healthcare.lab-tests.index', compact('tests', 'categories', 'statistics'));
    }

    /**
     * Show the form for creating a new lab test.
     */
    public function create()
    {
        return view('healthcare.lab-tests.create');
    }

    /**
     * Store a newly created lab test.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'test_code' => 'required|string|unique:lab_test_catalogs,test_code|max:50',
            'test_name' => 'required|string|max:255',
            'category' => 'required|in:hematology,chemistry,microbiology,urinalysis,immunology,blood_bank,molecular',
            'specimen_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'turnaround_time_hours' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'preparation_instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $test = LabTestCatalog::create($validated);

        return redirect()->route('healthcare.lab-tests.show', $test)
            ->with('success', 'Lab test created: '.$test->test_code);
    }

    /**
     * Display the specified lab test.
     */
    public function show(LabTestCatalog $test)
    {
        $test->load('parameters');

        return view('healthcare.lab-tests.show', compact('test'));
    }

    /**
     * Show the form for editing the specified lab test.
     */
    public function edit(LabTestCatalog $test)
    {
        return view('healthcare.lab-tests.edit', compact('test'));
    }

    /**
     * Update the specified lab test.
     */
    public function update(Request $request, LabTestCatalog $test)
    {
        $validated = $request->validate([
            'test_name' => 'required|string|max:255',
            'category' => 'required|in:hematology,chemistry,microbiology,urinalysis,immunology,blood_bank,molecular',
            'specimen_type' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'turnaround_time_hours' => 'required|integer|min:1',
            'description' => 'nullable|string',
            'preparation_instructions' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $test->update($validated);

        return redirect()->route('healthcare.lab-tests.index')
            ->with('success', 'Lab test updated successfully');
    }

    /**
     * Remove the specified lab test.
     */
    public function destroy(LabTestCatalog $test)
    {
        $test->delete();

        return redirect()->route('healthcare.lab-tests.index')
            ->with('success', 'Lab test deleted successfully');
    }

    /**
     * Manage test parameters.
     */
    public function manageParameters(LabTestCatalog $test)
    {
        $test->load('parameters');

        return view('healthcare.lab-tests.parameters', compact('test'));
    }

    /**
     * Add parameter to test.
     */
    public function addParameter(Request $request, LabTestCatalog $test)
    {
        $validated = $request->validate([
            'parameter_name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'reference_range_min' => 'nullable|numeric',
            'reference_range_max' => 'nullable|numeric',
            'critical_low' => 'nullable|numeric',
            'critical_high' => 'nullable|numeric',
            'is_required' => 'boolean',
        ]);

        $validated['is_required'] = $request->has('is_required');

        $parameter = $test->parameters()->create($validated);

        return response()->json([
            'success' => true,
            'data' => $parameter,
            'message' => 'Parameter added successfully',
        ]);
    }

    /**
     * Remove parameter from test.
     */
    public function removeParameter(LabTestParameter $parameter)
    {
        $parameter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Parameter removed successfully',
        ]);
    }
}
