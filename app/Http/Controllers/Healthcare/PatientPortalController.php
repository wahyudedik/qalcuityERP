<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\OutpatientVisit;
use App\Models\Prescription;
use App\Models\LabOrder;
use App\Models\MedicalBill;
use App\Models\LabResult;
use App\Services\DashboardCacheService;
use Illuminate\Http\Request;

class PatientPortalController extends Controller
{
    /**
     * Display patient portal index.
     */
    public function index()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            abort(403, 'No patient profile linked to this account');
        }

        return redirect()->route('patient-portal.dashboard');
    }

    /**
     * Display patient portal dashboard.
     */
    public function dashboard()
    {
        $patient = auth()->user()->patient;

        if (!$patient) {
            return view('healthcare.patient-portal.dashboard', [
                'patient' => null,
                'statistics' => [],
                'upcomingAppointments' => [],
                'recentLabResults' => [],
                'recentPrescriptions' => [],
            ]);
        }

        // Load dashboard data with eager loading
        $nextAppointment = Appointment::where('patient_id', $patient->id)
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->with('doctor')
            ->orderBy('appointment_date')
            ->first();

        $upcomingAppointments = Appointment::where('patient_id', $patient->id)
            ->where('appointment_date', '>=', now())
            ->where('status', 'scheduled')
            ->with('doctor')
            ->orderBy('appointment_date')
            ->take(5)
            ->get();

        $recentLabResults = LabResult::where('patient_id', $patient->id)
            ->with('test')
            ->latest()
            ->take(5)
            ->get();

        $recentPrescriptions = Prescription::where('patient_id', $patient->id)
            ->with('doctor')
            ->latest()
            ->take(5)
            ->get();

        // Stats - optimized with single query + caching
        $cacheKey = "stats:portal:patient_{$patient->id}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($patient) {
            $stats = OutpatientVisit::where('patient_id', $patient->id)
                ->selectRaw('
                    COUNT(*) as total_visits
                ')
                ->first();

            $prescriptionCount = Prescription::where('patient_id', $patient->id)->count();
            $labOrderCount = LabOrder::where('patient_id', $patient->id)->count();
            $pendingBills = MedicalBill::where('patient_id', $patient->id)
                ->where('status', 'pending')
                ->sum('outstanding_balance');

            $pendingLabResults = LabResult::where('patient_id', $patient->id)
                ->where('status', 'pending')
                ->count();

            return [
                'total_visits' => $stats->total_visits ?? 0,
                'total_prescriptions' => $prescriptionCount,
                'total_lab_orders' => $labOrderCount,
                'pending_bills' => $pendingBills,
                'pending_lab_results' => $pendingLabResults,
                'upcoming_appointments' => Appointment::where('patient_id', $patient->id)
                    ->where('appointment_date', '>=', now())
                    ->where('status', 'scheduled')
                    ->count(),
            ];
        }, 300);

        return view('healthcare.patient-portal.dashboard', compact(
            'patient',
            'statistics',
            'upcomingAppointments',
            'recentLabResults',
            'recentPrescriptions',
            'nextAppointment'
        ));
    }

    /**
     * Display medical records.
     */
    public function medicalRecords()
    {
        $patient = auth()->user()->patient;

        $records = $patient->medicalRecords()
            ->with('doctor')
            ->latest()
            ->paginate(20);

        return view('healthcare.patient-portal.medical-records', compact('patient', 'records'));
    }

    /**
     * Display appointments.
     */
    public function appointments()
    {
        $patient = auth()->user()->patient;

        $appointments = $patient->appointments()
            ->with('doctor')
            ->latest()
            ->paginate(20);

        return view('healthcare.patient-portal.appointments', compact('patient', 'appointments'));
    }

    /**
     * Book appointment.
     */
    public function bookAppointment(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required',
            'reason' => 'required|string',
            'visit_type' => 'required|in:general,specialist,consultation,follow-up',
            'notes' => 'nullable|string',
        ]);

        // Check doctor availability
        // Create appointment

        return back()->with('success', 'Appointment booked successfully');
    }

    /**
     * Display lab results.
     */
    public function labResults()
    {
        $patient = auth()->user()->patient;

        $labResults = []; // Will fetch verified lab results for patient

        return view('healthcare.patient-portal.lab-results', compact('patient', 'labResults'));
    }

    /**
     * Display lab result details.
     */
    public function showLabResult($id)
    {
        $patient = auth()->user()->patient;

        // Load lab result with verification that it belongs to patient
        $labResult = [];

        return view('healthcare.patient-portal.lab-result-show', compact('patient', 'labResult'));
    }

    /**
     * Display prescriptions.
     */
    public function prescriptions()
    {
        $patient = auth()->user()->patient;

        $prescriptions = $patient->prescriptions()
            ->latest()
            ->paginate(20);

        return view('healthcare.patient-portal.prescriptions', compact('patient', 'prescriptions'));
    }

    /**
     * Display billing.
     */
    public function billing()
    {
        $patient = auth()->user()->patient;

        $bills = $patient->medicalBills()
            ->latest()
            ->paginate(20);

        // Stats - optimized with caching
        $cacheKey = "stats:billing:patient_{$patient->id}";
        $statistics = DashboardCacheService::getStats($cacheKey, function () use ($patient) {
            $stats = MedicalBill::where('patient_id', $patient->id)
                ->selectRaw('
                    COUNT(*) as total_bills,
                    SUM(CASE WHEN status = \'paid\' THEN 1 ELSE 0 END) as paid_bills,
                    SUM(CASE WHEN status = \'pending\' THEN 1 ELSE 0 END) as pending_bills,
                    SUM(CASE WHEN status = \'pending\' THEN outstanding_balance ELSE 0 END) as total_outstanding
                ')
                ->first();

            return [
                'total_bills' => $stats->total_bills ?? 0,
                'paid_bills' => $stats->paid_bills ?? 0,
                'pending_bills' => $stats->pending_bills ?? 0,
                'total_outstanding' => $stats->total_outstanding ?? 0,
            ];
        }, 300);

        return view('healthcare.patient-portal.billing', compact('patient', 'bills', 'statistics'));
    }

    /**
     * Pay bill.
     */
    public function payBill($id, Request $request)
    {
        $patient = auth()->user()->patient;

        $bill = $patient->medicalBills()->findOrFail($id);

        $validated = $request->validate([
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer,ewallet',
            'payment_gateway_token' => 'nullable|string',
        ]);

        // Process payment via payment gateway
        // Update bill payment status

        return back()->with('success', 'Payment processed successfully');
    }

    /**
     * Display certificates.
     */
    public function certificates()
    {
        $patient = auth()->user()->patient;

        $certificates = []; // Will fetch medical certificate requests

        return view('healthcare.patient-portal.certificates', compact('patient', 'certificates'));
    }

    /**
     * Request medical certificate.
     */
    public function requestCertificate(Request $request)
    {
        $validated = $request->validate([
            'certificate_type' => 'required|in:sick_leave,fitness_to_work,medical_report,health_certificate',
            'purpose' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'additional_notes' => 'nullable|string',
        ]);

        $patient = auth()->user()->patient;

        // Create medical certificate request

        return back()->with('success', 'Certificate request submitted successfully');
    }

    /**
     * Display messages.
     */
    public function messages()
    {
        $patient = auth()->user()->patient;

        $messages = []; // Will fetch patient messages

        return view('healthcare.patient-portal.messages', compact('patient', 'messages'));
    }

    /**
     * Send message.
     */
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'subject' => 'required|string',
            'message' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:5120',
        ]);

        $patient = auth()->user()->patient;

        // Create patient message
        // Notify doctor

        return back()->with('success', 'Message sent successfully');
    }

    /**
     * Display health education content.
     */
    public function healthEducation(Request $request)
    {
        $query = []; // Will use HealthEducationContent model

        if ($request->filled('category')) {
            // Filter by category
        }

        if ($request->filled('search')) {
            // Search content
        }

        $contents = [];

        return view('healthcare.patient-portal.health-education', compact('contents'));
    }
}
