<?php

namespace App\Services;

use App\Models\TenantWhatsAppSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppService - Multi-provider WhatsApp notification service.
 *
 * Supported providers:
 * - Fonnte (https://fonnte.com)
 * - Wablas (https://wablas.com)
 * - Twilio WhatsApp API
 * - Ultramsg (https://ultramsg.com)
 * - Custom Webhook
 *
 * Tenants can configure their own provider via settings.
 */
class WhatsAppService
{
    protected int $tenantId;

    protected array $settings;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->settings = $this->loadSettings();
    }

    /**
     * Load WhatsApp settings from tenant WhatsApp settings table.
     */
    protected function loadSettings(): array
    {
        $settings = TenantWhatsAppSettings::getForTenant($this->tenantId);

        if (! $settings) {
            return [
                'provider' => 'fonnte',
                'api_key' => null,
                'api_secret' => null,
                'phone_number' => null,
                'webhook_url' => null,
                'is_active' => false,
                'enable_invoice_notifications' => true,
                'enable_appointment_reminders' => true,
                'enable_payment_reminders' => true,
                'enable_general_notifications' => true,
            ];
        }

        return [
            'provider' => $settings->provider,
            'api_key' => $settings->api_key,
            'api_secret' => $settings->api_secret,
            'phone_number' => $settings->phone_number,
            'webhook_url' => $settings->webhook_url,
            'is_active' => $settings->is_active,
            'enable_invoice_notifications' => $settings->enable_invoice_notifications,
            'enable_appointment_reminders' => $settings->enable_appointment_reminders,
            'enable_payment_reminders' => $settings->enable_payment_reminders,
            'enable_general_notifications' => $settings->enable_general_notifications,
        ];
    }

    /**
     * Check if WhatsApp is configured and active for this tenant.
     */
    public function isConfigured(): bool
    {
        return $this->settings['is_active']
            && ! empty($this->settings['api_key']);
    }

    /**
     * Send WhatsApp message to a phone number.
     *
     * @param  string  $to  Phone number (08xx or 62xx)
     * @param  string  $message  Message content
     * @param  array  $options  Additional options
     * @return array Result with status, message, and data
     */
    public function sendMessage(string $to, string $message, array $options = []): array
    {
        if (! $this->isConfigured()) {
            return [
                'status' => 'error',
                'message' => 'WhatsApp belum dikonfigurasi. Silakan setup di pengaturan tenant.',
            ];
        }

        // Normalize phone number
        $to = $this->normalizePhoneNumber($to);

        if (! $this->isValidPhoneNumber($to)) {
            return [
                'status' => 'error',
                'message' => 'Nomor telepon tidak valid.',
            ];
        }

        $provider = $this->settings['provider'];

        try {
            return match ($provider) {
                'fonnte' => $this->sendViaFonnte($to, $message, $options),
                'wablas' => $this->sendViaWablas($to, $message, $options),
                'twilio' => $this->sendViaTwilio($to, $message, $options),
                'ultramsg' => $this->sendViaUltramsg($to, $message, $options),
                'custom' => $this->sendViaCustomWebhook($to, $message, $options),
                default => [
                    'status' => 'error',
                    'message' => "Provider WhatsApp '{$provider}' tidak didukung.",
                ],
            };
        } catch (\Throwable $e) {
            Log::error("WhatsApp send error ({$provider}): ".$e->getMessage());

            return [
                'status' => 'error',
                'message' => 'Gagal mengirim pesan WhatsApp: '.$e->getMessage(),
            ];
        }
    }

    /**
     * Send invoice notification via WhatsApp.
     */
    public function sendInvoiceNotification(string $to, array $invoiceData): array
    {
        $message = $this->buildInvoiceMessage($invoiceData);

        return $this->sendMessage($to, $message);
    }

    /**
     * Send appointment reminder via WhatsApp.
     */
    public function sendAppointmentReminder(string $to, array $appointmentData): array
    {
        $message = $this->buildAppointmentReminderMessage($appointmentData);

        return $this->sendMessage($to, $message);
    }

    /**
     * Send payment reminder via WhatsApp.
     */
    public function sendPaymentReminder(string $to, array $paymentData): array
    {
        $message = $this->buildPaymentReminderMessage($paymentData);

        return $this->sendMessage($to, $message);
    }

    // ─── Provider Implementations ─────────────────────────────────────────────

    /**
     * Send via Fonnte API.
     */
    protected function sendViaFonnte(string $to, string $message, array $options = []): array
    {
        $response = Http::withHeaders([
            'Authorization' => $this->settings['api_key'],
        ])->post('https://api.fonnte.com/send', [
            'target' => $to,
            'message' => $message,
            'countryCode' => '62',
        ]);

        $result = $response->json();

        if ($response->successful() && ($result['status'] ?? false)) {
            return [
                'status' => 'success',
                'message' => 'Pesan WhatsApp berhasil dikirim via Fonnte.',
                'provider' => 'fonnte',
                'data' => $result,
            ];
        }

        Log::warning('Fonnte WA failed', ['response' => $result]);

        return [
            'status' => 'error',
            'message' => 'Gagal mengirim via Fonnte: '.($result['reason'] ?? 'Unknown error'),
            'provider' => 'fonnte',
        ];
    }

    /**
     * Send via Wablas API.
     */
    protected function sendViaWablas(string $to, string $message, array $options = []): array
    {
        $response = Http::post($this->settings['webhook_url'] ?? 'https://solo.wablas.com/api/send-message', [
            'phone' => $to,
            'message' => $message,
            'token' => $this->settings['api_key'],
        ]);

        $result = $response->json();

        if ($response->successful() && ($result['status'] ?? $result['success'] ?? false)) {
            return [
                'status' => 'success',
                'message' => 'Pesan WhatsApp berhasil dikirim via Wablas.',
                'provider' => 'wablas',
                'data' => $result,
            ];
        }

        Log::warning('Wablas WA failed', ['response' => $result]);

        return [
            'status' => 'error',
            'message' => 'Gagal mengirim via Wablas: '.($result['error'] ?? 'Unknown error'),
            'provider' => 'wablas',
        ];
    }

    /**
     * Send via Twilio WhatsApp API.
     */
    protected function sendViaTwilio(string $to, string $message, array $options = []): array
    {
        $accountSid = $this->settings['api_key'];
        $authToken = $this->settings['api_secret'];
        $fromNumber = $this->settings['phone_number'];

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->post("https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json", [
                'From' => 'whatsapp:'.$fromNumber,
                'To' => 'whatsapp:'.$to,
                'Body' => $message,
            ]);

        $result = $response->json();

        if ($response->successful() && ! empty($result['sid'])) {
            return [
                'status' => 'success',
                'message' => 'Pesan WhatsApp berhasil dikirim via Twilio.',
                'provider' => 'twilio',
                'data' => $result,
            ];
        }

        Log::warning('Twilio WA failed', ['response' => $result]);

        return [
            'status' => 'error',
            'message' => 'Gagal mengirim via Twilio: '.($result['message'] ?? 'Unknown error'),
            'provider' => 'twilio',
        ];
    }

    /**
     * Send via Ultramsg API.
     */
    protected function sendViaUltramsg(string $to, string $message, array $options = []): array
    {
        $instanceId = $this->settings['api_key'];
        $token = $this->settings['api_secret'];

        $response = Http::post("https://api.ultramsg.com/{$instanceId}/messages/chat", [
            'token' => $token,
            'to' => $to,
            'body' => $message,
        ]);

        $result = $response->json();

        if ($response->successful() && ($result['sent'] ?? false)) {
            return [
                'status' => 'success',
                'message' => 'Pesan WhatsApp berhasil dikirim via Ultramsg.',
                'provider' => 'ultramsg',
                'data' => $result,
            ];
        }

        Log::warning('Ultramsg WA failed', ['response' => $result]);

        return [
            'status' => 'error',
            'message' => 'Gagal mengirim via Ultramsg: '.($result['error'] ?? 'Unknown error'),
            'provider' => 'ultramsg',
        ];
    }

    /**
     * Send via Custom Webhook.
     */
    protected function sendViaCustomWebhook(string $to, string $message, array $options = []): array
    {
        $webhookUrl = $this->settings['webhook_url'];

        if (empty($webhookUrl)) {
            return [
                'status' => 'error',
                'message' => 'Webhook URL tidak dikonfigurasi.',
            ];
        }

        $payload = array_merge([
            'to' => $to,
            'message' => $message,
            'tenant_id' => $this->tenantId,
        ], $options);

        $response = Http::post($webhookUrl, $payload);

        if ($response->successful()) {
            return [
                'status' => 'success',
                'message' => 'Pesan WhatsApp berhasil dikirim via Custom Webhook.',
                'provider' => 'custom',
                'data' => $response->json(),
            ];
        }

        Log::warning('Custom Webhook WA failed', ['response' => $response->body()]);

        return [
            'status' => 'error',
            'message' => 'Gagal mengirim via Custom Webhook.',
            'provider' => 'custom',
        ];
    }

    // ─── Message Builders ─────────────────────────────────────────────────────

    /**
     * Build invoice message template.
     */
    protected function buildInvoiceMessage(array $data): string
    {
        $tenantName = $data['tenant_name'] ?? 'Kami';
        $invoiceNumber = $data['invoice_number'] ?? '-';
        $total = $data['total'] ?? 0;
        $dueDate = $data['due_date'] ?? '-';
        $customerName = $data['customer_name'] ?? 'Customer';

        $formattedTotal = 'Rp '.number_format($total, 0, ',', '.');

        return "Halo {$customerName},\n\n"
            ."Berikut tagihan dari *{$tenantName}*:\n\n"
            ."📄 No. Invoice: *{$invoiceNumber}*\n"
            ."💰 Total: *{$formattedTotal}*\n"
            ."📅 Jatuh Tempo: *{$dueDate}*\n\n"
            ."Mohon segera lakukan pembayaran sebelum tanggal jatuh tempo.\n\n"
            .'Terima kasih 🙏';
    }

    /**
     * Build appointment reminder message.
     */
    protected function buildAppointmentReminderMessage(array $data): string
    {
        $doctorName = $data['doctor_name'] ?? '-';
        $date = $data['date'] ?? '-';
        $time = $data['time'] ?? '-';
        $patientName = $data['patient_name'] ?? 'Pasien';
        $location = $data['location'] ?? '';

        $message = "Halo {$patientName},\n\n"
            ."Pengingat janji temu:\n\n"
            ."👨‍⚕️ Dokter: *{$doctorName}*\n"
            ."📅 Tanggal: *{$date}*\n"
            ."🕐 Waktu: *{$time}*\n";

        if ($location) {
            $message .= "📍 Lokasi: *{$location}*\n";
        }

        $message .= "\nMohon datang 10 menit sebelum jadwal.\n\n"
            .'Terima kasih 🙏';

        return $message;
    }

    /**
     * Build payment reminder message.
     */
    protected function buildPaymentReminderMessage(array $data): string
    {
        $customerName = $data['customer_name'] ?? 'Customer';
        $invoiceNumber = $data['invoice_number'] ?? '-';
        $amount = $data['amount'] ?? 0;
        $dueDate = $data['due_date'] ?? '-';
        $daysOverdue = $data['days_overdue'] ?? 0;

        $formattedAmount = 'Rp '.number_format($amount, 0, ',', '.');
        $statusText = $daysOverdue > 0
            ? "sudah terlambat *{$daysOverdue} hari*"
            : "akan jatuh tempo pada *{$dueDate}*";

        return "Halo {$customerName},\n\n"
            ."Pengingat pembayaran:\n\n"
            ."📄 Invoice: *{$invoiceNumber}*\n"
            ."💰 Jumlah: *{$formattedAmount}*\n"
            ."⏰ Status: {$statusText}\n\n"
            ."Segera lakukan pembayaran untuk menghindari denda.\n\n"
            .'Terima kasih 🙏';
    }

    // ─── Helper Methods ───────────────────────────────────────────────────────

    /**
     * Normalize phone number to international format.
     */
    protected function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Convert leading 0 to 62
        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        // Add 62 if doesn't start with country code
        if (! str_starts_with($phone, '62') && ! str_starts_with($phone, '+')) {
            $phone = '62'.$phone;
        }

        return $phone;
    }

    /**
     * Validate phone number format.
     */
    protected function isValidPhoneNumber(string $phone): bool
    {
        return preg_match('/^62[0-9]{8,12}$/', $phone) === 1;
    }

    /**
     * Get available providers.
     */
    public static function getAvailableProviders(): array
    {
        return [
            'fonnte' => 'Fonnte (Recommended for Indonesia)',
            'wablas' => 'Wablas',
            'twilio' => 'Twilio WhatsApp API',
            'ultramsg' => 'Ultramsg',
            'custom' => 'Custom Webhook',
        ];
    }
}
