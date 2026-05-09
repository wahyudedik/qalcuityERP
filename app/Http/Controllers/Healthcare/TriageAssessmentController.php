<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\TriageAssessment;
use Illuminate\Http\Request;

class TriageAssessmentController extends Controller
{
    /**
     * Display a listing of triage assessments.
     */
    public function index(Request $request)
    {
        $query = TriageAssessment::query()->with(['patient', 'assessedBy', 'assignedDoctor']);

        if ($request->filled('urgency_level')) {
            $query->where('urgency_level', $request->urgency_level);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('assessment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('assessment_date', '<=', $request->date_to);
        }

        $assessments = $query->orderBy('assessment_date', 'desc')->paginate(20);

        $statistics = [
            'total_assessments' => TriageAssessment::count(),
            'resuscitation' => TriageAssessment::where('urgency_level', 'resuscitation')->count(),
            'emergent' => TriageAssessment::where('urgency_level', 'emergent')->count(),
            'urgent' => TriageAssessment::where('urgency_level', 'urgent')->count(),
            'less_urgent' => TriageAssessment::where('urgency_level', 'less_urgent')->count(),
            'non_urgent' => TriageAssessment::where('urgency_level', 'non_urgent')->count(),
        ];

        return view('healthcare.triage.index', compact('assessments', 'statistics'));
    }

    /**
     * Show the form for creating a new triage assessment.
     */
    public function create(Request $request)
    {
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::find($request->patient_id);
        }

        return view('healthcare.triage.create', compact('patient'));
    }

    /**
     * Store a newly created triage assessment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'case_id' => 'required|exists:emergency_cases,id',
            'assessed_by' => 'required|exists:users,id',
            'assessment_date' => 'required|date',
            'chief_complaint_details' => 'required|string',
            'urgency_level' => 'required|in:resuscitation,emergent,urgent,less_urgent,non_urgent',
            'esi_level' => 'nullable|integer|min:1|max:5',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'blood_pressure_systolic' => 'nullable|integer|min:50|max:300',
            'blood_pressure_diastolic' => 'nullable|integer|min:30|max:200',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'gcs_eye' => 'nullable|string',
            'gcs_verbal' => 'nullable|string',
            'gcs_motor' => 'nullable|string',
            'gcs_total' => 'nullable|integer',
            'nurse_notes' => 'required|string',
            'vital_signs' => 'nullable|array',
            'requires_immediate_intervention' => 'nullable|boolean',
            'requires_isolation' => 'nullable|boolean',
        ]);

        if (! isset($validated['vital_signs'])) {
            $validated['vital_signs'] = json_encode([]);
        } else {
            $validated['vital_signs'] = json_encode($validated['vital_signs']);
        }

        $assessment = TriageAssessment::create($validated);

        return redirect()->route('healthcare.triage.show', $assessment)
            ->with('success', 'Triage assessment completed successfully.');
    }

    /**
     * Display the specified triage assessment.
     */
    public function show(TriageAssessment $triage)
    {
        $triage->load(['patient', 'assessedBy', 'assignedDoctor']);

        return view('healthcare.triage.show', ['assessment' => $triage]);
    }

    /**
     * Show the form for editing the specified triage assessment.
     */
    public function edit(TriageAssessment $triage)
    {
        return view('healthcare.triage.edit', ['assessment' => $triage]);
    }

    /**
     * Update the specified triage assessment.
     */
    public function update(Request $request, TriageAssessment $triage)
    {
        $validated = $request->validate([
            'urgency_level' => 'required|in:resuscitation,emergent,urgent,less_urgent,non_urgent',
            'chief_complaint_details' => 'required|string',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'blood_pressure_systolic' => 'nullable|integer|min:50|max:300',
            'blood_pressure_diastolic' => 'nullable|integer|min:30|max:200',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'gcs_eye' => 'nullable|string',
            'gcs_verbal' => 'nullable|string',
            'gcs_motor' => 'nullable|string',
            'gcs_total' => 'nullable|integer',
            'nurse_notes' => 'nullable|string',
            'requires_immediate_intervention' => 'nullable|boolean',
            'requires_isolation' => 'nullable|boolean',
        ]);

        $triage->update($validated);

        return redirect()->route('healthcare.triage.index')
            ->with('success', 'Triage assessment updated successfully');
    }

    /**
     * Remove the specified triage assessment.
     */
    public function destroy(TriageAssessment $triage)
    {
        $triage->delete();

        return redirect()->route('healthcare.triage.index')
            ->with('success', 'Triage assessment deleted successfully');
    }

    /**
     * Generate triage code based on priority level.
     */
    private function generateTriageCode($priorityLevel)
    {
        $codes = [
            'critical' => 'T1-RED',
            'emergency' => 'T2-ORANGE',
            'urgent' => 'T3-YELLOW',
            'semi_urgent' => 'T4-GREEN',
            'non_urgent' => 'T5-BLUE',
        ];

        return $codes[$priorityLevel] ?? 'T5-BLUE';
    }

    /**
     * Get triage queue.
     */
    public function queue()
    {
        $queue = TriageAssessment::with(['patient', 'assessedBy'])
            ->orderByRaw("FIELD(urgency_level, 'resuscitation', 'emergent', 'urgent', 'less_urgent', 'non_urgent')")
            ->orderBy('assessment_date')
            ->get();

        return view('healthcare.triage.queue', compact('queue'));
    }

    /**
     * Update triage status.
     */
    public function updateStatus(Request $request, TriageAssessment $triage)
    {
        $validated = $request->validate([
            'urgency_level' => 'required|in:resuscitation,emergent,urgent,less_urgent,non_urgent',
        ]);

        $triage->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Triage updated successfully',
        ]);
    }

    /**
     * Get triage statistics.
     */
    public function statistics()
    {
        $today = now()->toDateString();

        $stats = [
            'today' => [
                'total' => TriageAssessment::whereDate('assessment_date', $today)->count(),
                'resuscitation' => TriageAssessment::whereDate('assessment_date', $today)->where('urgency_level', 'resuscitation')->count(),
                'emergent' => TriageAssessment::whereDate('assessment_date', $today)->where('urgency_level', 'emergent')->count(),
                'urgent' => TriageAssessment::whereDate('assessment_date', $today)->where('urgency_level', 'urgent')->count(),
            ],
            'avg_wait_time' => TriageAssessment::whereDate('assessment_date', $today)
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, assessment_date, NOW())) as avg')
                ->value('avg'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
