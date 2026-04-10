<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\MinistryReport;
use Illuminate\Http\Request;

class MinistryReportController extends Controller
{
    public function index(Request $request)
    {
        $query = MinistryReport::query();

        if ($request->filled('report_type')) {
            $query->where('report_type', $request->report_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->orderBy('reporting_period', 'desc')->paginate(20);

        return view('healthcare.ministry-reports.index', compact('reports'));
    }

    public function create()
    {
        return view('healthcare.ministry-reports.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:monthly,quarterly,annual,episode',
            'reporting_period' => 'required|date',
            'report_data' => 'required|array',
            'notes' => 'nullable|string',
        ]);

        $validated['status'] = 'draft';

        $report = MinistryReport::create($validated);

        return redirect()->route('healthcare.ministry-reports.show', $report)
            ->with('success', 'Ministry report created');
    }

    public function show(MinistryReport $report)
    {
        return view('healthcare.ministry-reports.show', compact('report'));
    }

    public function submit(MinistryReport $report)
    {
        $report->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Report submitted']);
    }

    public function destroy(MinistryReport $report)
    {
        $report->delete();
        return response()->json(['success' => true, 'message' => 'Report deleted']);
    }
    /**
     * Show the form for editing.
     * Route: healthcare/ministry-reports/{ministry_report}/edit
     */
    public function edit($model)
    {
        $this->authorize('update', $model);
        
        return view('healthcare.ministry-report.edit', compact('model'));
    }
    /**
     * Update the specified resource.
     * Route: healthcare/ministry-reports/{ministry_report}
     */
    public function update(Request $request, $model)
    {
        $this->authorize('update', $model);
        
        $validated = $request->validate([
            // TODO: Add validation rules
        ]);
        
        $model->update($validated);
        
        return redirect()->route('healthcare.ministry-reports.update')
            ->with('success', 'Updated successfully.');
    }
}
