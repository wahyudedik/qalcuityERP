<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\TelemedicineConsultation;
use App\Services\Healthcare\TelemedicinePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelemedicineController extends Controller
{
    protected $paymentService;

    public function __construct(TelemedicinePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Display telemedicine dashboard.
     */
    public function index()
    {
        $tenantId = auth()->user()->tenant_id;

        $statistics = [
            'today_consultations' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->whereDate('consultation_date', today())
                ->count(),
            'scheduled' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'scheduled')
                ->count(),
            'completed' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->count(),
            'cancelled' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'cancelled')
                ->count(),
            'total_patients' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->distinct('patient_id')
                ->count('patient_id'),
        ];

        return view('healthcare.telemedicine.index', compact('statistics'));
    }

    /**
     * Book telemedicine consultation.
     */
    public function book(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'consultation_date' => 'required|date|after:today',
            'consultation_time' => 'required',
            'reason' => 'required|string',
            'consultation_type' => 'required|in:video,audio,chat',
            'notes' => 'nullable|string',
        ]);

        // Check doctor availability
        // Create teleconsultation record
        // Send notification to doctor and patient

        return back()->with('success', 'Telemedicine consultation booked successfully');
    }

    /**
     * Display consultations list.
     */
    public function consultations(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;

        $query = TelemedicineConsultation::with(['patient', 'doctor'])
            ->where('tenant_id', $tenantId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('consultation_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('consultation_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('consultation_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('patient', function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%");
            })->orWhereHas('doctor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $consultations = $query->latest('consultation_date')
            ->paginate(20)
            ->withQueryString();

        // Stats - with tenant isolation
        $statistics = [
            'total_consultations' => TelemedicineConsultation::where('tenant_id', $tenantId)->count(),
            'scheduled' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'scheduled')
                ->count(),
            'in_progress' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'in_progress')
                ->count(),
            'completed' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->count(),
            'completed_today' => TelemedicineConsultation::where('tenant_id', $tenantId)
                ->where('status', 'completed')
                ->whereDate('completed_at', today())
                ->count(),
        ];

        return view('healthcare.telemedicine.consultations', compact('consultations', 'statistics'));
    }

    /**
     * Display consultation details.
     */
    public function showConsultation($id)
    {
        // Will load Teleconsultation with relations
        $consultation = [];

        return view('healthcare.telemedicine.consultation-show', compact('consultation'));
    }

    /**
     * Join telemedicine consultation.
     */
    public function join($id)
    {
        // Generate WebRTC connection details
        // Return view with video call interface

        return view('healthcare.telemedicine.join', compact('id'));
    }

    /**
     * Start telemedicine consultation.
     */
    public function start($id)
    {
        // Update consultation status to 'in_progress'
        // Record start time

        return back()->with('success', 'Consultation started');
    }

    /**
     * End telemedicine consultation.
     */
    public function end($id, Request $request)
    {
        $validated = $request->validate([
            'summary' => 'required|string',
            'diagnosis' => 'nullable|string',
            'follow_up_required' => 'boolean',
            'follow_up_date' => 'nullable|date',
        ]);

        // Update consultation status to 'completed'
        // Record end time and duration
        // Save summary and diagnosis

        return back()->with('success', 'Consultation ended successfully');
    }

    /**
     * Add prescription to consultation.
     */
    public function addPrescription($id, Request $request)
    {
        $validated = $request->validate([
            'medications' => 'required|array',
            'medications.*.name' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.duration' => 'required|string',
            'instructions' => 'nullable|string',
        ]);

        // Create telemedicine prescription record

        return back()->with('success', 'Prescription added successfully');
    }

    /**
     * Add feedback to consultation.
     */
    public function addFeedback($id, Request $request)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comments' => 'nullable|string',
            'doctor_rating' => 'nullable|integer|min:1|max:5',
            'video_quality' => 'nullable|integer|min:1|max:5',
            'would_recommend' => 'boolean',
        ]);

        // Create teleconsultation feedback record

        return back()->with('success', 'Feedback submitted successfully');
    }

    /**
     * Display telemedicine dashboard.
     */
    public function dashboard()
    {
        $statistics = [
            'today_consultations' => 0,
            'scheduled' => 0,
            'completed' => 0,
            'avg_duration' => 0,
            'avg_rating' => 0,
        ];

        return view('healthcare.telemedicine.dashboard', compact('statistics'));
    }

    /**
     * Show payment page for consultation
     */
    public function showPayment($id)
    {
        $consultation = TelemedicineConsultation::with(['patient', 'doctor'])->findOrFail($id);

        // Check if consultation requires payment
        if ($consultation->payment_status === 'paid') {
            return back()->with('info', 'This consultation has already been paid.');
        }

        // Get available payment methods
        $paymentMethods = [
            'qris' => ['name' => 'QRIS', 'icon' => '📱', 'processing_time' => 'Instant'],
            'credit_card' => ['name' => 'Credit Card', 'icon' => '💳', 'processing_time' => 'Instant'],
            'debit_card' => ['name' => 'Debit Card', 'icon' => '💳', 'processing_time' => 'Instant'],
            'va_bca' => ['name' => 'BCA Virtual Account', 'icon' => '🏦', 'processing_time' => 'Instant'],
            'va_bni' => ['name' => 'BNI Virtual Account', 'icon' => '🏦', 'processing_time' => 'Instant'],
            'va_bri' => ['name' => 'BRI Virtual Account', 'icon' => '🏦', 'processing_time' => 'Instant'],
            'va_mandiri' => ['name' => 'Mandiri Virtual Account', 'icon' => '🏦', 'processing_time' => 'Instant'],
            'ewallet_gopay' => ['name' => 'GoPay', 'icon' => '🟢', 'processing_time' => 'Instant'],
            'ewallet_ovo' => ['name' => 'OVO', 'icon' => '🟣', 'processing_time' => 'Instant'],
            'ewallet_dana' => ['name' => 'DANA', 'icon' => '🔵', 'processing_time' => 'Instant'],
            'ewallet_shopeepay' => ['name' => 'ShopeePay', 'icon' => '🟠', 'processing_time' => 'Instant'],
        ];

        return view('healthcare.telemedicine.payment', compact('consultation', 'paymentMethods'));
    }

    /**
     * Process payment for consultation
     */
    public function processPayment(Request $request, $id)
    {
        $consultation = TelemedicineConsultation::with(['patient', 'doctor'])->findOrFail($id);

        $validated = $request->validate([
            'payment_method' => 'required|string',
            'payment_provider' => 'nullable|string|in:midtrans,xendit,duitku,tripay',
        ]);

        // Check if already paid
        if ($consultation->payment_status === 'paid') {
            return back()->with('error', 'This consultation has already been paid.');
        }

        // Create payment
        $result = $this->paymentService->createPayment(
            $consultation,
            $validated['payment_method'],
            $validated['payment_provider']
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment created successfully',
                'data' => $result,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'],
        ], 400);
    }

    /**
     * Payment finish callback
     */
    public function paymentFinish(Request $request)
    {
        return redirect()->route('healthcare.telemedicine.consultations')
            ->with('success', 'Payment completed successfully!');
    }

    /**
     * Payment pending callback
     */
    public function paymentPending(Request $request)
    {
        return redirect()->route('healthcare.telemedicine.consultations')
            ->with('info', 'Payment is still pending. Please complete the payment.');
    }

    /**
     * Payment error callback
     */
    public function paymentError(Request $request)
    {
        return redirect()->route('healthcare.telemedicine.consultations')
            ->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Payment gateway webhook callback
     */
    public function paymentCallback(Request $request, string $provider)
    {
        $payload = $request->all();

        Log::info("Payment callback received", [
            'provider' => $provider,
            'payload' => $payload,
        ]);

        $result = $this->paymentService->handlePaymentCallback($provider, $payload);

        if ($result['success']) {
            return response('OK', 200);
        }

        return response('FAILED', 400);
    }

    /**
     * Process refund for consultation
     */
    public function processRefund(Request $request, $id)
    {
        $consultation = TelemedicineConsultation::findOrFail($id);
        $paymentTransaction = \App\Models\PaymentTransaction::where('telemedicine_consultation_id', $consultation->id)
            ->where('status', 'paid')
            ->first();

        if (!$paymentTransaction) {
            return back()->with('error', 'No payment found for this consultation.');
        }

        $validated = $request->validate([
            'refund_reason' => 'required|string|max:500',
        ]);

        $result = $this->paymentService->processRefund($paymentTransaction, $validated['refund_reason']);

        if ($result['success']) {
            return back()->with('success', 'Refund processed successfully.');
        }

        return back()->with('error', 'Refund failed: ' . $result['error']);
    }
}
