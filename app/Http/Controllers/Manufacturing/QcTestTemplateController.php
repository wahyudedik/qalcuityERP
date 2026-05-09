<?php

namespace App\Http\Controllers\Manufacturing;

use App\Http\Controllers\Controller;
use App\Models\QcTestTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * QC Test Template Controller
 *
 * TASK-2.20: Create QC test templates
 */
class QcTestTemplateController extends Controller
{
    /**
     * Display a listing of QC test templates
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $templates = QcTestTemplate::where('tenant_id', $tenantId)
            ->withCount('inspections')
            ->orderBy('name')
            ->paginate(20);

        return view('qc.templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new QC test template
     */
    public function create()
    {
        return view('qc.templates.create');
    }

    /**
     * Store a newly created QC test template
     */
    public function store(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'nullable|string|max:255',
            'stage' => 'required|in:incoming,in-process,final',
            'test_parameters' => 'required|array',
            'test_parameters.*.name' => 'required|string',
            'test_parameters.*.min' => 'nullable|numeric',
            'test_parameters.*.max' => 'nullable|numeric',
            'test_parameters.*.unit' => 'nullable|string',
            'test_parameters.*.critical' => 'boolean',
            'sample_size_formula' => 'required|integer|in:1,2,3',
            'acceptance_quality_limit' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'instructions' => 'nullable|string',
        ]);

        QcTestTemplate::create([
            'tenant_id' => $tenantId,
            'name' => $data['name'],
            'product_type' => $data['product_type'] ?? null,
            'stage' => $data['stage'],
            'test_parameters' => $data['test_parameters'],
            'sample_size_formula' => $data['sample_size_formula'],
            'acceptance_quality_limit' => $data['acceptance_quality_limit'],
            'is_active' => $data['is_active'] ?? true,
            'instructions' => $data['instructions'] ?? null,
        ]);

        return redirect()->route('qc.templates.index')
            ->with('success', 'QC test template created successfully');
    }

    /**
     * Display the specified QC test template
     */
    public function show(QcTestTemplate $template)
    {
        abort_if($template->tenant_id !== Auth::user()->tenant_id, 403);

        $template->load([
            'inspections' => function ($query) {
                $query->latest()->limit(10);
            },
        ]);

        return view('qc.templates.show', compact('template'));
    }

    /**
     * Show the form for editing the specified QC test template
     */
    public function edit(QcTestTemplate $template)
    {
        abort_if($template->tenant_id !== Auth::user()->tenant_id, 403);

        return view('qc.templates.edit', compact('template'));
    }

    /**
     * Update the specified QC test template
     */
    public function update(Request $request, QcTestTemplate $template)
    {
        abort_if($template->tenant_id !== Auth::user()->tenant_id, 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'nullable|string|max:255',
            'stage' => 'required|in:incoming,in-process,final',
            'test_parameters' => 'required|array',
            'test_parameters.*.name' => 'required|string',
            'test_parameters.*.min' => 'nullable|numeric',
            'test_parameters.*.max' => 'nullable|numeric',
            'test_parameters.*.unit' => 'nullable|string',
            'test_parameters.*.critical' => 'boolean',
            'sample_size_formula' => 'required|integer|in:1,2,3',
            'acceptance_quality_limit' => 'required|numeric|min:0|max:100',
            'is_active' => 'boolean',
            'instructions' => 'nullable|string',
        ]);

        $template->update([
            'name' => $data['name'],
            'product_type' => $data['product_type'] ?? null,
            'stage' => $data['stage'],
            'test_parameters' => $data['test_parameters'],
            'sample_size_formula' => $data['sample_size_formula'],
            'acceptance_quality_limit' => $data['acceptance_quality_limit'],
            'is_active' => $data['is_active'] ?? true,
            'instructions' => $data['instructions'] ?? null,
        ]);

        return redirect()->route('qc.templates.index')
            ->with('success', 'QC test template updated successfully');
    }

    /**
     * Remove the specified QC test template
     */
    public function destroy(QcTestTemplate $template)
    {
        abort_if($template->tenant_id !== Auth::user()->tenant_id, 403);

        // Prevent deletion if template has inspections
        if ($template->inspections()->count() > 0) {
            return back()->with('error', 'Cannot delete template with existing inspections');
        }

        $template->delete();

        return redirect()->route('qc.templates.index')
            ->with('success', 'QC test template deleted successfully');
    }

    /**
     * Toggle template active status
     */
    public function toggleStatus(QcTestTemplate $template)
    {
        abort_if($template->tenant_id !== Auth::user()->tenant_id, 403);

        $template->update(['is_active' => ! $template->is_active]);

        return back()->with('success', 'Template status updated');
    }

    /**
     * Calculate sample size for a template
     */
    public function calculateSampleSize(Request $request, QcTestTemplate $template)
    {
        abort_if($template->tenant_id !== Auth::user()->tenant_id, 403);

        $data = $request->validate([
            'lot_size' => 'required|integer|min:1',
        ]);

        $sampleSize = $template->calculateSampleSize($data['lot_size']);

        return response()->json([
            'sample_size' => $sampleSize,
            'formula' => $template->sample_size_formula,
            'lot_size' => $data['lot_size'],
        ]);
    }
}
