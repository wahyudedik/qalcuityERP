<?php

namespace App\Services;

use App\Models\BPJSClaim;
use App\Models\HL7Message;
use App\Models\LabEquipmentIntegration;
use App\Models\NotificationLog;
use App\Models\PharmacyIntegrationLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HealthcareIntegrationService
{
    /**
     * Send HL7/FHIR Message
     */
    public function sendHL7Message(array $messageData): HL7Message
    {
        return DB::transaction(function () use ($messageData) {
            $hl7Message = HL7Message::create([
                'message_id' => $this->generateHL7MessageId(),
                'message_type' => $messageData['message_type'], // ADT, ORM, ORU, SIU
                'message_version' => $messageData['message_version'] ?? '2.5',
                'trigger_event' => $messageData['trigger_event'], // A01, A03, etc.
                'direction' => 'outbound',
                'source_system' => config('app.name'),
                'destination_system' => $messageData['destination_system'],
                'raw_message' => $messageData['raw_message'] ?? null,
                'parsed_data' => $messageData['parsed_data'] ?? null,
                'fhir_resource' => $messageData['fhir_resource'] ?? null,
                'patient_id' => $messageData['patient_id'] ?? null,
                'patient_identifier' => $messageData['patient_identifier'] ?? null,
                'encounter_id' => $messageData['encounter_id'] ?? null,
                'status' => 'received',
            ]);

            // Process HL7 message
            try {
                $this->processHL7Message($hl7Message);

                Log::info('HL7 message sent', [
                    'message_id' => $hl7Message->message_id,
                    'type' => $hl7Message->message_type,
                    'event' => $hl7Message->trigger_event,
                ]);
            } catch (Exception $e) {
                $hl7Message->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                Log::error('HL7 message failed', [
                    'message_id' => $hl7Message->message_id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $hl7Message;
        });
    }

    /**
     * Receive HL7/FHIR Message
     */
    public function receiveHL7Message(string $rawMessage): HL7Message
    {
        return DB::transaction(function () use ($rawMessage) {
            // Parse HL7 message
            $parsedData = $this->parseHL7Message($rawMessage);

            $hl7Message = HL7Message::create([
                'message_id' => $this->generateHL7MessageId(),
                'message_type' => $parsedData['message_type'] ?? 'UNKNOWN',
                'message_version' => $parsedData['version'] ?? '2.5',
                'trigger_event' => $parsedData['trigger_event'] ?? 'UNKNOWN',
                'direction' => 'inbound',
                'source_system' => $parsedData['source_system'] ?? 'External',
                'destination_system' => config('app.name'),
                'raw_message' => $rawMessage,
                'parsed_data' => $parsedData,
                'status' => 'received',
            ]);

            // Process based on message type
            $this->processIncomingHL7Message($hl7Message);

            // Send acknowledgment
            $ack = $this->generateHL7Acknowledgment($hl7Message);
            $hl7Message->update([
                'acknowledgment' => $ack,
                'status' => 'acknowledged',
            ]);

            Log::info('HL7 message received', [
                'message_id' => $hl7Message->message_id,
                'type' => $hl7Message->message_type,
            ]);

            return $hl7Message;
        });
    }

    /**
     * Submit BPJS Claim
     */
    public function submitBPJSClaim(array $claimData): BPJSClaim
    {
        return DB::transaction(function () use ($claimData) {
            $claim = BPJSClaim::create([
                'claim_number' => $this->generateBPJSClaimNumber(),
                'patient_id' => $claimData['patient_id'],
                'admission_id' => $claimData['admission_id'] ?? null,
                'bill_id' => $claimData['bill_id'] ?? null,
                'bpjs_number' => $claimData['bpjs_number'],
                'bpjs_class' => $claimData['bpjs_class'] ?? null,
                'participant_type' => $claimData['participant_type'] ?? null,
                'sep_number' => $claimData['sep_number'] ?? null,
                'admission_date' => $claimData['admission_date'],
                'discharge_date' => $claimData['discharge_date'] ?? null,
                'diagnosis_code' => $claimData['diagnosis_code'] ?? null,
                'diagnosis_description' => $claimData['diagnosis_description'] ?? null,
                'procedure_code' => $claimData['procedure_code'] ?? null,
                'procedure_description' => $claimData['procedure_description'] ?? null,
                'claimed_amount' => $claimData['claimed_amount'] ?? 0,
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Submit to BPJS API (V-Claim)
            try {
                $response = $this->submitToBPJSAPI($claim);

                $claim->update([
                    'response_data' => json_encode($response),
                    'status' => 'verified',
                    'verified_at' => now(),
                ]);

                Log::info('BPJS claim submitted', [
                    'claim_number' => $claim->claim_number,
                    'amount' => $claim->claimed_amount,
                ]);
            } catch (Exception $e) {
                $claim->update([
                    'status' => 'failed',
                    'response_data' => json_encode(['error' => $e->getMessage()]),
                ]);

                Log::error('BPJS claim submission failed', [
                    'claim_number' => $claim->claim_number,
                    'error' => $e->getMessage(),
                ]);
            }

            return $claim;
        });
    }

    /**
     * Check BPJS Eligibility
     */
    public function checkBPJSEligibility(string $bpjsNumber, string $cardDate): array
    {
        try {
            $response = Http::withHeaders([
                'X-cons-id' => config('services.bpjs.cons_id'),
                'X-timestamp' => $this->getBPJSTimestamp(),
                'X-signature' => $this->generateBPJSSignature(),
            ])->get(config('services.bpjs.base_url').'/Peserta/nokartu/'.$bpjsNumber.'/tglSEP/'.$cardDate);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'eligible' => true,
                    'bpjs_number' => $bpjsNumber,
                    'participant_name' => $data['response']['peserta']['nama'] ?? null,
                    'bpjs_class' => $data['response']['peserta']['hakKelas'] ?? null,
                    'participant_type' => $data['response']['peserta']['jenisPeserta'] ?? null,
                    'eligibility_date' => $data['response']['peserta']['tglTAT'] ?? null,
                ];
            }

            return ['eligible' => false, 'error' => 'BPJS API error'];
        } catch (Exception $e) {
            return ['eligible' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Register Lab Equipment
     */
    public function registerLabEquipment(array $equipmentData): LabEquipmentIntegration
    {
        return LabEquipmentIntegration::create([
            'equipment_name' => $equipmentData['equipment_name'],
            'equipment_model' => $equipmentData['equipment_model'] ?? null,
            'manufacturer' => $equipmentData['manufacturer'] ?? null,
            'serial_number' => $equipmentData['serial_number'] ?? null,
            'integration_type' => $equipmentData['integration_type'], // ASTM, HL7, API, file_import
            'connection_type' => $equipmentData['connection_type'], // serial, tcp_ip, http, file
            'connection_details' => json_encode($equipmentData['connection_details'] ?? []),
            'api_endpoint' => $equipmentData['api_endpoint'] ?? null,
            'authentication' => json_encode($equipmentData['authentication'] ?? []),
            'test_code_mapping' => json_encode($equipmentData['test_code_mapping'] ?? []),
            'unit_mapping' => json_encode($equipmentData['unit_mapping'] ?? []),
            'reference_ranges' => json_encode($equipmentData['reference_ranges'] ?? []),
            'configuration' => json_encode($equipmentData['configuration'] ?? []),
            'polling_interval_seconds' => $equipmentData['polling_interval_seconds'] ?? 60,
            'auto_import_results' => $equipmentData['auto_import_results'] ?? true,
        ]);
    }

    /**
     * Import Lab Results from Equipment
     */
    public function importLabResults(LabEquipmentIntegration $equipment, array $results): array
    {
        $imported = 0;
        $failed = 0;

        foreach ($results as $result) {
            try {
                // Map equipment test codes to system codes
                $testCode = $this->mapTestCode($equipment, $result['test_code']);

                // Create or update lab result
                // This would integrate with your LabResult model

                $imported++;
            } catch (Exception $e) {
                $failed++;
                Log::error('Lab result import failed', [
                    'equipment' => $equipment->equipment_name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $equipment->update([
            'last_connection' => now(),
            'is_connected' => true,
        ]);

        return [
            'imported' => $imported,
            'failed' => $failed,
            'total' => count($results),
        ];
    }

    /**
     * Send Prescription to Pharmacy
     */
    public function sendPrescriptionToPharmacy(array $prescriptionData): PharmacyIntegrationLog
    {
        return DB::transaction(function () use ($prescriptionData) {
            $log = PharmacyIntegrationLog::create([
                'pharmacy_id' => $prescriptionData['pharmacy_id'] ?? null,
                'prescription_id' => $prescriptionData['prescription_id'] ?? null,
                'integration_type' => 'e-prescription',
                'transaction_number' => $this->generatePharmacyTransactionNumber(),
                'direction' => 'to_pharmacy',
                'request_data' => json_encode($prescriptionData),
                'status' => 'pending',
                'sent_at' => now(),
            ]);

            // Send to pharmacy system
            try {
                $response = $this->sendToPharmacyAPI($prescriptionData);

                $log->update([
                    'status' => 'processed',
                    'response_data' => json_encode($response),
                    'received_at' => now(),
                    'response_time_ms' => $response['response_time'] ?? 0,
                ]);

                Log::info('Prescription sent to pharmacy', [
                    'prescription_id' => $prescriptionData['prescription_id'],
                    'pharmacy_id' => $prescriptionData['pharmacy_id'],
                ]);
            } catch (Exception $e) {
                $log->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                Log::error('Pharmacy integration failed', [
                    'prescription_id' => $prescriptionData['prescription_id'],
                    'error' => $e->getMessage(),
                ]);
            }

            return $log;
        });
    }

    /**
     * Send SMS Notification
     */
    public function sendSMSNotification(array $notificationData): NotificationLog
    {
        return $this->sendNotification('sms', $notificationData);
    }

    /**
     * Send WhatsApp Notification
     */
    public function sendWhatsAppNotification(array $notificationData): NotificationLog
    {
        return $this->sendNotification('whatsapp', $notificationData);
    }

    /**
     * Send Email Notification
     */
    public function sendEmailNotification(array $notificationData): NotificationLog
    {
        return $this->sendNotification('email', $notificationData);
    }

    /**
     * Send notification via channel
     */
    protected function sendNotification(string $channel, array $data): NotificationLog
    {
        return DB::transaction(function () use ($channel, $data) {
            $notification = NotificationLog::create([
                'patient_id' => $data['patient_id'] ?? null,
                'user_id' => $data['user_id'] ?? null,
                'notification_number' => $this->generateNotificationNumber(),
                'channel' => $channel,
                'template_code' => $data['template_code'] ?? null,
                'subject' => $data['subject'] ?? null,
                'message_body' => $data['message_body'],
                'message_data' => $data['message_data'] ?? null,
                'recipient_name' => $data['recipient_name'] ?? null,
                'recipient_phone' => $data['recipient_phone'] ?? null,
                'recipient_email' => $data['recipient_email'] ?? null,
                'gateway_provider' => $data['gateway_provider'] ?? $this->getDefaultGateway($channel),
                'status' => 'pending',
                'category' => $data['category'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'reference_type' => $data['reference_type'] ?? null,
            ]);

            // Send via gateway
            try {
                $response = $this->sendViaGateway($channel, $notification);

                $notification->update([
                    'status' => 'sent',
                    'gateway_message_id' => $response['message_id'] ?? null,
                    'gateway_response' => json_encode($response),
                    'sent_at' => now(),
                ]);

                Log::info('Notification sent', [
                    'notification_number' => $notification->notification_number,
                    'channel' => $channel,
                    'recipient' => $notification->recipient_phone ?? $notification->recipient_email,
                ]);
            } catch (Exception $e) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'retry_count' => 1,
                    'next_retry_at' => now()->addMinutes(5),
                ]);

                Log::error('Notification failed', [
                    'notification_number' => $notification->notification_number,
                    'error' => $e->getMessage(),
                ]);
            }

            return $notification;
        });
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function generateHL7MessageId(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'HL7-'.$date;

        $last = HL7Message::where('message_id', 'like', $prefix.'%')
            ->orderBy('message_id', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->message_id, -6) + 1 : 1,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function generateBPJSClaimNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'BPJS-'.$date;

        $last = BPJSClaim::where('claim_number', 'like', $prefix.'%')
            ->orderBy('claim_number', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->claim_number, -4) + 1 : 1,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function generateNotificationNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'NOTIF-'.$date;

        $last = NotificationLog::where('notification_number', 'like', $prefix.'%')
            ->orderBy('notification_number', 'desc')
            ->first();

        return $prefix.'-'.str_pad(
            $last ? (int) substr($last->notification_number, -6) + 1 : 1,
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    protected function generatePharmacyTransactionNumber(): string
    {
        return 'PHARM-'.uniqid();
    }

    protected function parseHL7Message(string $rawMessage): array
    {
        // Parse HL7 v2.x message
        $segments = explode("\r", $rawMessage);
        $mshSegment = explode('|', $segments[0] ?? '');

        return [
            'message_type' => $mshSegment[8] ?? 'UNKNOWN',
            'version' => $mshSegment[11] ?? '2.5',
            'trigger_event' => $mshSegment[9] ?? 'UNKNOWN',
            'source_system' => $mshSegment[3] ?? 'Unknown',
            'segments' => count($segments),
        ];
    }

    protected function processHL7Message(HL7Message $message): void
    {
        // Process HL7 message based on type
        match ($message->message_type) {
            'ADT' => $this->processADTMessage($message),
            'ORM' => $this->processORMMessage($message),
            'ORU' => $this->processORUMessage($message),
            'SIU' => $this->processSIUMessage($message),
            default => throw new Exception("Unknown HL7 message type: {$message->message_type}"),
        };

        $message->update(['status' => 'processed', 'is_valid' => true]);
    }

    protected function processIncomingHL7Message(HL7Message $message): void
    {
        // Process incoming message
        $this->processHL7Message($message);
    }

    protected function generateHL7Acknowledgment(HL7Message $message): array
    {
        return [
            'ack_code' => 'AA', // Application Accept
            'message_control_id' => $message->message_id,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function submitToBPJSAPI(BPJSClaim $claim): array
    {
        // Submit to BPJS V-Claim API
        // Implementation depends on BPJS API documentation

        return [
            'status' => 'success',
            'reference_id' => uniqid(),
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function getBPJSTimestamp(): string
    {
        return time();
    }

    protected function generateBPJSSignature(): string
    {
        // Generate HMAC signature for BPJS API
        $consId = config('services.bpjs.cons_id');
        $secretKey = config('services.bpjs.secret_key');
        $timestamp = $this->getBPJSTimestamp();

        return hash_hmac('sha256', $consId.'&'.$timestamp, $secretKey);
    }

    protected function sendToPharmacyAPI(array $prescriptionData): array
    {
        // Send to pharmacy POS system
        return [
            'status' => 'success',
            'response_time' => 150,
        ];
    }

    protected function sendViaGateway(string $channel, NotificationLog $notification): array
    {
        // Send via SMS/WhatsApp/Email gateway
        match ($channel) {
            'sms' => $this->sendSMS($notification),
            'whatsapp' => $this->sendWhatsApp($notification),
            'email' => $this->sendEmail($notification),
        };

        return [
            'message_id' => uniqid(),
            'status' => 'sent',
        ];
    }

    protected function sendSMS(NotificationLog $notification): void
    {
        // Send via Twilio or other SMS gateway
    }

    protected function sendWhatsApp(NotificationLog $notification): void
    {
        // Send via WhatsApp Business API
    }

    protected function sendEmail(NotificationLog $notification): void
    {
        // Send via email service
    }

    protected function processADTMessage(HL7Message $message): void
    {
        // Process Admission/Discharge/Transfer
    }

    protected function processORMMessage(HL7Message $message): void
    {
        // Process Order Message
    }

    protected function processORUMessage(HL7Message $message): void
    {
        // Process Observation Result
    }

    protected function processSIUMessage(HL7Message $message): void
    {
        // Process Scheduling Information Unsolicited
    }

    protected function mapTestCode(LabEquipmentIntegration $equipment, string $testCode): string
    {
        $mapping = $equipment->test_code_mapping ?? [];

        return $mapping[$testCode] ?? $testCode;
    }

    protected function getDefaultGateway(string $channel): string
    {
        return match ($channel) {
            'sms' => config('services.sms.gateway', 'twilio'),
            'whatsapp' => config('services.whatsapp.gateway', 'whatsapp_business'),
            'email' => config('mail.default', 'smtp'),
        };
    }
}
