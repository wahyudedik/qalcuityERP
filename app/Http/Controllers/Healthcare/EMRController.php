<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedicalRecord;
use App\Models\PatientVisit;
use App\Models\PharmacyInventory;
use App\Services\EMRService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EMRController extends Controller
{
    private EMRService $emrService;

    public function __construct(EMRService $emrService)
    {
        $this->emrService = $emrService;
    }

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
                'date' => $labOrder->created_at,
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

    /**
     * Patient dashboard with vital signs and overview
     */
    public function dashboard($patientId)
    {
        $dashboardData = $this->emrService->getPatientDashboard($patientId);

        return view('healthcare.emr.dashboard', $dashboardData);
    }

    /**
     * Get vital signs chart data (AJAX)
     */
    public function getVitalSignsChart($patientId, Request $request)
    {
        $days = $request->get('days', 30);
        $trend = $this->emrService->getVitalSignsTrend($patientId, $days);

        return response()->json([
            'success' => true,
            'data' => $trend,
        ]);
    }

    /**
     * Create SOAP format visit note
     */
    public function createSOAPNote(Request $request, $visitId)
    {
        $visit = PatientVisit::with(['patient', 'doctor'])->findOrFail($visitId);

        // If GET request, show form
        if ($request->isMethod('get')) {
            $previousRecords = PatientMedicalRecord::where('patient_id', $visit->patient_id)
                ->latest()
                ->limit(5)
                ->get();

            return view('healthcare.emr.soap-note', compact('visit', 'previousRecords'));
        }

        // POST request - save SOAP note
        $validated = $request->validate([
            'subjective' => 'required|array',
            'subjective.chief_complaint' => 'required|string',
            'subjective.history_of_present_illness' => 'nullable|string',
            'objective' => 'required|array',
            'objective.vital_signs' => 'nullable|array',
            'objective.physical_examination' => 'nullable|string',
            'assessment' => 'required|array',
            'assessment.diagnoses' => 'nullable|array',
            'plan' => 'required|array',
            'plan.treatment_plan' => 'nullable|string',
        ]);

        $soapNote = $this->emrService->buildSOAPNote($validated);
        $validation = $this->emrService->validateSOAPNote($soapNote);

        // Create medical record from SOAP
        $record = PatientMedicalRecord::create([
            'patient_id' => $visit->patient_id,
            'visit_id' => $visit->id,
            'doctor_id' => $visit->doctor_id ?? Auth::id(),
            'record_date' => now(),
            'chief_complaint' => $soapNote['subjective']['chief_complaint'],
            'history_of_present_illness' => $soapNote['subjective']['history_of_present_illness'],
            'vital_signs' => $soapNote['objective']['vital_signs'],
            'physical_examination' => $soapNote['objective']['physical_examination'],
            'diagnosis' => $soapNote['assessment']['diagnoses'][0]['description'] ?? '',
            'treatment_plan' => $soapNote['plan']['treatment_plan'],
            'doctor_notes' => json_encode($soapNote),
            'status' => 'completed',
        ]);

        // Clear cache
        $this->emrService->clearDashboardCache($visit->patient_id);

        return redirect()->route('healthcare.emr.dashboard', $visit->patient_id)
            ->with('success', 'SOAP note saved successfully');
    }

    /**
     * Search ICD-10 codes (AJAX)
     */
    public function searchICD10(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $this->emrService->searchICD10($query);

        return response()->json($results);
    }

    /**
     * Check drug interactions (AJAX)
     */
    public function checkDrugInteractions(Request $request)
    {
        $validated = $request->validate([
            'medications' => 'required|array',
            'medications.*' => 'required|string',
        ]);

        $result = $this->emrService->checkDrugInteractions($validated['medications']);

        return response()->json($result);
    }

    /**
     * Get patient timeline (AJAX or view)
     */
    public function getTimeline($patientId, Request $request)
    {
        $filters = [
            'type' => $request->get('type', 'all'),
            'date_from' => $request->get('date_from'),
            'date_to' => $request->get('date_to'),
        ];

        $timeline = $this->emrService->getPatientTimeline($patientId, $filters);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $timeline,
            ]);
        }

        $patient = Patient::findOrFail($patientId);

        return view('healthcare.emr.timeline', compact('patient', 'timeline'));
    }

    /**
     * Print prescription
     */
    public function printPrescription($prescriptionId)
    {
        $data = $this->emrService->generatePrescriptionPDF($prescriptionId);

        return view('healthcare.emr.prescription-print', $data);
    }

    /**
     * Show prescribe form for a visit.
     */
    public function prescribeForm($visitId)
    {
        $visit = PatientVisit::with(['patient', 'doctor'])->findOrFail($visitId);

        $pharmacyItems = PharmacyInventory::where('stock_quantity', '>', 0)
            ->orderBy('item_name')
            ->get(['id', 'item_name', 'generic_name', 'unit_of_measure', 'stock_quantity']);

        return view('healthcare.emr.prescribe', compact('visit', 'pharmacyItems'));
    }

    /**
     * Show diagnose form for a visit.
     */
    public function diagnoseForm($visitId)
    {
        $visit = PatientVisit::with(['patient', 'doctor', 'diagnoses'])->findOrFail($visitId);

        return view('healthcare.emr.diagnose', compact('visit'));
    }
}
