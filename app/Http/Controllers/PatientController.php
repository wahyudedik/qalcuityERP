<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientVisit;
use App\Models\PatientAllergy;
use App\Models\PatientInsurance;
use App\Services\PatientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientController extends Controller
{
    protected PatientService $patientService;

    public function __construct(PatientService $patientService)
    {
        $this->patientService = $patientService;
    }

    /**
     * Display a listing of patients
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $filters = $request->only(['status', 'blood_type', 'gender', 'has_allergies', 'has_chronic_diseases', 'age_min', 'age_max', 'insurance_provider']);

        $patients = $this->patientService->searchPatients($search, $filters, 20);

        return view('healthcare.patients.index', compact('patients', 'search', 'filters'));
    }

    /**
     * Show the form for creating a new patient
     */
    public function create()
    {
        return view('healthcare.patients.create');
    }

    /**
     * Store a newly created patient in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik' => 'nullable|string|max:16|unique:patients,nik',
            'full_name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'birth_place' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'religion' => 'nullable|string|max:50',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'occupation' => 'nullable|string|max:255',
            'nationality' => 'nullable|string|max:100',
            'phone_primary' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_street' => 'nullable|string',
            'address_rt' => 'nullable|string|max:10',
            'address_rw' => 'nullable|string|max:10',
            'address_kelurahan' => 'nullable|string|max:100',
            'address_kecamatan' => 'nullable|string|max:100',
            'address_city' => 'nullable|string|max:100',
            'address_province' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relation' => 'nullable|string|max:100',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_class' => 'nullable|string|max:50',
            'known_allergies' => 'nullable|array',
            'chronic_diseases' => 'nullable|array',
            'current_medications' => 'nullable|array',
            'medical_notes' => 'nullable|string',
            'primary_doctor_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'id_card' => 'nullable|image|max:2048',
            'insurance_card' => 'nullable|image|max:2048',
        ]);

        $validated['registered_by'] = Auth::id();

        $patient = $this->patientService->createPatient($validated);

        return redirect()->route('healthcare.patients.show', $patient)
            ->with('success', 'Patient registered successfully. Medical Record Number: ' . $patient->medical_record_number);
    }

    /**
     * Display the specified patient
     */
    public function show(Patient $patient, Request $request)
    {
        $tab = $request->input('tab', 'profile');

        // Load necessary relationships
        $patient->load([
            'registeredBy',
            'primaryDoctor',
            'allergyRecords' => function ($query) {
                $query->active()->orderBy('severity');
            },
            'insuranceRecords' => function ($query) {
                $query->active()->orderBy('is_primary', 'desc');
            },
            'visits' => function ($query) {
                $query->latest('visit_date')->limit(10);
            },
            'appointments' => function ($query) {
                $query->upcoming()->limit(5);
            },
        ]);

        $statistics = $this->patientService->getPatientStatistics($patient);
        $timeline = $this->patientService->getPatientTimeline($patient, 20);

        return view('healthcare.patients.show', compact('patient', 'tab', 'statistics', 'timeline'));
    }

    /**
     * Show the form for editing the specified patient
     */
    public function edit(Patient $patient)
    {
        return view('healthcare.patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient in storage
     */
    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:255',
            'birth_date' => 'required|date',
            'birth_place' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'phone_primary' => 'required|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_street' => 'nullable|string',
            'address_city' => 'nullable|string|max:100',
            'address_province' => 'nullable|string|max:100',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:2048',
            'id_card' => 'nullable|image|max:2048',
        ]);

        $this->patientService->updatePatient($patient, $validated);

        return redirect()->route('healthcare.patients.show', $patient)
            ->with('success', 'Patient updated successfully');
    }

    /**
     * Search patients by QR code or manual input
     */
    public function search(Request $request)
    {
        $query = $request->input('query', '');
        $searchType = $request->input('type', 'auto'); // auto, mrn, nik, phone, qr

        $patient = null;

        if ($searchType === 'qr' || str_starts_with($query, 'QR:')) {
            $qrCode = str_replace('QR:', '', $query);
            $patient = $this->patientService->getByQrCode($qrCode);
        } elseif ($searchType === 'mrn' || str_starts_with($query, 'MR-')) {
            $patient = $this->patientService->getByMedicalRecordNumber($query);
        } elseif ($searchType === 'nik' && strlen($query) === 16) {
            $patient = $this->patientService->getByNik($query);
        } else {
            // Auto search
            $patients = $this->patientService->searchPatients($query, [], 10);
            return view('healthcare.patients.search', compact('patients', 'query'));
        }

        if ($patient) {
            return redirect()->route('healthcare.patients.show', $patient);
        }

        return back()->with('error', 'Patient not found');
    }

    /**
     * Add allergy to patient
     */
    public function addAllergy(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'allergen' => 'required|string|max:255',
            'allergen_type' => 'required|in:medication,food,environmental,other',
            'severity' => 'required|in:mild,moderate,severe,life_threatening',
            'reaction_description' => 'nullable|string',
            'treatment_if_exposed' => 'nullable|string',
            'diagnosis_method' => 'nullable|in:self_reported,skin_test,blood_test,clinical',
            'is_verified' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $validated['diagnosed_by'] = Auth::id();
        $validated['diagnosed_date'] = now();

        $allergy = $this->patientService->addAllergy($patient, $validated);

        return back()->with('success', 'Allergy added successfully');
    }

    /**
     * Remove allergy from patient
     */
    public function removeAllergy(PatientAllergy $allergy)
    {
        $patient = $allergy->patient;
        $this->patientService->removeAllergy($allergy);

        return back()->with('success', 'Allergy removed successfully');
    }

    /**
     * Add insurance to patient
     */
    public function addInsurance(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'insurance_provider' => 'required|string|max:255',
            'insurance_type' => 'required|in:national,private,corporate,self_pay',
            'policy_number' => 'required|string|max:100',
            'group_number' => 'nullable|string|max:100',
            'member_id' => 'nullable|string|max:100',
            'plan_name' => 'nullable|string|max:255',
            'plan_class' => 'nullable|string|max:50',
            'coverage_limit' => 'nullable|numeric|min:0',
            'deductible' => 'nullable|numeric|min:0',
            'copay_percentage' => 'nullable|numeric|min:0|max:100',
            'effective_date' => 'required|date',
            'expiry_date' => 'required|date|after:effective_date',
            'is_primary' => 'boolean',
            'employer_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $insurance = $this->patientService->addInsurance($patient, $validated);

        return back()->with('success', 'Insurance added successfully');
    }

    /**
     * Generate QR code for patient
     */
    public function generateQrCode(Patient $patient)
    {
        $qrPath = $this->patientService->generateQrCode($patient);

        return response()->json([
            'success' => true,
            'qr_path' => $qrPath,
            'qr_data' => [
                'mrn' => $patient->medical_record_number,
                'name' => $patient->full_name,
                'dob' => $patient->birth_date->format('Y-m-d'),
                'qr_id' => $patient->qr_code,
            ],
        ]);
    }

    /**
     * Download patient QR code
     */
    public function downloadQrCode(Patient $patient)
    {
        $qrPath = 'patients/qr/' . $patient->qr_code . '.json';

        if (!Storage::disk('public')->exists($qrPath)) {
            $this->patientService->generateQrCode($patient);
        }

        $content = Storage::disk('public')->get($qrPath);

        return response($content, 200)
            ->header('Content-Type', 'application/json')
            ->header('Content-Disposition', 'attachment; filename="patient-' . $patient->medical_record_number . '-qr.json"');
    }

    /**
     * Deactivate patient
     */
    public function deactivate(Patient $patient, Request $request)
    {
        $reason = $request->input('reason', 'No reason provided');
        $this->patientService->deactivatePatient($patient, $reason);

        return back()->with('success', 'Patient deactivated successfully');
    }

    /**
     * Get patient allergies (API endpoint)
     */
    public function getAllergies(Patient $patient)
    {
        $allergies = $this->patientService->getActiveAllergies($patient);

        return response()->json([
            'success' => true,
            'allergies' => $allergies,
            'has_allergies' => $allergies->count() > 0,
        ]);
    }

    /**
     * Get patient insurance (API endpoint)
     */
    public function getInsurance(Patient $patient)
    {
        $insurance = $this->patientService->getValidInsurance($patient);

        return response()->json([
            'success' => true,
            'insurance' => $insurance,
            'is_insured' => $insurance !== null,
        ]);
    }
}
