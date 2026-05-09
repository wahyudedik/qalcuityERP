<?php

namespace App\Services\Integrations;

use App\Models\PaymentGateway;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Create payment with Midtrans
     */
    public function createMidtransPayment(array $orderData, int $tenantId): array
    {
        $gateway = PaymentGateway::where('tenant_id', $tenantId)
            ->where('provider', 'midtrans')
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            throw new \Exception('Midtrans gateway not configured');
        }

        $serverKey = $gateway->environment === 'production'
            ? $gateway->secret_key
            : config('services.midtrans.server_key_sandbox');

        $baseUrl = $gateway->environment === 'production'
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderData['order_id'],
                'gross_amount' => $orderData['amount'],
            ],
            'customer_details' => [
                'first_name' => $orderData['customer_name'] ?? 'Customer',
                'email' => $orderData['customer_email'] ?? 'customer@example.com',
                'phone' => $orderData['customer_phone'] ?? '081234567890',
            ],
            'enabled_payments' => $orderData['payment_methods'] ?? [
                'credit_card',
                'bank_transfer',
                'gopay',
                'shopeepay',
            ],
        ];

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->post("{$baseUrl}/charge", $payload);

            if (! $response->ok()) {
                throw new \Exception('Midtrans API error: '.$response->body());
            }

            $result = $response->json();

            // Save transaction
            $transaction = PaymentTransaction::create([
                'tenant_id' => $tenantId,
                'transaction_id' => $result['transaction_id'],
                'order_id' => $orderData['order_id'],
                'gateway_provider' => 'midtrans',
                'amount' => $orderData['amount'],
                'currency' => 'IDR',
                'status' => 'pending',
                'payment_method' => null,
                'payment_type' => null,
                'gateway_response' => $result,
                'metadata' => $orderData['metadata'] ?? null,
                'expired_at' => now()->addHours(24),
            ]);

            return [
                'success' => true,
                'redirect_url' => $result['redirect_url'] ?? null,
                'token' => $result['token'] ?? null,
                'transaction_id' => $result['transaction_id'],
                'transaction' => $transaction,
            ];

        } catch (\Throwable $e) {
            Log::error('Midtrans payment creation failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create payment with Xendit
     */
    public function createXenditPayment(array $orderData, int $tenantId): array
    {
        $gateway = PaymentGateway::where('tenant_id', $tenantId)
            ->where('provider', 'xendit')
            ->where('is_active', true)
            ->first();

        if (! $gateway) {
            throw new \Exception('Xendit gateway not configured');
        }

        $apiKey = $gateway->environment === 'production'
            ? $gateway->api_key
            : config('services.xendit.api_key_sandbox');

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => $orderData['order_id'],
                    'amount' => $orderData['amount'],
                    'description' => $orderData['description'] ?? 'Payment',
                    'invoice_duration' => 86400, // 24 hours
                    'customer' => [
                        'given_names' => $orderData['customer_name'] ?? 'Customer',
                        'email' => $orderData['customer_email'] ?? 'customer@example.com',
                        'mobile_number' => $orderData['customer_phone'] ?? '+6281234567890',
                    ],
                    'success_redirect_url' => $orderData['success_url'] ?? null,
                    'failure_redirect_url' => $orderData['failure_url'] ?? null,
                ]);

            if (! $response->ok()) {
                throw new \Exception('Xendit API error: '.$response->body());
            }

            $result = $response->json();

            $transaction = PaymentTransaction::create([
                'tenant_id' => $tenantId,
                'transaction_id' => $result['id'],
                'order_id' => $orderData['order_id'],
                'gateway_provider' => 'xendit',
                'amount' => $orderData['amount'],
                'currency' => 'IDR',
                'status' => 'pending',
                'gateway_response' => $result,
                'expired_at' => now()->addSeconds(86400),
            ]);

            return [
                'success' => true,
                'invoice_url' => $result['invoice_url'],
                'transaction_id' => $result['id'],
                'transaction' => $transaction,
            ];

        } catch (\Throwable $e) {
            Log::error('Xendit payment creation failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook notification
     */
    public function handleWebhook(string $provider, array $payload): bool
    {
        try {
            switch ($provider) {
                case 'midtrans':
                    return $this->handleMidtransWebhook($payload);
                case 'xendit':
                    return $this->handleXenditWebhook($payload);
                case 'duitku':
                    return $this->handleDuitkuWebhook($payload);
                default:
                    throw new \Exception("Unsupported provider: {$provider}");
            }
        } catch (\Throwable $e) {
            Log::error("Payment webhook handling failed ({$provider}): ".$e->getMessage());

            return false;
        }
    }

    /**
     * Handle Midtrans webhook
     */
    protected function handleMidtransWebhook(array $payload): bool
    {
        $orderId = $payload['order_id'];
        $statusCode = $payload['status_code'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        $transaction = PaymentTransaction::where('order_id', $orderId)->first();

        if (! $transaction) {
            Log::warning("Midtrans webhook: Transaction not found for order {$orderId}");

            return false;
        }

        // Determine status
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $transaction->markAsPaid();
        } elseif ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $transaction->markAsFailed("Transaction {$transactionStatus}");
        }

        $transaction->update([
            'payment_method' => $payload['payment_type'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
            'gateway_response' => $payload,
        ]);

        Log::info("Midtrans webhook processed for order: {$orderId}, status: {$transactionStatus}");

        return true;
    }

    /**
     * Handle Xendit webhook
     */
    protected function handleXenditWebhook(array $payload): bool
    {
        $externalId = $payload['external_id'];
        $status = $payload['status'];

        $transaction = PaymentTransaction::where('order_id', $externalId)->first();

        if (! $transaction) {
            Log::warning("Xendit webhook: Transaction not found for order {$externalId}");

            return false;
        }

        if ($status == 'PAID') {
            $transaction->markAsPaid();
        } elseif ($status == 'EXPIRED' || $status == 'FAILED') {
            $transaction->markAsFailed("Payment {$status}");
        }

        $transaction->update([
            'gateway_response' => $payload,
        ]);

        Log::info("Xendit webhook processed for order: {$externalId}, status: {$status}");

        return true;
    }

    /**
     * Handle Duitku webhook
     */
    protected function handleDuitkuWebhook(array $payload): bool
    {
        // Similar implementation for Duitku
        Log::info('Duitku webhook received', $payload);

        return true;
    }

    /**
     * Get transaction status
     */
    public function getTransactionStatus(string $transactionId, string $provider, int $tenantId): ?array
    {
        $transaction = PaymentTransaction::where('transaction_id', $transactionId)
            ->where('gateway_provider', $provider)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $transaction) {
            return null;
        }

        return [
            'transaction_id' => $transaction->transaction_id,
            'order_id' => $transaction->order_id,
            'status' => $transaction->status,
            'amount' => $transaction->amount,
            'paid_at' => $transaction->paid_at,
            'payment_method' => $transaction->payment_method,
        ];
    }
}
