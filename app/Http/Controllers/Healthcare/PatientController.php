<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\Appointment;
use App\Models\LabResult;
use App\Models\Patient;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PatientController extends Controller
{
    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = Patient::query()->where('tenant_id', $tenantId);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('patient_number', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('phone_primary', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('patient_type')) {
            $query->where('patient_type', $request->patient_type);
        }

        $patients = $query->latest()->paginate(20)->withQueryString();

        // Stats - optimized with caching
        $cacheKey = "stats:patients:{$tenantId}";
        $stats = DashboardCacheService::getStats($cacheKey, function () use ($tenantId) {
            $patientStats = Patient::where('tenant_id', $tenantId)
                ->selectRaw('
                    COUNT(*) as total,
                    SUM(CASE WHEN status = \'active\' THEN 1 ELSE 0 END) as active
                ')
                ->first();

            $todayAppointments = Appointment::where('tenant_id', $tenantId)
                ->whereDate('appointment_date', today())
                ->count();

            $admittedPatients = Admission::where('tenant_id', $tenantId)
                ->where('status', 'admitted')
                ->count();

            return [
                'total_patients' => $patientStats->total ?? 0,
                'active_patients' => $patientStats->active ?? 0,
                'today_appointments' => $todayAppointments,
                'admitted_patients' => $admittedPatients,
            ];
        }, 300);

        return view('healthcare.patients.index', compact('patients', 'stats'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        return view('healthcare.patients.create');
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16|unique:patients,nik',
            'birth_date' => 'required|date',
            'birth_place' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'phone_primary' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_street' => 'nullable|string',
            'address_rt' => 'nullable|string|max:10',
            'address_rw' => 'nullable|string|max:10',
            'address_kelurahan' => 'nullable|string|max:100',
            'address_kecamatan' => 'nullable|string|max:100',
            'address_city' => 'nullable|string|max:100',
            'address_province' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:5',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'religion' => 'nullable|in:islam,christian,catholic,hindu,buddhist,confucian',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_valid_until' => 'nullable|date',
            'insurance_type' => 'nullable|in:bpjs,private,corporate,self_pay',
            'known_allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'current_medications' => 'nullable|string',
        ]);

        // Convert comma-separated strings to arrays for JSON columns
        if (! empty($validated['known_allergies'])) {
            $validated['known_allergies'] = array_map('trim', explode(',', $validated['known_allergies']));
        }
        if (! empty($validated['chronic_diseases'])) {
            $validated['chronic_diseases'] = array_map('trim', explode(',', $validated['chronic_diseases']));
        }
        if (! empty($validated['current_medications'])) {
            $validated['current_medications'] = array_map('trim', explode(',', $validated['current_medications']));
        }

        $validated['tenant_id'] = $tenantId;
        $validated['status'] = 'active';
        $patient = Patient::create($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:patients:{$tenantId}");

        return redirect()->route('healthcare.patients.show', $patient)
            ->with('success', 'Pasien berhasil didaftarkan: ' . $patient->medical_record_number);
    }

    /**
     * Display the specified patient.
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'visits' => function ($q) {
                $q->latest()->limit(10);
            },
            'medicalRecords' => function ($q) {
                $q->latest()->limit(10);
            },
            'allergyRecords',
            'insuranceRecords',
        ]);

        $statistics = [
            'total_visits' => $patient->visits()->count(),
            'total_appointments' => $patient->appointments()->count(),
            'total_lab_orders' => $patient->labOrders()->count(),
            'total_prescriptions' => $patient->prescriptions()->count(),
        ];

        return view('healthcare.patients.show', compact('patient', 'statistics'));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        return view('healthcare.patients.edit', compact('patient'));
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient)
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:16|unique:patients,nik,' . $patient->id,
            'birth_date' => 'required|date',
            'birth_place' => 'nullable|string|max:100',
            'gender' => 'required|in:male,female',
            'phone_primary' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address_street' => 'nullable|string',
            'address_rt' => 'nullable|string|max:10',
            'address_rw' => 'nullable|string|max:10',
            'address_kelurahan' => 'nullable|string|max:100',
            'address_kecamatan' => 'nullable|string|max:100',
            'address_city' => 'nullable|string|max:100',
            'address_province' => 'nullable|string|max:100',
            'address_postal_code' => 'nullable|string|max:5',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'blood_type' => 'nullable|in:A,B,AB,O',
            'religion' => 'nullable|in:islam,christian,catholic,hindu,buddhist,confucian',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'insurance_provider' => 'nullable|string|max:100',
            'insurance_policy_number' => 'nullable|string|max:100',
            'insurance_valid_until' => 'nullable|date',
            'known_allergies' => 'nullable|string',
            'chronic_diseases' => 'nullable|string',
            'current_medications' => 'nullable|string',
            'status' => 'required|in:active,inactive,deceased',
        ]);

        // Convert comma-separated strings to arrays for JSON columns
        if (isset($validated['known_allergies']) && ! empty($validated['known_allergies'])) {
            $validated['known_allergies'] = array_map('trim', explode(',', $validated['known_allergies']));
        }
        if (isset($validated['chronic_diseases']) && ! empty($validated['chronic_diseases'])) {
            $validated['chronic_diseases'] = array_map('trim', explode(',', $validated['chronic_diseases']));
        }
        if (isset($validated['current_medications']) && ! empty($validated['current_medications'])) {
            $validated['current_medications'] = array_map('trim', explode(',', $validated['current_medications']));
        }

        $patient->update($validated);

        // Clear cache
        DashboardCacheService::clearStats("stats:patients:{$tenantId}");

        return redirect()->route('healthcare.patients.show', $patient)
            ->with('success', 'Data pasien berhasil diperbarui');
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient)
    {
        $tenantId = auth()->user()->tenant_id;

        // Check if patient has active records
        if ($patient->visits()->exists() || $patient->medicalRecords()->exists()) {
            return back()->with('error', 'Cannot delete patient with existing medical records');
        }

        $patient->delete();

        // Clear cache
        DashboardCacheService::clearStats("stats:patients:{$tenantId}");

        return redirect()->route('healthcare.patients.index')
            ->with('success', 'Patient deleted successfully');
    }

    /**
     * Search patients.
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $patients = Patient::where(function ($q) use ($query) {
            $q->where('patient_number', 'like', "%{$query}%")
                ->orWhere('full_name', 'like', "%{$query}%")
                ->orWhere('nik', 'like', "%{$query}%")
                ->orWhere('phone', 'like', "%{$query}%");
        })
            ->limit(20)
            ->get(['id', 'patient_number', 'full_name', 'date_of_birth', 'phone']);

        return response()->json($patients);
    }

    /**
     * Get patient medical records.
     */
    public function medicalRecords(Patient $patient)
    {
        $records = $patient->medicalRecords()
            ->with('doctor')
            ->latest()
            ->paginate(20);

        return view('healthcare.patients.medical-records', compact('patient', 'records'));
    }

    /**
     * Get patient visits.
     */
    public function visits(Patient $patient)
    {
        $visits = $patient->visits()
            ->with(['doctor', 'department'])
            ->latest()
            ->paginate(20);

        return view('healthcare.patients.visits', compact('patient', 'visits'));
    }

    /**
     * Get patient appointments.
     */
    public function appointments(Patient $patient)
    {
        $appointments = $patient->appointments()
            ->with('doctor')
            ->latest()
            ->paginate(20);

        return view('healthcare.patients.appointments', compact('patient', 'appointments'));
    }

    /**
     * Get patient prescriptions.
     */
    public function prescriptions(Patient $patient)
    {
        $prescriptions = $patient->prescriptions()
            ->latest()
            ->paginate(20);

        return view('healthcare.patients.prescriptions', compact('patient', 'prescriptions'));
    }

    /**
     * Get patient lab results.
     */
    public function labResults(Patient $patient)
    {
        $labResults = LabResult::where('patient_id', $patient->id)
            ->with(['labOrder', 'doctor'])
            ->latest()
            ->paginate(20);

        return view('healthcare.patients.lab-results', compact('patient', 'labResults'));
    }

    /**
     * Get patient timeline.
     */
    public function timeline(Patient $patient)
    {
        $timeline = collect();

        // Add visits
        $patient->visits()->get()->each(function ($visit) use ($timeline) {
            $timeline->push([
                'date' => $visit->visit_date,
                'type' => 'visit',
                'title' => 'Patient Visit',
                'description' => $visit->visit_type . ' - ' . $visit->chief_complaint,
                'icon' => 'stethoscope',
            ]);
        });

        // Add appointments
        $patient->appointments()->get()->each(function ($appointment) use ($timeline) {
            $timeline->push([
                'date' => $appointment->appointment_date,
                'type' => 'appointment',
                'title' => 'Appointment',
                'description' => $appointment->reason,
                'icon' => 'calendar',
            ]);
        });

        // Add lab orders
        $patient->labOrders()->get()->each(function ($labOrder) use ($timeline) {
            $timeline->push([
                'date' => $labOrder->created_at,
                'type' => 'lab',
                'title' => 'Lab Order',
                'description' => $labOrder->lab_test?->test_name,
                'icon' => 'flask',
            ]);
        });

        $timeline = $timeline->sortByDesc('date')->values();

        return view('healthcare.patients.timeline', compact('patient', 'timeline'));
    }

    /**
     * Scan patient QR code.
     */
    public function scanQR($qrCode)
    {
        $patient = Patient::where('qr_code', $qrCode)->first();

        if (! $patient) {
            return redirect()->route('healthcare.patients.index')
                ->with('error', 'Patient not found');
        }

        return redirect()->route('healthcare.patients.show', $patient);
    }

    /**
     * Generate QR Code for patient.
     */
    public function generateQR(Patient $patient)
    {
        $qrCode = QrCode::size(300)
            ->generate($patient->patient_number);

        return response()->view('healthcare.patients.qr', compact('patient', 'qrCode'));
    }
}
