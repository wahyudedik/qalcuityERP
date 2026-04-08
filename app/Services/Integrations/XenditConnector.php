<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Xendit Payment Gateway Connector
 * 
 * Handles payment processing with Xendit
 * Supports Virtual Accounts, E-Wallets, Cards
 */
class XenditConnector extends BaseConnector
{
    protected string $apiUrl = 'https://api.xendit.co';
    protected string $secretKey;

    public function __construct(Integration $integration)
    {
        parent::__construct($integration);
        $this->secretKey = $integration->getConfigValue('secret_key');
    }

    public function authenticate(): bool
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->apiUrl}/users");

            if ($response->successful()) {
                $this->integration->markAsActive();
                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::error('Xendit authentication failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Create Virtual Account payment
     */
    public function createVirtualAccount(array $paymentData): array
    {
        try {
            $payload = [
                'external_id' => $paymentData['order_id'],
                'bank_code' => $paymentData['bank'] ?? 'BCA',
                'name' => $paymentData['customer_name'],
                'expected_amount' => $paymentData['amount'],
                'is_closed' => true,
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->apiUrl}/callback_virtual_accounts", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'va_id' => $data['id'] ?? null,
                    'va_number' => $data['bank_account_number'] ?? null,
                    'bank' => $data['bank_code'] ?? null,
                ];
            }

            return ['success' => false, 'error' => $response->json()['message'] ?? 'Failed'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createVirtualAccount');
        }
    }

    /**
     * Create E-Wallet payment (OVO, GoPay, Dana, etc.)
     */
    public function createEwalletPayment(array $paymentData): array
    {
        try {
            $payload = [
                'reference_id' => $paymentData['order_id'],
                'currency' => 'IDR',
                'amount' => $paymentData['amount'],
                'checkout_method' => 'ONE_TIME_PAYMENT',
                'channel_code' => $paymentData['ewallet'] ?? 'ID_OVO',
                'channel_properties' => [
                    'mobile_number' => $paymentData['customer_phone'] ?? '',
                ],
            ];

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->apiUrl}/ewallets/charges", $payload);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'charge_id' => $data['id'] ?? null,
                    'checkout_url' => $data['actions']['mobile_web_checkout_url'] ?? null,
                    'status' => $data['status'] ?? 'PENDING',
                ];
            }

            return ['success' => false, 'error' => $response->json()['message'] ?? 'Failed'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createEwalletPayment');
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $paymentId): array
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->apiUrl}/ewallets/charges/{$paymentId}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'amount' => $data['amount'] ?? 0,
                ];
            }

            return ['success' => false];
        } catch (Throwable $e) {
            return $this->handleError($e, 'getPaymentStatus');
        }
    }

    /**
     * Handle Xendit webhook
     */
    public function handleWebhook(array $payload): void
    {
        Log::info('Xendit webhook received', [
            'event' => $payload['event'] ?? 'unknown',
            'business_id' => $payload['business_id'] ?? null,
        ]);

        // Verify callback token
        $callbackToken = request()->header('x-callback-token');
        $configuredToken = $this->integration->getConfigValue('callback_token');

        if ($callbackToken !== $configuredToken) {
            Log::error('Xendit webhook token mismatch');
            return;
        }

        // Process based on event
        if (str_contains($payload['event'] ?? '', 'payment')) {
            $this->handlePaymentEvent($payload);
        }
    }

    protected function handlePaymentEvent(array $payload): void
    {
        Log::info('Xendit payment event', [
            'status' => $payload['status'] ?? null,
            'amount' => $payload['amount'] ?? null,
        ]);
    }

    // E-commerce methods (not applicable)
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
