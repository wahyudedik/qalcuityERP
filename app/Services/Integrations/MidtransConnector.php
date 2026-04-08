<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Midtrans Payment Gateway Connector
 * 
 * Handles payment processing with Midtrans
 * Supports Snap, Core API, and Notifications
 */
class MidtransConnector extends BaseConnector
{
    protected string $apiUrl;
    protected string $serverKey;

    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $environment = $integration->getConfigValue('environment') ?? 'sandbox';
        $this->serverKey = $integration->getConfigValue('server_key');

        $this->apiUrl = $environment === 'production'
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    public function authenticate(): bool
    {
        try {
            // Test with getting transaction status (dummy)
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get("{$this->apiUrl}/status/dummy-transaction");

            // Midtrans returns 404 for non-existent transaction, which means auth is working
            if ($response->status() === 404 || $response->successful()) {
                $this->integration->markAsActive();
                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::error('Midtrans authentication failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create payment transaction
     */
    public function createPayment(array $paymentData): array
    {
        try {
            $payload = [
                'payment_type' => $paymentData['payment_type'] ?? 'bank_transfer',
                'transaction_details' => [
                    'order_id' => $paymentData['order_id'],
                    'gross_amount' => $paymentData['amount'],
                ],
                'customer_details' => [
                    'first_name' => $paymentData['customer_name'],
                    'email' => $paymentData['customer_email'] ?? '',
                    'phone' => $paymentData['customer_phone'] ?? '',
                ],
            ];

            // Add bank transfer config if applicable
            if ($payload['payment_type'] === 'bank_transfer') {
                $payload['bank_transfer'] = [
                    'bank' => $paymentData['bank'] ?? 'bca',
                ];
            }

            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->apiUrl}/charge", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'redirect_url' => $data['redirect_url'] ?? null,
                    'va_number' => $data['va_numbers'][0]['va_number'] ?? null,
                    'status' => $data['transaction_status'] ?? 'pending',
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['status_message'] ?? 'Payment failed',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createPayment');
        }
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $transactionId): array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get("{$this->apiUrl}/{$transactionId}/status");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'status' => $data['transaction_status'] ?? 'unknown',
                    'fraud_status' => $data['fraud_status'] ?? null,
                    'payment_type' => $data['payment_type'] ?? null,
                    'transaction_time' => $data['transaction_time'] ?? null,
                ];
            }

            return ['success' => false, 'error' => 'Failed to get status'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'getTransactionStatus');
        }
    }

    /**
     * Cancel transaction
     */
    public function cancelTransaction(string $transactionId): array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->apiUrl}/{$transactionId}/cancel");

            return ['success' => $response->successful()];
        } catch (Throwable $e) {
            return $this->handleError($e, 'cancelTransaction');
        }
    }

    /**
     * Refund transaction
     */
    public function refundTransaction(string $transactionId, float $amount, string $reason = ''): array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->apiUrl}/refund", [
                    'transaction_id' => $transactionId,
                    'amount' => $amount,
                    'reason' => $reason,
                ]);

            return ['success' => $response->successful()];
        } catch (Throwable $e) {
            return $this->handleError($e, 'refundTransaction');
        }
    }

    /**
     * Handle Midtrans notification/webhook
     */
    public function handleWebhook(array $payload): void
    {
        Log::info('Midtrans notification received', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
        ]);

        // Verify signature
        if (!$this->verifyNotification($payload)) {
            Log::error('Midtrans notification signature mismatch');
            return;
        }

        // Process based on status
        match ($payload['transaction_status']) {
            'capture', 'settlement' => $this->handlePaymentSuccess($payload),
            'pending' => $this->handlePaymentPending($payload),
            'deny', 'expire', 'cancel' => $this->handlePaymentFailed($payload),
            default => Log::warning('Unknown Midtrans status', ['status' => $payload['transaction_status']]),
        };
    }

    /**
     * Verify Midtrans notification signature
     */
    protected function verifyNotification(array $payload): bool
    {
        $signatureKey = hash(
            'sha512',
            $payload['order_id'] .
            $payload['status_code'] .
            $payload['gross_amount'] .
            $this->serverKey
        );

        return $signatureKey === ($payload['signature_key'] ?? '');
    }

    /**
     * Handle successful payment
     */
    protected function handlePaymentSuccess(array $payload): void
    {
        Log::info('Payment successful', [
            'order_id' => $payload['order_id'],
            'transaction_id' => $payload['transaction_id'] ?? null,
        ]);

        // Update invoice/payment status in ERP
        // This would typically dispatch a job or call a service
    }

    /**
     * Handle pending payment
     */
    protected function handlePaymentPending(array $payload): void
    {
        Log::info('Payment pending', [
            'order_id' => $payload['order_id'],
            'payment_type' => $payload['payment_type'] ?? null,
        ]);
    }

    /**
     * Handle failed payment
     */
    protected function handlePaymentFailed(array $payload): void
    {
        Log::warning('Payment failed', [
            'order_id' => $payload['order_id'],
            'status' => $payload['transaction_status'],
        ]);
    }

    // E-commerce methods (not applicable for payment gateway)
    public function syncProducts(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }
    public function syncOrders(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }
    public function syncInventory(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0];
    }
    public function registerWebhooks(): array
    {
        return ['success' => true, 'registered' => []];
    }
}
