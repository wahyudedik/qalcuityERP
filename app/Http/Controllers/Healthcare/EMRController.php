<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\PatientMedicalRecord;
use App\Models\Patient;
use App\Models\PatientVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EMRController extends Controller
{
    /**
     * Display a listing of medical records.
     */
    public function index(Request $request)
    {
        $query = PatientMedicalRecord::with(['patient', 'doctor', 'visit']);

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('record_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('record_date', '<=', $request->date_to);
        }

        $records = $query->latest()->paginate(20);

        return view('healthcare.emr.index', compact('records'));
    }

    /**
     * Display medical records for a patient.
     */
    public function show($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $records = $patient->medicalRecords()
            ->with(['doctor', 'visit'])
            ->latest()
            ->paginate(30);

        return view('healthcare.emr.show', compact('patient', 'records'));
    }

    /**
     * Store a new medical record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'visit_id' => 'nullable|exists:patient_visits,id',
            'doctor_id' => 'required|exists:doctors,id',
            'record_date' => 'required|date',
            'chief_complaint' => 'required|string',
            'diagnosis' => 'required|string',
            'icd10_code' => 'nullable|string|max:10',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
            'vital_signs' => 'nullable|array',
            'vital_signs.temperature' => 'nullable|numeric',
            'vital_signs.blood_pressure' => 'nullable|string',
            'vital_signs.heart_rate' => 'nullable|integer',
            'vital_signs.respiratory_rate' => 'nullable|integer',
            'vital_signs.spo2' => 'nullable|integer',
        ]);

        $record = PatientMedicalRecord::create($validated);

        return redirect()->route('healthcare.emr.show', $validated['patient_id'])
            ->with('success', 'Medical record created successfully');
    }

    /**
     * Update medical record.
     */
    public function update(Request $request, PatientMedicalRecord $emr)
    {
        $validated = $request->validate([
            'chief_complaint' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'icd10_code' => 'nullable|string|max:10',
            'treatment_plan' => 'nullable|string',
            'notes' => 'nullable|string',
            'vital_signs' => 'nullable|array',
        ]);

        $emr->update($validated);

        return back()->with('success', 'Medical record updated successfully');
    }

    /**
     * Get medical record history.
     */
    public function history(PatientMedicalRecord $emr)
    {
        $history = PatientMedicalRecord::where('patient_id', $emr->patient_id)
            ->with('doctor')
            ->latest()
            ->limit(50)
            ->get();

        return view('healthcare.emr.history', compact('emr', 'history'));
    }

    /**
     * Add diagnosis to medical record.
     */
    public function addDiagnosis(PatientMedicalRecord $emr, Request $request)
    {
        $validated = $request->validate([
            'diagnosis' => 'required|string',
            'icd10_code' => 'nullable|string|max:10',
            'diagnosis_type' => 'required|in:primary,secondary,provisional,final',
        ]);

        $diagnoses = $emr->diagnoses ?? [];
        $diagnoses[] = array_merge($validated, [
            'created_at' => now()->toISOString(),
        ]);

        $emr->update([
            'diagnoses' => $diagnoses,
            'diagnosis' => $validated['diagnosis'],
            'icd10_code' => $validated['icd10_code'],
        ]);

        return back()->with('success', 'Diagnosis added successfully');
    }

    /**
     * Add prescription to medical record.
     */
    public function addPrescription(PatientMedicalRecord $emr, Request $request)
    {
        $validated = $request->validate([
            'medication_name' => 'required|string',
            'dosage' => 'required|string',
            'frequency' => 'required|string',
            'duration' => 'required|string',
            'route' => 'required|in:oral,intravenous,intramuscular,topical,subcutaneous,rectal',
            'instructions' => 'nullable|string',
        ]);

        $prescription = $emr->patient->prescriptions()->create([
            'medical_record_id' => $emr->id,
            'doctor_id' => $emr->doctor_id,
            'medication_name' => $validated['medication_name'],
            'dosage' => $validated['dosage'],
            'frequency' => $validated['frequency'],
            'duration' => $validated['duration'],
            'route' => $validated['route'],
            'instructions' => $validated['instructions'],
            'prescribed_date' => now(),
        ]);

        return back()->with('success', 'Prescription added successfully');
    }

    /**
     * Order lab test from medical record.
     */
    public function orderLab(PatientMedicalRecord $emr, Request $request)
    {
        $validated = $request->validate([
            'lab_test_id' => 'required|exists:lab_test_catalogs,id',
            'priority' => 'required|in:routine,urgent,stat',
            'clinical_notes' => 'nullable|string',
        ]);

        $labOrder = $emr->patient->labOrders()->create([
            'medical_record_id' => $emr->id,
            'doctor_id' => $emr->doctor_id,
            'lab_test_id' => $validated['lab_test_id'],
            'priority' => $validated['priority'],
            'clinical_notes' => $validated['clinical_notes'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Lab order created successfully');
    }

    /**
     * Get patient timeline.
     */
    public function timeline($patientId)
    {
        $patient = Patient::findOrFail($patientId);

        $timeline = collect();

        // Medical records
        $patient->medicalRecords()->get()->each(function ($record) use ($timeline) {
            $timeline->push([
                'date' => $record->record_date,
                'type' => 'medical_record',
                'title' => 'Medical Record',
                'description' => $record->chief_complaint,
                'doctor' => $record->doctor?->name,
            ]);
        });

        // Lab results
        $patient->labOrders()->with('results')->get()->each(function ($labOrder) use ($timeline) {
            $timeline->push([
                'date' => $labOrder->order_date,
                'type' => 'lab',
                'title' => 'Lab Test: ' . $labOrder->labTest?->test_name,
                'description' => 'Status: ' . $labOrder->status,
            ]);
        });

        $timeline = $timeline->sortByDesc('date')->values();

        return view('healthcare.emr.timeline', compact('patient', 'timeline'));
    }

    /**
     * Export medical records.
     */
    public function export($patientId)
    {
        $patient = Patient::with([
            'medicalRecords.doctor',
            'visits',
            'labOrders.results',
            'prescriptions',
            'allergies',
        ])->findOrFail($patientId);

        // Generate PDF or Excel export
        return view('healthcare.emr.export', compact('patient'));
    }
}
