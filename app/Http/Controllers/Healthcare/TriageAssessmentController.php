<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\TriageAssessment;
use App\Models\EmergencyVisit;
use App\Models\Patient;
use Illuminate\Http\Request;

class TriageAssessmentController extends Controller
{
    /**
     * Display a listing of triage assessments.
     */
    public function index(Request $request)
    {
        $query = TriageAssessment::query()->with(['patient', 'nurse', 'doctor']);

        if ($request->filled('priority_level')) {
            $query->where('priority_level', $request->priority_level);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('assessment_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('assessment_time', '<=', $request->date_to);
        }

        $assessments = $query->orderBy('assessment_time', 'desc')->paginate(20);

        $statistics = [
            'total_assessments' => TriageAssessment::count(),
            'critical' => TriageAssessment::where('priority_level', 'critical')->count(),
            'emergency' => TriageAssessment::where('priority_level', 'emergency')->count(),
            'urgent' => TriageAssessment::where('priority_level', 'urgent')->count(),
            'non_urgent' => TriageAssessment::where('priority_level', 'non_urgent')->count(),
            'pending' => TriageAssessment::where('status', 'pending')->count(),
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
            'patient_id' => 'required|exists:patients,id',
            'emergency_visit_id' => 'nullable|exists:emergency_visits,id',
            'nurse_id' => 'required|exists:users,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'assessment_time' => 'required|date',
            'chief_complaint' => 'required|string',
            'priority_level' => 'required|in:critical,emergency,urgent,semi_urgent,non_urgent',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'blood_pressure_systolic' => 'nullable|integer|min:50|max:300',
            'blood_pressure_diastolic' => 'nullable|integer|min:30|max:200',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'consciousness_level' => 'nullable|in:alert,voice,pain,unresponsive',
            'triage_notes' => 'nullable|string',
            'immediate_intervention' => 'nullable|string',
        ]);

        $validated['triage_code'] = $this->generateTriageCode($validated['priority_level']);

        $assessment = TriageAssessment::create($validated);

        return redirect()->route('healthcare.triage.show', $assessment)
            ->with('success', 'Triage assessment completed: ' . $assessment->triage_code);
    }

    /**
     * Display the specified triage assessment.
     */
    public function show(TriageAssessment $assessment)
    {
        $assessment->load(['patient', 'nurse', 'doctor', 'emergencyVisit']);

        return view('healthcare.triage.show', compact('assessment'));
    }

    /**
     * Show the form for editing the specified triage assessment.
     */
    public function edit(TriageAssessment $assessment)
    {
        return view('healthcare.triage.edit', compact('assessment'));
    }

    /**
     * Update the specified triage assessment.
     */
    public function update(Request $request, TriageAssessment $assessment)
    {
        $validated = $request->validate([
            'priority_level' => 'required|in:critical,emergency,urgent,semi_urgent,non_urgent',
            'chief_complaint' => 'required|string',
            'temperature' => 'nullable|numeric|min:30|max:45',
            'heart_rate' => 'nullable|integer|min:30|max:250',
            'blood_pressure_systolic' => 'nullable|integer|min:50|max:300',
            'blood_pressure_diastolic' => 'nullable|integer|min:30|max:200',
            'respiratory_rate' => 'nullable|integer|min:5|max:60',
            'oxygen_saturation' => 'nullable|integer|min:50|max:100',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'consciousness_level' => 'nullable|in:alert,voice,pain,unresponsive',
            'triage_notes' => 'nullable|string',
            'immediate_intervention' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,referred',
        ]);

        if ($validated['priority_level'] !== $assessment->priority_level) {
            $validated['triage_code'] = $this->generateTriageCode($validated['priority_level']);
        }

        $assessment->update($validated);

        return redirect()->route('healthcare.triage.index')
            ->with('success', 'Triage assessment updated successfully');
    }

    /**
     * Remove the specified triage assessment.
     */
    public function destroy(TriageAssessment $assessment)
    {
        $assessment->delete();

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
        $queue = TriageAssessment::with(['patient', 'nurse'])
            ->whereIn('status', ['pending', 'in_progress'])
            ->orderByRaw("FIELD(priority_level, 'critical', 'emergency', 'urgent', 'semi_urgent', 'non_urgent')")
            ->orderBy('assessment_time')
            ->get();

        return view('healthcare.triage.queue', compact('queue'));
    }

    /**
     * Update triage status.
     */
    public function updateStatus(Request $request, TriageAssessment $assessment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,referred',
        ]);

        $assessment->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Triage status updated successfully',
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
                'total' => TriageAssessment::whereDate('assessment_time', $today)->count(),
                'critical' => TriageAssessment::whereDate('assessment_time', $today)->where('priority_level', 'critical')->count(),
                'emergency' => TriageAssessment::whereDate('assessment_time', $today)->where('priority_level', 'emergency')->count(),
                'urgent' => TriageAssessment::whereDate('assessment_time', $today)->where('priority_level', 'urgent')->count(),
            ],
            'current_wait_time' => TriageAssessment::where('status', 'pending')
                ->avg(fn($q) => $q->whereRaw('TIMESTAMPDIFF(MINUTE, assessment_time, NOW())')),
            'pending_count' => TriageAssessment::where('status', 'pending')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}
