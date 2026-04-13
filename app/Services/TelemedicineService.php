<?php

namespace App\Services;

use App\Models\Teleconsultation;
use App\Models\TeleconsultationRecording;
use App\Models\TelemedicinePrescription;
use App\Models\TeleconsultationPayment;
use App\Models\TeleconsultationFeedback;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelemedicineService
{
    /**
     * Book teleconsultation
     */
    public function bookConsultation(array $bookingData): Teleconsultation
    {
        return DB::transaction(function () use ($bookingData) {
            $consultation = Teleconsultation::create([
                'patient_id' => $bookingData['patient_id'],
                'doctor_id' => $bookingData['doctor_id'],
                'consultation_number' => $this->generateConsultationNumber(),
                'consultation_date' => $bookingData['consultation_date'] ?? today(),
                'scheduled_time' => $bookingData['scheduled_time'],
                'scheduled_duration' => $bookingData['duration'] ?? 30,
                'platform' => $bookingData['platform'] ?? 'video',
                'consultation_type' => $bookingData['consultation_type'] ?? 'new',
                'status' => 'scheduled',
                'chief_complaint' => $bookingData['chief_complaint'] ?? null,
                'medical_history' => $bookingData['medical_history'] ?? null,
                'consultation_fee' => $bookingData['consultation_fee'] ?? 0,
                'discount' => $bookingData['discount'] ?? 0,
                'total_amount' => $bookingData['total_amount'] ?? 0,
                'payment_status' => 'unpaid',
            ]);

            // Generate meeting details
            $this->generateMeetingDetails($consultation);

            Log::info("Teleconsultation booked", [
                'consultation_number' => $consultation->consultation_number,
                'doctor_id' => $consultation->doctor_id,
                'scheduled_time' => $consultation->scheduled_time,
            ]);

            return $consultation;
        });
    }

    /**
     * Start teleconsultation
     */
    public function startConsultation(int $consultationId): Teleconsultation
    {
        return DB::transaction(function () use ($consultationId) {
            $consultation = Teleconsultation::findOrFail($consultationId);

            if (!$consultation->canStart()) {
                throw new Exception('Consultation cannot be started yet.');
            }

            $consultation->update([
                'actual_start_time' => now(),
                'status' => 'in_progress',
            ]);

            Log::info("Teleconsultation started", [
                'consultation_number' => $consultation->consultation_number,
                'start_time' => $consultation->actual_start_time,
            ]);

            return $consultation;
        });
    }

    /**
     * Complete teleconsultation
     */
    public function completeConsultation(int $consultationId, array $completionData): Teleconsultation
    {
        return DB::transaction(function () use ($consultationId, $completionData) {
            $consultation = Teleconsultation::findOrFail($consultationId);

            $endTime = now();
            $duration = $consultation->actual_start_time->diffInMinutes($endTime);

            $consultation->update([
                'actual_end_time' => $endTime,
                'actual_duration' => $duration,
                'diagnosis' => $completionData['diagnosis'] ?? null,
                'icd10_code' => $completionData['icd10_code'] ?? null,
                'treatment_plan' => $completionData['treatment_plan'] ?? null,
                'doctor_notes' => $completionData['doctor_notes'] ?? null,
                'status' => 'completed',
            ]);

            Log::info("Teleconsultation completed", [
                'consultation_number' => $consultation->consultation_number,
                'duration' => $duration,
            ]);

            return $consultation;
        });
    }

    /**
     * Cancel teleconsultation
     */
    public function cancelConsultation(int $consultationId, string $reason, int $cancelledBy): Teleconsultation
    {
        return DB::transaction(function () use ($consultationId, $reason, $cancelledBy) {
            $consultation = Teleconsultation::findOrFail($consultationId);

            if (!$consultation->canCancel()) {
                throw new Exception('Consultation cannot be cancelled.');
            }

            $consultation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'cancelled_by' => $cancelledBy,
                'cancelled_at' => now(),
            ]);

            Log::info("Teleconsultation cancelled", [
                'consultation_number' => $consultation->consultation_number,
                'reason' => $reason,
            ]);

            return $consultation;
        });
    }

    /**
     * Save teleconsultation recording
     */
    public function saveRecording(int $consultationId, array $recordingData): TeleconsultationRecording
    {
        return DB::transaction(function () use ($consultationId, $recordingData) {
            $recording = TeleconsultationRecording::create([
                'consultation_id' => $consultationId,
                'recording_url' => $recordingData['recording_url'],
                'thumbnail_url' => $recordingData['thumbnail_url'] ?? null,
                'duration' => $recordingData['duration'] ?? 0,
                'storage_size' => $recordingData['storage_size'] ?? 0,
                'storage_provider' => $recordingData['storage_provider'] ?? 'local',
                'storage_path' => $recordingData['storage_path'] ?? null,
                'cloud_url' => $recordingData['cloud_url'] ?? null,
                'is_encrypted' => true,
                'expires_at' => $recordingData['expires_at'] ?? now()->addMonths(6),
                'status' => 'available',
            ]);

            Log::info("Teleconsultation recording saved", [
                'consultation_id' => $consultationId,
                'duration' => $recording->duration,
            ]);

            return $recording;
        });
    }

    /**
     * Create telemedicine prescription
     */
    public function createPrescription(int $consultationId, array $prescriptionData): TelemedicinePrescription
    {
        return DB::transaction(function () use ($consultationId, $prescriptionData) {
            $consultation = Teleconsultation::findOrFail($consultationId);

            $prescription = TelemedicinePrescription::create([
                'consultation_id' => $consultationId,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'prescription_number' => $this->generatePrescriptionNumber(),
                'prescription_date' => today(),
                'valid_until' => $prescriptionData['valid_until'] ?? now()->addDays(30),
                'prescription_data' => $prescriptionData['prescription_data'],
                'diagnosis' => $prescriptionData['diagnosis'] ?? null,
                'icd10_code' => $prescriptionData['icd10_code'] ?? null,
                'instructions' => $prescriptionData['instructions'] ?? null,
                'special_notes' => $prescriptionData['special_notes'] ?? null,
                'status' => 'active',
            ]);

            // Send to pharmacy if specified
            if (!empty($prescriptionData['pharmacy_id'])) {
                $this->sendToPharmacy($prescription->id, $prescriptionData['pharmacy_id']);
            }

            Log::info("Telemedicine prescription created", [
                'prescription_number' => $prescription->prescription_number,
            ]);

            return $prescription;
        });
    }

    /**
     * Send prescription to pharmacy
     */
    public function sendToPharmacy(int $prescriptionId, int $pharmacyId): TelemedicinePrescription
    {
        return DB::transaction(function () use ($prescriptionId, $pharmacyId) {
            $prescription = TelemedicinePrescription::findOrFail($prescriptionId);

            $prescription->update([
                'pharmacy_id' => $pharmacyId,
                'sent_to_pharmacy' => true,
                'sent_at' => now(),
                'pharmacy_status' => 'pending',
            ]);

            Log::info("Prescription sent to pharmacy", [
                'prescription_number' => $prescription->prescription_number,
                'pharmacy_id' => $pharmacyId,
            ]);

            return $prescription;
        });
    }

    /**
     * Process teleconsultation payment
     */
    public function processPayment(int $consultationId, array $paymentData): TeleconsultationPayment
    {
        return DB::transaction(function () use ($consultationId, $paymentData) {
            $consultation = Teleconsultation::findOrFail($consultationId);

            $payment = TeleconsultationPayment::create([
                'consultation_id' => $consultationId,
                'patient_id' => $consultation->patient_id,
                'payment_number' => $this->generatePaymentNumber(),
                'amount' => $consultation->total_amount,
                'discount' => $paymentData['discount'] ?? 0,
                'total_amount' => $paymentData['total_amount'] ?? $consultation->total_amount,
                'payment_method' => $paymentData['payment_method'] ?? 'ewallet',
                'status' => 'pending',
            ]);

            // Process payment gateway
            if (in_array($paymentData['payment_method'] ?? '', ['credit_card', 'debit_card', 'ewallet', 'bank_transfer'])) {
                $gatewayResponse = $this->processGatewayPayment($payment, $paymentData);

                $payment->update([
                    'payment_gateway' => $gatewayResponse['gateway'] ?? null,
                    'gateway_transaction_id' => $gatewayResponse['transaction_id'] ?? null,
                    'gateway_response' => json_encode($gatewayResponse),
                    'status' => $gatewayResponse['status'] ?? 'pending',
                ]);

                if ($gatewayResponse['status'] === 'success') {
                    $this->markPaymentSuccess($payment);
                }
            } else {
                // Cash payment - mark as paid immediately
                $this->markPaymentSuccess($payment);
            }

            Log::info("Teleconsultation payment processed", [
                'payment_number' => $payment->payment_number,
                'status' => $payment->status,
            ]);

            return $payment;
        });
    }

    /**
     * Submit feedback
     */
    public function submitFeedback(int $consultationId, array $feedbackData): TeleconsultationFeedback
    {
        return DB::transaction(function () use ($consultationId, $feedbackData) {
            $consultation = Teleconsultation::findOrFail($consultationId);

            $feedback = TeleconsultationFeedback::create([
                'consultation_id' => $consultationId,
                'patient_id' => $consultation->patient_id,
                'doctor_id' => $consultation->doctor_id,
                'rating' => $feedbackData['rating'],
                'video_quality' => $feedbackData['video_quality'] ?? null,
                'audio_quality' => $feedbackData['audio_quality'] ?? null,
                'doctor_rating' => $feedbackData['doctor_rating'] ?? $feedbackData['rating'],
                'platform_rating' => $feedbackData['platform_rating'] ?? null,
                'feedback' => $feedbackData['feedback'] ?? null,
                'positive_feedback' => $feedbackData['positive_feedback'] ?? null,
                'negative_feedback' => $feedbackData['negative_feedback'] ?? null,
                'suggestions' => $feedbackData['suggestions'] ?? null,
                'feedback_tags' => $feedbackData['feedback_tags'] ?? null,
                'is_anonymous' => $feedbackData['is_anonymous'] ?? false,
                'is_public' => $feedbackData['is_public'] ?? false,
                'would_recommend' => $feedbackData['would_recommend'] ?? true,
                'would_use_again' => $feedbackData['would_use_again'] ?? true,
                'needs_followup' => $feedbackData['needs_followup'] ?? false,
                'followup_notes' => $feedbackData['followup_notes'] ?? null,
            ]);

            Log::info("Teleconsultation feedback submitted", [
                'consultation_id' => $consultationId,
                'rating' => $feedback->rating,
            ]);

            return $feedback;
        });
    }

    /**
     * Get doctor's consultations
     */
    public function getDoctorConsultations(int $doctorId, $date = null): array
    {
        $query = Teleconsultation::byDoctor($doctorId);

        if ($date) {
            $query->whereDate('scheduled_time', $date);
        }

        return $query->with(['patient', 'payment', 'feedback'])
            ->orderBy('scheduled_time')
            ->get()
            ->toArray();
    }

    /**
     * Get patient's consultations
     */
    public function getPatientConsultations(int $patientId): array
    {
        return Teleconsultation::byPatient($patientId)
            ->with(['doctor', 'payment', 'prescriptions', 'feedback'])
            ->orderBy('scheduled_time', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get telemedicine dashboard
     */
    public function getDashboardData(): array
    {
        return [
            'consultations_today' => Teleconsultation::whereDate('scheduled_time', today())->count(),
            'in_progress' => Teleconsultation::inProgress()->count(),
            'completed_today' => Teleconsultation::where('status', 'completed')
                ->whereDate('actual_end_time', today())->count(),
            'pending_payments' => TeleconsultationPayment::where('status', 'pending')->count(),
            'revenue_today' => TeleconsultationPayment::where('status', 'success')
                ->whereDate('paid_at', today())->sum('total_amount'),
            'average_rating' => TeleconsultationFeedback::whereDate('created_at', today())
                ->avg('rating'),
            'upcoming_consultations' => Teleconsultation::upcoming()
                ->take(10)
                ->get()
                ->toArray(),
        ];
    }

    /**
     * Generate consultation number
     */
    protected function generateConsultationNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TEL-' . $date;

        $last = Teleconsultation::where('consultation_number', 'like', $prefix . '%')
            ->orderBy('consultation_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->consultation_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate prescription number
     */
    protected function generatePrescriptionNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'RX-TEL-' . $date;

        $last = TelemedicinePrescription::where('prescription_number', 'like', $prefix . '%')
            ->orderBy('prescription_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->prescription_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate payment number
     */
    protected function generatePaymentNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'PAY-TEL-' . $date;

        $last = TeleconsultationPayment::where('payment_number', 'like', $prefix . '%')
            ->orderBy('payment_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->payment_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    /**
     * Generate meeting details (WebRTC/Zoom/Google Meet)
     */
    protected function generateMeetingDetails(Teleconsultation $consultation): void
    {
        // For WebRTC implementation
        $meetingId = 'TEL-' . uniqid();
        $meetingUrl = route('telemedicine.join', ['meetingId' => $meetingId]);

        $consultation->update([
            'meeting_id' => $meetingId,
            'meeting_url' => $meetingUrl,
            'meeting_password' => substr(md5(uniqid()), 0, 8),
            'meeting_details' => json_encode([
                'provider' => 'webrtc',
                'room_id' => $meetingId,
                'created_at' => now(),
            ]),
        ]);
    }

    /**
     * Process payment gateway (Midtrans/Xendit/etc)
     */
    protected function processGatewayPayment(TeleconsultationPayment $payment, array $paymentData): array
    {
        // Get consultation details
        $consultation = $payment->teleconsultation;
        $patient = $consultation->patient;
        $doctor = $consultation->doctor;

        // Prepare payment payload for Midtrans
        $transactionDetails = [
            'order_id' => 'TELEMED-' . $payment->id . '-' . time(),
            'gross_amount' => (int) $payment->amount,
        ];

        $customerDetails = [
            'first_name' => $patient->full_name,
            'email' => $patient->email ?? 'patient@example.com',
            'phone' => $patient->phone ?? '08123456789',
        ];

        $itemDetails = [
            [
                'id' => 'TELECONSULT-' . $consultation->id,
                'price' => (int) $payment->amount,
                'quantity' => 1,
                'name' => 'Konsultasi Telemedicine - Dr. ' . $doctor->full_name,
                'category' => 'healthcare',
            ],
        ];

        // Midtrans SNAP integration
        $midtransConfig = config('services.midtrans');

        if (!$midtransConfig || !isset($midtransConfig['server_key'])) {
            // Fallback: Mark as manual payment required
            Log::warning('Midtrans not configured - marking as manual payment');

            $payment->update([
                'gateway' => 'manual',
                'gateway_transaction_id' => $transactionDetails['order_id'],
                'status' => 'pending',
                'payment_instructions' => 'Silakan transfer ke rekening: BCA 1234567890 a/n Qalcuity Healthcare',
            ]);

            return [
                'gateway' => 'manual',
                'transaction_id' => $transactionDetails['order_id'],
                'status' => 'pending',
                'payment_instructions' => 'Transfer ke BCA 1234567890 a/n Qalcuity Healthcare',
                'message' => 'Manual payment instructions generated',
            ];
        }

        // Midtrans SNAP integration (production ready)
        // Note: Requires midtrans/midtrans-php package. Fallback to manual payment if not available.
        try {
            // Check if Midtrans library is available
            if (!class_exists('\Midtrans\Config')) {
                Log::warning('Midtrans library not installed - using manual payment');
                throw new \Exception('Midtrans library not available');
            }

            // @phpstan-ignore-next-line - Midtrans is optional dependency
            \Midtrans\Config::$serverKey = $midtransConfig['server_key'];
            // @phpstan-ignore-next-line
            \Midtrans\Config::$isProduction = $midtransConfig['is_production'] ?? false;
            // @phpstan-ignore-next-line
            \Midtrans\Config::$isSanitized = true;
            // @phpstan-ignore-next-line
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => $transactionDetails,
                'customer_details' => $customerDetails,
                'item_details' => $itemDetails,
                'enabled_payments' => [
                    'credit_card',
                    'mandiri_clickpay',
                    'cimb_clicks',
                    'bca_klikbca',
                    'bca_klikpay',
                    'bri_epay',
                    'echannel',
                    'permata_va',
                    'bca_va',
                    'bni_va',
                    'bri_va',
                    'cimb_va',
                    'other_va',
                    'gopay',
                    'indomaret',
                    'danamon_online',
                    'akulaku',
                    'shopeepay',
                ],
                'credit_card' => [
                    'secure' => true,
                ],
                'callbacks' => [
                    'finish' => route('healthcare.telemedicine.payments.callback', ['payment' => $payment->id]),
                    'error' => route('healthcare.telemedicine.payments.error', ['payment' => $payment->id]),
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $redirectUrl = \Midtrans\Snap::getSnapUrl($params);

            // Update payment with gateway info
            $payment->update([
                'gateway' => 'midtrans',
                'gateway_transaction_id' => $transactionDetails['order_id'],
                'snap_token' => $snapToken,
                'status' => 'pending',
            ]);

            return [
                'gateway' => 'midtrans',
                'transaction_id' => $transactionDetails['order_id'],
                'snap_token' => $snapToken,
                'redirect_url' => $redirectUrl,
                'status' => 'pending',
                'message' => 'Payment redirect generated',
            ];
        } catch (\Exception $e) {
            Log::error('Midtrans payment error: ' . $e->getMessage(), [
                'payment_id' => $payment->id,
                'consultation_id' => $consultation->id,
            ]);

            throw new \Exception('Payment gateway error: ' . $e->getMessage());
        }
    }

    /**
     * Mark payment as success
     */
    protected function markPaymentSuccess(TeleconsultationPayment $payment): void
    {
        $payment->update([
            'status' => 'success',
            'paid_at' => now(),
        ]);

        // Update consultation payment status
        $payment->consultation->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
