<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\CriticalAlert;
use App\Models\TriageAssessment;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    /**
     * Store a new triage assessment.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'triage_date' => 'required|date',
            'chief_complaint' => 'required|string',
            'triage_level' => 'required|in:red,yellow,green,black',
            'temperature' => 'nullable|numeric',
            'heart_rate' => 'nullable|integer',
            'respiratory_rate' => 'nullable|integer',
            'blood_pressure_systolic' => 'nullable|integer',
            'blood_pressure_diastolic' => 'nullable|integer',
            'spo2' => 'nullable|integer',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'gcs_score' => 'nullable|integer|min:3|max:15',
            'requires_immediate_intervention' => 'boolean',
            'requires_resuscitation' => 'boolean',
            'requires_isolation' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $triage = TriageAssessment::create($validated);

        // Create critical alert if red triage
        if ($validated['triage_level'] === 'red' || $validated['requires_immediate_intervention']) {
            CriticalAlert::create([
                'patient_id' => $validated['patient_id'],
                'alert_type' => 'critical_triage',
                'severity' => 'critical',
                'description' => 'Critical triage assessment: '.$validated['chief_complaint'],
                'triage_assessment_id' => $triage->id,
            ]);
        }

        return redirect()->route('healthcare.er.triage.show', $triage)
            ->with('success', 'Triage assessment created successfully');
    }

    /**
     * Display triage assessment.
     */
    public function show(TriageAssessment $triage)
    {
        $triage->load(['patient', 'doctor']);

        $ews = $triage->calculateEarlyWarningScore();

        return view('healthcare.er.triage.show', compact('triage', 'ews'));
    }

    /**
     * Update triage assessment.
     */
    public function update(Request $request, TriageAssessment $triage)
    {
        $validated = $request->validate([
            'triage_level' => 'required|in:red,yellow,green,black',
            'vital_signs' => 'nullable|array',
            'pain_scale' => 'nullable|integer|min:0|max:10',
            'gcs_score' => 'nullable|integer|min:3|max:15',
            'notes' => 'nullable|string',
            'disposition' => 'nullable|in:discharged,admitted,transferred,referred,awaiting_treatment',
        ]);

        $triage->update($validated);

        // Update severity if triage level changed to red
        if ($validated['triage_level'] === 'red' && $triage->criticalAlert) {
            $triage->criticalAlert->update(['severity' => 'critical']);
        }

        return back()->with('success', 'Triage assessment updated successfully');
    }
}
