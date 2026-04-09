<?php

namespace App\Services;

use App\Models\PatientMessage;
use App\Models\MessageAttachment;
use App\Models\MedicalCertificateRequest;
use App\Models\HealthEducationContent;
use App\Models\NotificationLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;

class PatientPortalService
{
    /**
     * Get patient dashboard summary
     */
    public function getPatientDashboard($patientId): array
    {
        return [
            'upcoming_appointments' => $this->getUpcomingAppointments($patientId),
            'recent_lab_results' => $this->getRecentLabResults($patientId),
            'active_prescriptions' => $this->getActivePrescriptions($patientId),
            'pending_bills' => $this->getPendingBills($patientId),
            'unread_messages' => $this->getUnreadMessageCount($patientId),
            'certificate_requests' => $this->getCertificateRequests($patientId),
        ];
    }

    /**
     * Get patient medical records
     */
    public function getMedicalRecords($patientId, array $filters = []): array
    {
        return [
            'visits' => $this->getVisitHistory($patientId, $filters),
            'diagnoses' => $this->getDiagnoses($patientId),
            'lab_results' => $this->getLabResults($patientId, $filters),
            'radiology_results' => $this->getRadiologyResults($patientId, $filters),
            'prescriptions' => $this->getPrescriptions($patientId, $filters),
            'allergies' => $this->getAllergies($patientId),
            'vaccinations' => $this->getVaccinations($patientId),
        ];
    }

    /**
     * Book appointment online
     */
    public function bookAppointment(array $bookingData): array
    {
        return DB::transaction(function () use ($bookingData) {
            // Check doctor availability
            $available = $this->checkDoctorAvailability(
                $bookingData['doctor_id'],
                $bookingData['appointment_date'],
                $bookingData['appointment_time']
            );

            if (!$available) {
                throw new Exception('Doctor is not available at the selected time');
            }

            // Create appointment
            $appointment = $this->createAppointment($bookingData);

            // Send confirmation notification
            $this->sendAppointmentConfirmation($appointment);

            Log::info("Patient booked appointment", [
                'patient_id' => $bookingData['patient_id'],
                'doctor_id' => $bookingData['doctor_id'],
                'appointment_id' => $appointment->id,
            ]);

            return [
                'success' => true,
                'appointment' => $appointment,
                'message' => 'Appointment booked successfully',
            ];
        });
    }

    /**
     * Get lab results
     */
    public function getLabResults($patientId, array $filters = []): array
    {
        $query = DB::table('lab_results')
            ->where('patient_id', $patientId)
            ->orderByDesc('result_date');

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('result_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('result_date', '<=', $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    /**
     * View lab result detail
     */
    public function viewLabResult($patientId, $resultId): array
    {
        $result = DB::table('lab_results')
            ->where('id', $resultId)
            ->where('patient_id', $patientId)
            ->first();

        if (!$result) {
            throw new Exception('Lab result not found');
        }

        // Log access
        $this->logPortalActivity($patientId, 'view_lab_result', "Viewed lab result #{$resultId}", $resultId, 'lab_result');

        return (array) $result;
    }

    /**
     * Get prescriptions
     */
    public function getPrescriptions($patientId, array $filters = []): array
    {
        $query = DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->orderByDesc('prescription_date');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Pay bill online
     */
    public function payBill(array $paymentData): array
    {
        return DB::transaction(function () use ($paymentData) {
            $bill = DB::table('medical_bills')
                ->where('id', $paymentData['bill_id'])
                ->where('patient_id', $paymentData['patient_id'])
                ->first();

            if (!$bill) {
                throw new Exception('Bill not found');
            }

            if ($bill->status === 'paid') {
                throw new Exception('Bill already paid');
            }

            // Process payment via payment gateway
            $payment = $this->processPayment($paymentData);

            // Update bill status
            DB::table('medical_bills')
                ->where('id', $bill->id)
                ->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_method' => $paymentData['payment_method'],
                    'payment_reference' => $payment['reference_id'],
                ]);

            // Send payment confirmation
            $this->sendPaymentConfirmation($bill, $payment);

            Log::info("Patient paid bill", [
                'patient_id' => $paymentData['patient_id'],
                'bill_id' => $bill->id,
                'amount' => $bill->total_amount,
            ]);

            return [
                'success' => true,
                'payment' => $payment,
                'message' => 'Payment successful',
            ];
        });
    }

    /**
     * Get visit history
     */
    public function getVisitHistory($patientId, array $filters = []): array
    {
        $query = DB::table('patient_visits')
            ->where('patient_id', $patientId)
            ->orderByDesc('visit_date');

        if (isset($filters['date_from'])) {
            $query->where('visit_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('visit_date', '<=', $filters['date_to']);
        }

        if (isset($filters['department'])) {
            $query->where('department_id', $filters['department']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    /**
     * Request medical certificate
     */
    public function requestMedicalCertificate(array $requestData): MedicalCertificateRequest
    {
        return DB::transaction(function () use ($requestData) {
            $request = MedicalCertificateRequest::create([
                'patient_id' => $requestData['patient_id'],
                'doctor_id' => $requestData['doctor_id'] ?? null,
                'department_id' => $requestData['department_id'] ?? null,
                'request_number' => $this->generateCertificateNumber(),
                'request_date' => today(),
                'certificate_type' => $requestData['certificate_type'],
                'purpose' => $requestData['purpose'],
                'description' => $requestData['description'] ?? null,
                'start_date' => $requestData['start_date'],
                'end_date' => $requestData['end_date'] ?? null,
                'days_requested' => $requestData['days_requested'] ?? null,
                'visit_id' => $requestData['visit_id'] ?? null,
                'admission_id' => $requestData['admission_id'] ?? null,
                'status' => 'pending',
            ]);

            // Notify doctor/admin
            $this->notifyCertificateRequest($request);

            Log::info("Patient requested medical certificate", [
                'request_number' => $request->request_number,
                'patient_id' => $requestData['patient_id'],
                'type' => $request->certificate_type,
            ]);

            return $request;
        });
    }

    /**
     * Send message to doctor
     */
    public function sendMessageToDoctor(array $messageData): PatientMessage
    {
        return DB::transaction(function () use ($messageData) {
            $message = PatientMessage::create([
                'patient_id' => $messageData['patient_id'],
                'doctor_id' => $messageData['doctor_id'] ?? null,
                'sender_id' => $messageData['sender_id'],
                'recipient_id' => $messageData['recipient_id'],
                'message_number' => $this->generateMessageNumber(),
                'parent_id' => $messageData['parent_id'] ?? null,
                'conversation_id' => $messageData['conversation_id'] ?? uniqid('conv_'),
                'subject' => $messageData['subject'],
                'message_body' => $messageData['message_body'],
                'message_type' => $messageData['message_type'] ?? 'general',
                'priority' => $messageData['priority'] ?? 'normal',
                'appointment_id' => $messageData['appointment_id'] ?? null,
                'prescription_id' => $messageData['prescription_id'] ?? null,
                'lab_result_id' => $messageData['lab_result_id'] ?? null,
            ]);

            // Handle attachments
            if (isset($messageData['attachments'])) {
                $this->handleMessageAttachments($message, $messageData['attachments']);
            }

            // Notify recipient
            $this->notifyNewMessage($message);

            Log::info("Patient sent message to doctor", [
                'message_number' => $message->message_number,
                'patient_id' => $messageData['patient_id'],
                'doctor_id' => $messageData['doctor_id'],
            ]);

            return $message;
        });
    }

    /**
     * Get patient messages
     */
    public function getPatientMessages($patientId, array $filters = []): array
    {
        $query = PatientMessage::where('patient_id', $patientId)
            ->with(['sender', 'recipient'])
            ->orderByDesc('created_at');

        if (isset($filters['conversation_id'])) {
            $query->where('conversation_id', $filters['conversation_id']);
        }

        if (isset($filters['is_read'])) {
            $query->where('is_read', $filters['is_read']);
        }

        if (isset($filters['message_type'])) {
            $query->where('message_type', $filters['message_type']);
        }

        return $query->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get health education content
     */
    public function getHealthEducationContent(array $filters = []): array
    {
        $query = HealthEducationContent::where('is_published', true)
            ->where(function ($q) {
                $q->whereNull('publish_start')
                    ->orWhere('publish_start', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('publish_end')
                    ->orWhere('publish_end', '>=', now());
            })
            ->orderByDesc('published_at');

        // Apply filters
        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['tag'])) {
            $query->whereJsonContains('tags', $filters['tag']);
        }

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                    ->orWhere('summary', 'like', "%{$filters['search']}%")
                    ->orWhere('content', 'like', "%{$filters['search']}%");
            });
        }

        if (isset($filters['featured']) && $filters['featured']) {
            $query->where('is_featured', true);
        }

        return $query->paginate($filters['per_page'] ?? 12);
    }

    /**
     * View health education content
     */
    public function viewHealthContent($contentId): array
    {
        $content = HealthEducationContent::findOrFail($contentId);

        // Increment view count
        $content->increment('view_count');

        return $content->toArray();
    }

    /**
     * Rate health content helpfulness
     */
    public function rateContentHelpfulness($contentId, bool $helpful): void
    {
        $content = HealthEducationContent::findOrFail($contentId);

        if ($helpful) {
            $content->increment('helpful_count');
        } else {
            $content->increment('not_helpful_count');
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function getUpcomingAppointments($patientId): array
    {
        return DB::table('appointments')
            ->where('patient_id', $patientId)
            ->where('appointment_date', '>=', today())
            ->where('status', 'scheduled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get()
            ->toArray();
    }

    protected function getRecentLabResults($patientId): array
    {
        return DB::table('lab_results')
            ->where('patient_id', $patientId)
            ->where('status', 'completed')
            ->orderByDesc('result_date')
            ->limit(3)
            ->get()
            ->toArray();
    }

    protected function getActivePrescriptions($patientId): array
    {
        return DB::table('prescriptions')
            ->where('patient_id', $patientId)
            ->where('status', 'active')
            ->orderByDesc('prescription_date')
            ->get()
            ->toArray();
    }

    protected function getPendingBills($patientId): array
    {
        return DB::table('medical_bills')
            ->where('patient_id', $patientId)
            ->where('status', 'pending')
            ->orderByDesc('bill_date')
            ->get()
            ->toArray();
    }

    protected function getUnreadMessageCount($patientId): int
    {
        return PatientMessage::where('patient_id', $patientId)
            ->where('is_read', false)
            ->count();
    }

    protected function getCertificateRequests($patientId): array
    {
        return MedicalCertificateRequest::where('patient_id', $patientId)
            ->orderByDesc('request_date')
            ->limit(5)
            ->get()
            ->toArray();
    }

    protected function getDiagnoses($patientId): array
    {
        return DB::table('diagnoses')
            ->where('patient_id', $patientId)
            ->orderByDesc('diagnosis_date')
            ->get()
            ->toArray();
    }

    protected function getRadiologyResults($patientId, array $filters = []): array
    {
        $query = DB::table('radiology_results')
            ->where('patient_id', $patientId)
            ->orderByDesc('exam_date');

        if (isset($filters['date_from'])) {
            $query->where('exam_date', '>=', $filters['date_from']);
        }

        return $query->paginate($filters['per_page'] ?? 10);
    }

    protected function getAllergies($patientId): array
    {
        return DB::table('patient_allergies')
            ->where('patient_id', $patientId)
            ->get()
            ->toArray();
    }

    protected function getVaccinations($patientId): array
    {
        return DB::table('patient_vaccinations')
            ->where('patient_id', $patientId)
            ->orderByDesc('vaccination_date')
            ->get()
            ->toArray();
    }

    protected function checkDoctorAvailability($doctorId, $date, $time): bool
    {
        // Check if doctor has appointment at this time
        $conflict = DB::table('appointments')
            ->where('doctor_id', $doctorId)
            ->where('appointment_date', $date)
            ->where('appointment_time', $time)
            ->where('status', 'scheduled')
            ->exists();

        return !$conflict;
    }

    protected function createAppointment(array $bookingData)
    {
        // Create appointment in appointments table
        return DB::table('appointments')->insertGetId([
            'patient_id' => $bookingData['patient_id'],
            'doctor_id' => $bookingData['doctor_id'],
            'appointment_date' => $bookingData['appointment_date'],
            'appointment_time' => $bookingData['appointment_time'],
            'status' => 'scheduled',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function sendAppointmentConfirmation($appointment): void
    {
        // Send SMS/WhatsApp/Email confirmation
        // Implementation depends on your notification service
    }

    protected function processPayment(array $paymentData): array
    {
        // Process via payment gateway (Midtrans, Xendit, etc.)
        return [
            'reference_id' => uniqid('PAY_'),
            'status' => 'success',
            'transaction_id' => uniqid(),
        ];
    }

    protected function sendPaymentConfirmation($bill, $payment): void
    {
        // Send payment receipt via SMS/WhatsApp/Email
    }

    protected function notifyCertificateRequest(MedicalCertificateRequest $request): void
    {
        // Notify doctor/admin about new certificate request
    }

    protected function notifyNewMessage(PatientMessage $message): void
    {
        // Notify recipient about new message
        // Send SMS/WhatsApp/Email notification
    }

    protected function handleMessageAttachments(PatientMessage $message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $path = $attachment->store('patient-messages', 'public');

            MessageAttachment::create([
                'message_id' => $message->id,
                'file_name' => $attachment->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $attachment->getClientOriginalExtension(),
                'file_size' => $attachment->getSize(),
                'mime_type' => $attachment->getMimeType(),
            ]);
        }
    }

    protected function logPortalActivity($patientId, string $type, string $description, $referenceId = null, $referenceType = null): void
    {
        DB::table('patient_portal_logs')->insert([
            'patient_id' => $patientId,
            'user_id' => auth()->id(),
            'activity_type' => $type,
            'activity_description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function generateCertificateNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'CERT-' . $date;

        $last = MedicalCertificateRequest::where('request_number', 'like', $prefix . '%')
            ->orderBy('request_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->request_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function generateMessageNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'MSG-' . $date;

        $last = PatientMessage::where('message_number', 'like', $prefix . '%')
            ->orderBy('message_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->message_number, -6) + 1 : 1,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
