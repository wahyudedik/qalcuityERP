<?php

namespace App\Http\Controllers\Healthcare;

use App\Http\Controllers\Controller;
use App\Models\Teleconsultation;
use App\Services\Healthcare\TelemedicinePaymentService;
use App\Services\TelemedicineVideoService;
use App\Services\TelemedicineFeedbackService;
use App\Models\TelemedicineSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TelemedicineController extends Controller
{
    protected $paymentService;
    protected $videoService;
    protected $feedbackService;

    public function __construct(
        TelemedicinePaymentService $paymentService,
        TelemedicineVideoService $videoService,
        TelemedicineFeedbackService $feedbackService
    ) {
        $this->paymentService = $paymentService;
        $this->videoService = $videoService;
        $this->feedbackService = $feedbackService;
    }
    /**
     * Display telemedicine dashboard.
     */
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;

        $statistics = [
            'today_consultations' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->whereDate('scheduled_time', today())
                ->count(),
            'scheduled' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'scheduled')
                ->count(),
            'completed' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'completed')
                ->count(),
            'cancelled' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'cancelled')
                ->count(),
            'total_patients' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
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
        $tenantId = Auth::user()->tenant_id;

        $query = Teleconsultation::with(['patient', 'doctor'])
            ->whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });

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
            'total_consultations' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })->count(),
            'scheduled' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'scheduled')
                ->count(),
            'in_progress' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'in_progress')
                ->count(),
            'completed' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'completed')
                ->count(),
            'completed_today' => Teleconsultation::whereHas('patient', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            })
                ->where('status', 'completed')
                ->whereDate('updated_at', today())
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
        $consultation = Teleconsultation::with(['patient', 'doctor'])->findOrFail($id);

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
        $consultation = Teleconsultation::with(['patient', 'doctor'])->findOrFail($id);

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
        $consultation = Teleconsultation::findOrFail($id);
        $paymentTransaction = \App\Models\PaymentTransaction::where('teleconsultation_id', $consultation->id)
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

    // ========================================
    // VIDEO INTEGRATION METHODS (Jitsi Meet)
    // ========================================

    /**
     * Show video room for consultation.
     */
    public function videoRoom($id)
    {
        $consultation = Teleconsultation::with(['patient', 'doctor'])->findOrFail($id);

        // Check if user can join
        if (!$consultation->canJoin()) {
            return redirect()->route('healthcare.telemedicine.consultations')
                ->with('error', 'This consultation cannot be joined at this time.');
        }

        // Generate or get meeting details
        if (!$consultation->meeting_url) {
            $meetingData = $this->videoService->generateMeetingRoom($consultation);
        } else {
            $settings = TelemedicineSetting::getForTenant($consultation->patient?->tenant_id ?? 1);
            $meetingData = [
                'room_name' => $consultation->meeting_id,
                'meeting_url' => $consultation->meeting_url,
                'jitsi_domain' => $settings->getJitsiDomain(),
                'settings' => $settings,
            ];
        }

        return view('healthcare.telemedicine.video-room', [
            'consultation' => $consultation,
            'roomName' => $meetingData['room_name'],
            'jitsiServerUrl' => $meetingData['settings']->jitsi_server_url,
            'jitsiDomain' => $meetingData['jitsi_domain'],
            'settings' => $meetingData['settings'],
        ]);
    }

    /**
     * Generate JWT token for Jitsi (if using self-hosted with auth).
     */
    public function generateToken(Request $request, $id)
    {
        $consultation = Teleconsultation::findOrFail($id);
        $user = Auth::user();

        // Determine role
        $role = ($consultation->doctor_id == $user->id) ? 'moderator' : 'participant';
        $userId = $user->id;

        $token = $this->videoService->generateJWT(
            $consultation->meeting_id,
            $role,
            $userId
        );

        return response()->json([
            'token' => $token,
            'role' => $role,
        ]);
    }

    /**
     * Start recording for consultation.
     */
    public function startRecording($id)
    {
        $consultation = Teleconsultation::findOrFail($id);

        $success = $this->videoService->startRecording($consultation);

        if ($success) {
            return response()->json(['message' => 'Recording started']);
        }

        return response()->json(['message' => 'Failed to start recording'], 500);
    }

    /**
     * Stop recording for consultation.
     */
    public function stopRecording($id)
    {
        $consultation = Teleconsultation::findOrFail($id);

        $success = $this->videoService->stopRecording($consultation);

        if ($success) {
            return response()->json(['message' => 'Recording stopped']);
        }

        return response()->json(['message' => 'Failed to stop recording'], 500);
    }

    /**
     * Show feedback form for completed consultation.
     */
    public function showFeedback($id)
    {
        $consultation = Teleconsultation::with(['patient', 'doctor'])->findOrFail($id);

        // Check if feedback already exists
        if ($this->feedbackService->hasFeedback($consultation->id)) {
            return redirect()->route('healthcare.telemedicine.consultations')
                ->with('info', 'You have already submitted feedback for this consultation.');
        }

        return view('healthcare.telemedicine.feedback', compact('consultation'));
    }

    /**
     * Submit feedback for consultation.
     */
    public function submitFeedback($id, Request $request)
    {
        $consultation = Teleconsultation::findOrFail($id);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'video_quality' => 'nullable|integer|min:1|max:5',
            'audio_quality' => 'nullable|integer|min:1|max:5',
            'doctor_rating' => 'required|integer|min:1|max:5',
            'platform_rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
            'positive_feedback' => 'nullable|string|max:500',
            'negative_feedback' => 'nullable|string|max:500',
            'suggestions' => 'nullable|string|max:500',
            'would_recommend' => 'nullable|boolean',
            'would_use_again' => 'nullable|boolean',
            'needs_followup' => 'nullable|boolean',
            'followup_notes' => 'nullable|string|max:500',
        ]);

        try {
            $feedback = $this->feedbackService->submitFeedback([
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'rating' => $validated['rating'],
                'video_quality' => $validated['video_quality'] ?? null,
                'audio_quality' => $validated['audio_quality'] ?? null,
                'doctor_rating' => $validated['doctor_rating'],
                'platform_rating' => $validated['platform_rating'] ?? null,
                'feedback' => $validated['feedback'] ?? null,
                'positive_feedback' => $validated['positive_feedback'] ?? null,
                'negative_feedback' => $validated['negative_feedback'] ?? null,
                'suggestions' => $validated['suggestions'] ?? null,
                'would_recommend' => $validated['would_recommend'] ?? true,
                'would_use_again' => $validated['would_use_again'] ?? true,
                'needs_followup' => $validated['needs_followup'] ?? false,
                'followup_notes' => $validated['followup_notes'] ?? null,
            ]);

            return redirect()->route('healthcare.telemedicine.consultations')
                ->with('success', 'Thank you for your feedback!');
        } catch (\Exception $e) {
            Log::error('Failed to submit feedback', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to submit feedback. Please try again.');
        }
    }

    /**
     * Get feedback for consultation (API).
     */
    public function getFeedback($id)
    {
        $consultation = Teleconsultation::findOrFail($id);
        $feedback = $this->feedbackService->getConsultationFeedback($consultation->id);

        if (!$feedback) {
            return response()->json(['message' => 'No feedback found'], 404);
        }

        return response()->json($feedback);
    }
}

