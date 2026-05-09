<?php

namespace App\Services;

use App\Models\PaymentCallback;
use App\Models\PaymentTransaction;
use App\Models\SalesOrder;
use App\Models\TenantPaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    private int $tenantId;

    // Gateway providers
    const PROVIDER_MIDTRANS = 'midtrans';

    const PROVIDER_XENDIT = 'xendit';

    const PROVIDER_DUITKU = 'duitku';

    const PROVIDER_TRIPAY = 'tripay';

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Generate QRIS payment
     */
    public function generateQrisPayment(SalesOrder $order, ?string $provider = null): array
    {
        try {
            // Get gateway configuration
            $gateway = $this->getGateway($provider);

            if (! $gateway) {
                return [
                    'success' => false,
                    'error' => 'No payment gateway configured',
                ];
            }

            // Generate transaction number
            $transactionNumber = $this->generateTransactionNumber();

            // Create payment transaction record
            $paymentTransaction = PaymentTransaction::create([
                'tenant_id' => $this->tenantId,
                'sales_order_id' => $order->id,
                'transaction_number' => $transactionNumber,
                'gateway_provider' => $gateway->provider,
                'payment_method' => 'qris',
                'amount' => $order->total ?? $order->grand_total,
                'status' => 'pending',
                'expired_at' => now()->addMinutes(15), // QRIS expires in 15 minutes
            ]);

            // Call gateway API based on provider
            $result = match ($gateway->provider) {
                self::PROVIDER_MIDTRANS => $this->generateMidtransQris($gateway, $order, $paymentTransaction),
                self::PROVIDER_XENDIT => $this->generateXenditQris($gateway, $order, $paymentTransaction),
                self::PROVIDER_DUITKU => $this->generateDuitkuQris($gateway, $order, $paymentTransaction),
                default => throw new \Exception("Unsupported provider: {$gateway->provider}"),
            };

            if ($result['success']) {
                // Update payment transaction
                $paymentTransaction->update([
                    'gateway_transaction_id' => $result['gateway_transaction_id'],
                    'qr_string' => $result['qr_string'],
                    'qr_image_url' => $result['qr_image_url'] ?? null,
                    'gateway_response' => json_encode($result['raw_response']),
                    'status' => 'waiting_payment',
                ]);

                return [
                    'success' => true,
                    'transaction_number' => $transactionNumber,
                    'qr_string' => $result['qr_string'],
                    'qr_image_url' => $result['qr_image_url'] ?? null,
                    'expiry_time' => $paymentTransaction->expired_at->timestamp,
                    'amount' => $order->grand_total,
                ];
            } else {
                $paymentTransaction->update([
                    'status' => 'failed',
                    'failure_reason' => $result['error'],
                ]);

                return $result;
            }

        } catch (\Exception $e) {
            Log::error("QRIS generation failed: {$e->getMessage()}", [
                'tenant_id' => $this->tenantId,
                'order_id' => $order->id,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $transactionNumber): array
    {
        try {
            $paymentTransaction = PaymentTransaction::where('tenant_id', $this->tenantId)
                ->where('transaction_number', $transactionNumber)
                ->first();

            if (! $paymentTransaction) {
                return [
                    'success' => false,
                    'error' => 'Transaction not found',
                ];
            }

            // If already completed, return cached status
            if (in_array($paymentTransaction->status, ['success', 'failed', 'expired', 'cancelled'])) {
                return [
                    'success' => true,
                    'status' => $paymentTransaction->status,
                    'paid_at' => $paymentTransaction->paid_at,
                    'transaction_number' => $paymentTransaction->transaction_number,
                ];
            }

            // Check with gateway
            $gateway = TenantPaymentGateway::where('tenant_id', $this->tenantId)
                ->where('provider', $paymentTransaction->gateway_provider)
                ->first();

            if (! $gateway) {
                return [
                    'success' => false,
                    'error' => 'Gateway configuration not found',
                ];
            }

            $result = match ($gateway->provider) {
                self::PROVIDER_MIDTRANS => $this->checkMidtransStatus($gateway, $paymentTransaction),
                self::PROVIDER_XENDIT => $this->checkXenditStatus($gateway, $paymentTransaction),
                self::PROVIDER_DUITKU => $this->checkDuitkuStatus($gateway, $paymentTransaction),
                default => throw new \Exception('Unsupported provider'),
            };

            return $result;

        } catch (\Exception $e) {
            Log::error("Payment status check failed: {$e->getMessage()}");

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Handle webhook callback
     */
    public function handleWebhook(string $provider, array $payload, ?string $signature = null): array
    {
        try {
            // Log callback
            $callback = PaymentCallback::create([
                'tenant_id' => $this->tenantId,
                'gateway_provider' => $provider,
                'event_type' => $payload['transaction_status'] ?? $payload['status'] ?? 'unknown',
                'payload' => $payload,
                'signature' => $signature,
            ]);

            // Verify signature
            $verified = $this->verifyWebhookSignature($provider, $payload, $signature);
            $callback->update(['verified' => $verified]);

            if (! $verified) {
                $callback->update([
                    'processed' => true,
                    'error_message' => 'Invalid signature',
                ]);

                return [
                    'success' => false,
                    'error' => 'Invalid webhook signature',
                ];
            }

            // Process based on provider
            $result = match ($provider) {
                self::PROVIDER_MIDTRANS => $this->processMidtransWebhook($payload),
                self::PROVIDER_XENDIT => $this->processXenditWebhook($payload),
                default => throw new \Exception("Unsupported provider: {$provider}"),
            };

            $callback->update([
                'processed' => $result['success'],
                'processed_at' => now(),
                'error_message' => $result['error'] ?? null,
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error("Webhook processing failed: {$e->getMessage()}");

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify gateway credentials
     */
    public function verifyGateway(string $provider): array
    {
        try {
            $gateway = TenantPaymentGateway::where('tenant_id', $this->tenantId)
                ->where('provider', $provider)
                ->first();

            if (! $gateway) {
                return [
                    'success' => false,
                    'error' => 'Gateway not configured',
                ];
            }

            $credentials = $gateway->getDecryptedCredentials();

            return match ($provider) {
                self::PROVIDER_MIDTRANS => $this->verifyMidtransCredentials($credentials),
                self::PROVIDER_XENDIT => $this->verifyXenditCredentials($credentials),
                self::PROVIDER_DUITKU => $this->verifyDuitkuCredentials($credentials),
                default => ['success' => false, 'error' => 'Unsupported provider'],
            };

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    // ==================== MIDTRANS METHODS ====================

    private function generateMidtransQris(TenantPaymentGateway $gateway, SalesOrder $order, PaymentTransaction $paymentTransaction): array
    {
        $credentials = $gateway->getDecryptedCredentials();
        $serverKey = $credentials['server_key'];
        $isProduction = $gateway->environment === 'production';

        $baseUrl = $isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $payload = [
            'payment_type' => 'qris',
            'transaction_details' => [
                'order_id' => $paymentTransaction->transaction_number,
                'gross_amount' => (int) ($order->total ?? $order->grand_total),
            ],
            'customer_details' => [
                'first_name' => $order->customer?->name ?? $order->customer_name ?? 'Customer',
                'email' => $order->customer?->email ?? $order->customer_email ?? 'customer@example.com',
                'phone' => $order->customer?->phone ?? $order->customer_phone ?? '08123456789',
            ],
            'item_details' => $order->items->map(fn ($item) => [
                'id' => (string) $item->product_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => substr($item->product?->name ?? $item->product_name ?? 'Item', 0, 50),
            ])->toArray(),
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->post("{$baseUrl}/charge", $payload);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'gateway_transaction_id' => $data['transaction_id'],
                'qr_string' => $data['actions'][0]['url'] ?? $data['qr_string'],
                'qr_image_url' => $data['actions'][0]['url'] ?? null,
                'raw_response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('status_message') ?? 'Failed to generate QRIS',
        ];
    }

    private function checkMidtransStatus(TenantPaymentGateway $gateway, PaymentTransaction $paymentTransaction): array
    {
        $credentials = $gateway->getDecryptedCredentials();
        $serverKey = $credentials['server_key'];
        $isProduction = $gateway->environment === 'production';

        $baseUrl = $isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';

        $response = Http::withBasicAuth($serverKey, '')
            ->get("{$baseUrl}/{$paymentTransaction->gateway_transaction_id}/status");

        if ($response->successful()) {
            $data = $response->json();
            $status = $this->mapMidtransStatus($data['transaction_status']);

            // Update payment transaction if status changed
            if ($status !== $paymentTransaction->status) {
                $paymentTransaction->update([
                    'status' => $status,
                    'paid_at' => $status === 'success' ? now() : null,
                    'gateway_response' => json_encode($data),
                ]);

                // Update order if payment successful
                if ($status === 'success' && $paymentTransaction->salesOrder) {
                    $paymentTransaction->salesOrder->update([
                        'payment_status' => 'paid',
                        'status' => 'completed',
                    ]);
                }
            }

            return [
                'success' => true,
                'status' => $status,
                'paid_at' => $paymentTransaction->paid_at,
                'transaction_number' => $paymentTransaction->transaction_number,
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to check status',
        ];
    }

    private function processMidtransWebhook(array $payload): array
    {
        $orderId = $payload['order_id'];
        $transactionStatus = $payload['transaction_status'];
        $fraudStatus = $payload['fraud_status'] ?? null;

        // Find payment transaction
        $paymentTransaction = PaymentTransaction::where('transaction_number', $orderId)->first();

        if (! $paymentTransaction) {
            return [
                'success' => false,
                'error' => 'Transaction not found',
            ];
        }

        // Determine status
        $status = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

        // Update payment transaction
        $updateData = [
            'status' => $status,
            'gateway_response' => json_encode($payload),
        ];

        if ($status === 'success') {
            $updateData['paid_at'] = now();
        }

        $paymentTransaction->update($updateData);

        // Update order if payment successful
        if ($status === 'success' && $paymentTransaction->salesOrder) {
            $paymentTransaction->salesOrder->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);
        }

        return [
            'success' => true,
            'message' => "Payment status updated to {$status}",
        ];
    }

    private function verifyMidtransCredentials(array $credentials): array
    {
        $serverKey = $credentials['server_key'] ?? null;

        if (! $serverKey) {
            return [
                'success' => false,
                'error' => 'Server key is required',
            ];
        }

        // Test API call
        $response = Http::withBasicAuth($serverKey, '')
            ->get('https://api.sandbox.midtrans.com/v2/status');

        return [
            'success' => $response->successful() || $response->status() === 404, // 404 is OK for status endpoint without order_id
        ];
    }

    private function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        if ($fraudStatus === 'challenge') {
            return 'processing';
        }

        return match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'waiting_payment',
            'deny', 'cancel', 'expire' => 'failed',
            'refund' => 'refund',
            default => 'pending',
        };
    }

    // ==================== XENDIT METHODS ====================

    private function generateXenditQris(TenantPaymentGateway $gateway, SalesOrder $order, PaymentTransaction $paymentTransaction): array
    {
        $credentials = $gateway->getDecryptedCredentials();
        $apiKey = $credentials['api_key'];
        $isProduction = $gateway->environment === 'production';

        $baseUrl = $isProduction
            ? 'https://api.xendit.co'
            : 'https://api.xendit.co'; // Xendit uses same URL for sandbox

        $payload = [
            'external_id' => $paymentTransaction->transaction_number,
            'type' => 'DYNAMIC',
            'callback_url' => $gateway->webhook_url,
            'amount' => (int) ($order->total ?? $order->grand_total),
            'description' => "Pembayaran Order #{$order->number}",
            'currency' => 'IDR',
            'metadata' => [
                'order_id' => $order->id,
                'tenant_id' => $this->tenantId,
            ],
        ];

        $response = Http::withBasicAuth($apiKey, '')
            ->post("{$baseUrl}/qr_codes", $payload);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'gateway_transaction_id' => $data['id'],
                'qr_string' => $data['qr_string'],
                'qr_image_url' => $data['qr_url'] ?? null,
                'raw_response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('message') ?? 'Failed to generate QRIS',
        ];
    }

    private function checkXenditStatus(TenantPaymentGateway $gateway, PaymentTransaction $paymentTransaction): array
    {
        $credentials = $gateway->getDecryptedCredentials();
        $apiKey = $credentials['api_key'];

        $response = Http::withBasicAuth($apiKey, '')
            ->get("https://api.xendit.co/qr_codes/{$paymentTransaction->gateway_transaction_id}");

        if ($response->successful()) {
            $data = $response->json();
            $status = $this->mapXenditStatus($data['status']);

            if ($status !== $paymentTransaction->status) {
                $paymentTransaction->update([
                    'status' => $status,
                    'paid_at' => $status === 'success' ? now() : null,
                    'gateway_response' => json_encode($data),
                ]);

                if ($status === 'success' && $paymentTransaction->salesOrder) {
                    $paymentTransaction->salesOrder->update([
                        'payment_status' => 'paid',
                        'status' => 'completed',
                    ]);
                }
            }

            return [
                'success' => true,
                'status' => $status,
                'paid_at' => $paymentTransaction->paid_at,
                'transaction_number' => $paymentTransaction->transaction_number,
            ];
        }

        return [
            'success' => false,
            'error' => 'Failed to check status',
        ];
    }

    private function processXenditWebhook(array $payload): array
    {
        $externalId = $payload['external_id'];
        $status = $payload['status'];

        $paymentTransaction = PaymentTransaction::where('transaction_number', $externalId)->first();

        if (! $paymentTransaction) {
            return [
                'success' => false,
                'error' => 'Transaction not found',
            ];
        }

        $mappedStatus = $this->mapXenditStatus($status);

        $updateData = [
            'status' => $mappedStatus,
            'gateway_response' => json_encode($payload),
        ];

        if ($mappedStatus === 'success') {
            $updateData['paid_at'] = now();
        }

        $paymentTransaction->update($updateData);

        if ($mappedStatus === 'success' && $paymentTransaction->salesOrder) {
            $paymentTransaction->salesOrder->update([
                'payment_status' => 'paid',
                'status' => 'completed',
            ]);
        }

        return [
            'success' => true,
            'message' => "Payment status updated to {$mappedStatus}",
        ];
    }

    private function verifyXenditCredentials(array $credentials): array
    {
        $apiKey = $credentials['api_key'] ?? null;

        if (! $apiKey) {
            return [
                'success' => false,
                'error' => 'API key is required',
            ];
        }

        // Test API call
        $response = Http::withBasicAuth($apiKey, '')
            ->get('https://api.xendit.co/balance');

        return [
            'success' => $response->successful(),
        ];
    }

    private function mapXenditStatus(string $status): string
    {
        return match ($status) {
            'ACTIVE' => 'waiting_payment',
            'COMPLETED' => 'success',
            'INACTIVE', 'EXPIRED' => 'expired',
            default => 'pending',
        };
    }

    // ==================== HELPER METHODS ====================

    // ==================== DUITKU METHODS ====================

    private function generateDuitkuQris(TenantPaymentGateway $gateway, SalesOrder $order, PaymentTransaction $paymentTransaction): array
    {
        $credentials = $gateway->getDecryptedCredentials();
        $merchantCode = $credentials['merchant_code'];
        $merchantKey = $credentials['merchant_key'];
        $isProduction = $gateway->environment === 'production';

        $baseUrl = $isProduction
            ? 'https://api.duitku.com/webapi/api/merchant/v2/inquiry'
            : 'https://sandbox.duitku.com/webapi/api/merchant/v2/inquiry';

        $amount = (int) ($order->total ?? $order->grand_total);
        $merchantOrderId = $paymentTransaction->transaction_number;
        $datetime = now()->format('Y-m-d H:i:s');

        // Duitku signature: MD5(merchantCode + merchantOrderId + amount + merchantKey)
        $signature = md5($merchantCode.$merchantOrderId.$amount.$merchantKey);

        $payload = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => $amount,
            'paymentMethod' => 'QR', // QRIS
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => "Pembayaran Order #{$order->number}",
            'additionalParam' => '',
            'merchantUserInfo' => $order->customer?->name ?? 'Customer',
            'customerVaName' => $order->customer?->name ?? 'Customer',
            'email' => $order->customer?->email ?? 'customer@example.com',
            'phoneNumber' => $order->customer?->phone ?? '08123456789',
            'itemDetails' => $order->items->map(fn ($item) => [
                'name' => substr($item->product?->name ?? 'Item', 0, 50),
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
            ])->toArray(),
            'callbackUrl' => route('payment.webhook', ['provider' => 'duitku']),
            'returnUrl' => config('app.url').'/pos',
            'signature' => $signature,
            'expiryPeriod' => 15, // 15 minutes
            'datetime' => $datetime,
        ];

        $response = Http::post($baseUrl, $payload);

        if ($response->successful()) {
            $data = $response->json();

            if (($data['statusCode'] ?? '') === '00') {
                return [
                    'success' => true,
                    'gateway_transaction_id' => $data['reference'] ?? $merchantOrderId,
                    'qr_string' => $data['qrString'] ?? $data['paymentUrl'] ?? '',
                    'qr_image_url' => $data['qrUrl'] ?? null,
                    'raw_response' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $data['statusMessage'] ?? 'Gagal membuat QRIS Duitku',
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('statusMessage') ?? 'Gagal membuat QRIS Duitku',
        ];
    }

    private function checkDuitkuStatus(TenantPaymentGateway $gateway, PaymentTransaction $paymentTransaction): array
    {
        $credentials = $gateway->getDecryptedCredentials();
        $merchantCode = $credentials['merchant_code'];
        $merchantKey = $credentials['merchant_key'];
        $isProduction = $gateway->environment === 'production';

        $baseUrl = $isProduction
            ? 'https://api.duitku.com/webapi/api/merchant/transactionStatus'
            : 'https://sandbox.duitku.com/webapi/api/merchant/transactionStatus';

        $merchantOrderId = $paymentTransaction->transaction_number;
        $datetime = now()->format('Y-m-d H:i:s');
        $signature = md5($merchantCode.$merchantOrderId.$merchantKey);

        $response = Http::post($baseUrl, [
            'merchantCode' => $merchantCode,
            'merchantOrderId' => $merchantOrderId,
            'signature' => $signature,
            'datetime' => $datetime,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $resultCode = $data['statusCode'] ?? '01';

            $status = match ($resultCode) {
                '00' => 'success',
                '01' => 'waiting_payment',
                '02' => 'failed',
                default => 'pending',
            };

            if ($status !== $paymentTransaction->status) {
                $paymentTransaction->update([
                    'status' => $status,
                    'paid_at' => $status === 'success' ? now() : null,
                    'gateway_response' => json_encode($data),
                ]);

                if ($status === 'success' && $paymentTransaction->salesOrder) {
                    $paymentTransaction->salesOrder->update([
                        'status' => 'completed',
                        'payment_type' => 'qris',
                        'payment_method' => 'qris',
                        'paid_amount' => $paymentTransaction->amount,
                        'completed_at' => now(),
                    ]);
                }
            }

            return [
                'success' => true,
                'status' => $status,
                'paid_at' => $paymentTransaction->paid_at,
                'transaction_number' => $paymentTransaction->transaction_number,
            ];
        }

        return [
            'success' => false,
            'error' => 'Gagal memeriksa status pembayaran Duitku',
        ];
    }

    private function verifyDuitkuCredentials(array $credentials): array
    {
        $merchantCode = $credentials['merchant_code'] ?? null;
        $merchantKey = $credentials['merchant_key'] ?? null;

        if (! $merchantCode || ! $merchantKey) {
            return [
                'success' => false,
                'error' => 'Merchant code dan merchant key wajib diisi',
            ];
        }

        // Test with a simple status check
        $datetime = now()->format('Y-m-d H:i:s');
        $signature = md5($merchantCode.'TEST-'.time().$merchantKey);

        $response = Http::post('https://sandbox.duitku.com/webapi/api/merchant/transactionStatus', [
            'merchantCode' => $merchantCode,
            'merchantOrderId' => 'TEST-'.time(),
            'signature' => $signature,
            'datetime' => $datetime,
        ]);

        // 400/404 means credentials are valid but order not found — that's OK
        return [
            'success' => $response->successful() || in_array($response->status(), [400, 404]),
        ];
    }

    private function getGateway(?string $provider = null): ?TenantPaymentGateway
    {
        if ($provider) {
            return TenantPaymentGateway::where('tenant_id', $this->tenantId)
                ->where('provider', $provider)
                ->where('is_active', true)
                ->first();
        }

        // Get default active gateway
        return TenantPaymentGateway::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    private function generateTransactionNumber(): string
    {
        $prefix = 'PAY';
        $date = now()->format('Ymd');

        $lastTransaction = PaymentTransaction::where('tenant_id', $this->tenantId)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTransaction ? (intval(substr($lastTransaction->transaction_number, -4)) + 1) : 1;

        return sprintf('%s-%s-%04d', $prefix, $date, $sequence);
    }

    private function verifyWebhookSignature(string $provider, array $payload, ?string $signature): bool
    {
        $gateway = TenantPaymentGateway::where('tenant_id', $this->tenantId)
            ->where('provider', $provider)
            ->first();

        if (! $gateway || ! $gateway->webhook_secret) {
            return true; // Skip verification if no secret configured
        }

        $expectedSignature = hash_hmac('sha256', json_encode($payload), $gateway->webhook_secret);

        return hash_equals($expectedSignature, $signature ?? '');
    }
}
