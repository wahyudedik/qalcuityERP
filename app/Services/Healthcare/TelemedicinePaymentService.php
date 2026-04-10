<?php

namespace App\Services\Healthcare;

use App\Models\Teleconsultation;
use App\Models\PaymentTransaction;
use App\Models\PaymentGateway;
use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class TelemedicinePaymentService
{
    const PROVIDER_MIDTRANS = 'midtrans';
    const PROVIDER_XENDIT = 'xendit';
    const PROVIDER_DUITKU = 'duitku';
    const PROVIDER_TRIPAY = 'tripay';

    const PAYMENT_METHODS = [
        'qris',
        'credit_card',
        'debit_card',
        'bank_transfer',
        'va_bca',
        'va_bni',
        'va_bri',
        'va_mandiri',
        'ewallet_gopay',
        'ewallet_ovo',
        'ewallet_dana',
        'ewallet_shopeepay',
    ];

    /**
     * Create payment for telemedicine consultation
     */
    public function createPayment(Teleconsultation $consultation, string $paymentMethod = 'qris', string $provider = null): array
    {
        try {
            DB::beginTransaction();

            // Get active payment gateway
            $gateway = $this->getActiveGateway($provider);

            if (!$gateway) {
                return [
                    'success' => false,
                    'error' => 'No payment gateway configured. Please configure payment gateway in settings.',
                ];
            }

            // Generate transaction number
            $transactionNumber = 'TELEMED-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create payment transaction
            $paymentTransaction = PaymentTransaction::create([
                'tenant_id' => $consultation->tenant_id,
                'telemedicine_consultation_id' => $consultation->id,
                'transaction_number' => $transactionNumber,
                'gateway_provider' => $gateway->provider,
                'payment_method' => $paymentMethod,
                'amount' => $consultation->consultation_fee,
                'currency' => 'IDR',
                'status' => 'pending',
                'expired_at' => $this->getExpiryTime($paymentMethod),
                'customer_name' => $consultation->patient->full_name ?? 'Patient',
                'customer_email' => $consultation->patient->email ?? null,
                'customer_phone' => $consultation->patient->phone ?? null,
            ]);

            // Call gateway API
            $result = match ($gateway->provider) {
                self::PROVIDER_MIDTRANS => $this->generateMidtransPayment($gateway, $consultation, $paymentTransaction),
                self::PROVIDER_XENDIT => $this->generateXenditPayment($gateway, $consultation, $paymentTransaction),
                self::PROVIDER_DUITKU => $this->generateDuitkuPayment($gateway, $consultation, $paymentTransaction),
                self::PROVIDER_TRIPAY => $this->generateTripayPayment($gateway, $consultation, $paymentTransaction),
                default => throw new \Exception("Unsupported payment provider: {$gateway->provider}"),
            };

            if ($result['success']) {
                $paymentTransaction->update([
                    'gateway_transaction_id' => $result['gateway_transaction_id'],
                    'qr_string' => $result['qr_string'] ?? null,
                    'qr_image_url' => $result['qr_image_url'] ?? null,
                    'redirect_url' => $result['redirect_url'] ?? null,
                    'va_number' => $result['va_number'] ?? null,
                    'gateway_response' => json_encode($result['raw_response'] ?? []),
                    'status' => 'waiting_payment',
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'transaction_number' => $transactionNumber,
                    'payment_url' => $result['redirect_url'] ?? null,
                    'qr_string' => $result['qr_string'] ?? null,
                    'qr_image_url' => $result['qr_image_url'] ?? null,
                    'va_number' => $result['va_number'] ?? null,
                    'amount' => $consultation->consultation_fee,
                    'expired_at' => $paymentTransaction->expired_at,
                    'payment_method' => $paymentMethod,
                ];
            } else {
                DB::rollBack();

                return $result;
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Telemedicine payment creation failed: {$e->getMessage()}", [
                'consultation_id' => $consultation->id,
                'tenant_id' => $consultation->tenant_id,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => 'Failed to create payment: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment callback/notification
     */
    public function handlePaymentCallback(string $provider, array $payload): array
    {
        try {
            DB::beginTransaction();

            $transaction = null;
            $status = null;
            $gatewayTransactionId = null;

            // Parse callback based on provider
            switch ($provider) {
                case self::PROVIDER_MIDTRANS:
                    $transaction = $this->parseMidtransCallback($payload);
                    break;
                case self::PROVIDER_XENDIT:
                    $transaction = $this->parseXenditCallback($payload);
                    break;
                case self::PROVIDER_DUITKU:
                    $transaction = $this->parseDuitkuCallback($payload);
                    break;
                case self::PROVIDER_TRIPAY:
                    $transaction = $this->parseTripayCallback($payload);
                    break;
            }

            if (!$transaction) {
                return ['success' => false, 'error' => 'Invalid callback payload'];
            }

            // Find payment transaction
            $paymentTransaction = PaymentTransaction::where('gateway_transaction_id', $transaction['gateway_transaction_id'])
                ->orWhere('transaction_number', $transaction['transaction_number'])
                ->first();

            if (!$paymentTransaction) {
                return ['success' => false, 'error' => 'Transaction not found'];
            }

            // Update payment status
            $paymentTransaction->update([
                'status' => $transaction['status'],
                'paid_at' => $transaction['status'] === 'paid' ? now() : null,
                'gateway_response' => json_encode($payload),
            ]);

            // If payment successful, update consultation and create invoice
            if ($transaction['status'] === 'paid') {
                $consultation = $paymentTransaction->telemedicineConsultation;

                if ($consultation) {
                    // Update consultation payment status
                    $consultation->update([
                        'payment_status' => 'paid',
                        'paid_at' => now(),
                    ]);

                    // Create invoice
                    $this->createInvoiceForConsultation($consultation, $paymentTransaction);
                }
            }

            DB::commit();

            return ['success' => true, 'message' => 'Payment callback processed'];

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Telemedicine payment callback failed: {$e->getMessage()}", [
                'provider' => $provider,
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Process refund for cancelled consultation
     */
    public function processRefund(PaymentTransaction $paymentTransaction, string $reason = ''): array
    {
        try {
            DB::beginTransaction();

            $gateway = $this->getActiveGateway($paymentTransaction->gateway_provider);

            if (!$gateway) {
                return ['success' => false, 'error' => 'Payment gateway not configured'];
            }

            // Call refund API based on provider
            $result = match ($paymentTransaction->gateway_provider) {
                self::PROVIDER_MIDTRANS => $this->refundMidtrans($gateway, $paymentTransaction, $reason),
                self::PROVIDER_XENDIT => $this->refundXendit($gateway, $paymentTransaction, $reason),
                default => throw new \Exception("Refund not supported for: {$paymentTransaction->gateway_provider}"),
            };

            if ($result['success']) {
                $paymentTransaction->update([
                    'status' => 'refunded',
                    'refunded_at' => now(),
                    'refund_reason' => $reason,
                    'refund_amount' => $paymentTransaction->amount,
                ]);

                // Update consultation
                $consultation = $paymentTransaction->telemedicineConsultation;
                if ($consultation) {
                    $consultation->update([
                        'payment_status' => 'refunded',
                        'refund_reason' => $reason,
                    ]);
                }

                DB::commit();

                return ['success' => true, 'message' => 'Refund processed successfully'];
            } else {
                DB::rollBack();
                return $result;
            }

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Telemedicine refund failed: {$e->getMessage()}", [
                'transaction_id' => $paymentTransaction->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get active payment gateway
     */
    protected function getActiveGateway(string $provider = null): ?PaymentGateway
    {
        $query = PaymentGateway::where('is_active', true);

        if ($provider) {
            $query->where('provider', $provider);
        }

        return $query->first();
    }

    /**
     * Get expiry time based on payment method
     */
    protected function getExpiryTime(string $paymentMethod)
    {
        return match ($paymentMethod) {
            'qris' => now()->addMinutes(15),
            'ewallet_gopay', 'ewallet_ovo', 'ewallet_dana', 'ewallet_shopeepay' => now()->addMinutes(30),
            'bank_transfer', 'va_bca', 'va_bni', 'va_bri', 'va_mandiri' => now()->addHours(24),
            'credit_card', 'debit_card' => now()->addMinutes(60),
            default => now()->addMinutes(30),
        };
    }

    /**
     * Generate Midtrans payment
     */
    protected function generateMidtransPayment(PaymentGateway $gateway, Teleconsultation $consultation, PaymentTransaction $transaction): array
    {
        $serverKey = $gateway->configuration['server_key'] ?? '';
        $isProduction = $gateway->configuration['is_production'] ?? false;
        $baseUrl = $isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        $payload = [
            'transaction_details' => [
                'order_id' => $transaction->transaction_number,
                'gross_amount' => (int) $transaction->amount,
            ],
            'customer_details' => [
                'first_name' => $transaction->customer_name,
                'email' => $transaction->customer_email,
                'phone' => $transaction->customer_phone,
            ],
            'item_details' => [
                [
                    'id' => "TELEMED-{$consultation->id}",
                    'price' => (int) $consultation->consultation_fee,
                    'quantity' => 1,
                    'name' => "Telemedicine Consultation - Dr. {$consultation->doctor->name}",
                    'category' => 'healthcare',
                ],
            ],
            'callbacks' => [
                'finish' => route('healthcare.telemedicine.payment.finish'),
                'unfinish' => route('healthcare.telemedicine.payment.pending'),
                'error' => route('healthcare.telemedicine.payment.error'),
            ],
        ];

        // Add specific payment method
        if (str_starts_with($transaction->payment_method, 'va_')) {
            $bank = str_replace('va_', '', $transaction->payment_method);
            $payload['enabled_payments'] = ["{$bank}_va"];
        } elseif (str_starts_with($transaction->payment_method, 'ewallet_')) {
            $ewallet = str_replace('ewallet_', '', $transaction->payment_method);
            $payload['enabled_payments'] = [$ewallet];
        }

        $response = Http::withBasicAuth($serverKey, '')
            ->post("{$baseUrl}/v2/charge", $payload);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'gateway_transaction_id' => $data['transaction_id'] ?? $transaction->transaction_number,
                'redirect_url' => $data['redirect_url'] ?? null,
                'qr_string' => $data['qr_string'] ?? null,
                'va_number' => $data['va_numbers'][0]['va_number'] ?? null,
                'raw_response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('status_message', 'Payment creation failed'),
        ];
    }

    /**
     * Generate Xendit payment
     */
    protected function generateXenditPayment(PaymentGateway $gateway, Teleconsultation $consultation, PaymentTransaction $transaction): array
    {
        $secretKey = $gateway->configuration['secret_key'] ?? '';

        $payload = [
            'reference_id' => $transaction->transaction_number,
            'type' => 'DYNAMIC_VA',
            'currency' => 'IDR',
            'amount' => (int) $transaction->amount,
            'channel_properties' => [
                'channel_code' => $this->mapToXenditChannelCode($transaction->payment_method),
            ],
            'customer' => [
                'given_names' => $transaction->customer_name,
                'email' => $transaction->customer_email,
                'mobile_number' => $transaction->customer_phone,
            ],
            'metadata' => [
                'consultation_id' => $consultation->id,
                'doctor_name' => $consultation->doctor->name ?? '',
            ],
        ];

        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.xendit.co/v2/callback_virtual_accounts', $payload);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'gateway_transaction_id' => $data['id'] ?? $transaction->transaction_number,
                'va_number' => $data['account_number'] ?? null,
                'raw_response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('message', 'Payment creation failed'),
        ];
    }

    /**
     * Generate Duitku payment
     */
    protected function generateDuitkuPayment(PaymentGateway $gateway, Teleconsultation $consultation, PaymentTransaction $transaction): array
    {
        $merchantKey = $gateway->configuration['merchant_key'] ?? '';
        $merchantCode = $gateway->configuration['merchant_code'] ?? '';

        $signature = md5("{$merchantCode}{$transaction->transaction_number}{$transaction->amount}{$merchantKey}");

        $payload = [
            'merchantCode' => $merchantCode,
            'paymentAmount' => (int) $transaction->amount,
            'merchantOrderId' => $transaction->transaction_number,
            'productDetails' => "Telemedicine Consultation - Dr. {$consultation->doctor->name}",
            'email' => $transaction->customer_email,
            'userPhone' => $transaction->customer_phone,
            'callbackUrl' => route('healthcare.telemedicine.payment.callback', ['provider' => 'duitku']),
            'returnUrl' => route('healthcare.telemedicine.payment.finish'),
            'signature' => $signature,
        ];

        $response = Http::post('https://api.duitku.com/webapi/api/merchant/v2/inquiry', $payload);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'gateway_transaction_id' => $data['reference'] ?? $transaction->transaction_number,
                'redirect_url' => $data['paymentUrl'] ?? null,
                'qr_string' => $data['qrCode'] ?? null,
                'raw_response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $data['message'] ?? 'Payment creation failed',
        ];
    }

    /**
     * Generate Tripay payment
     */
    protected function generateTripayPayment(PaymentGateway $gateway, Teleconsultation $consultation, PaymentTransaction $transaction): array
    {
        $apiKey = $gateway->configuration['api_key'] ?? '';
        $privateKey = $gateway->configuration['private_key'] ?? '';
        $merchantCode = $gateway->configuration['merchant_code'] ?? '';

        $signature = hash_hmac('sha256', "{$merchantCode}{$transaction->transaction_number}{$transaction->amount}", $privateKey);

        $payload = [
            'method' => $this->mapToTripayMethod($transaction->payment_method),
            'merchant_ref' => $transaction->transaction_number,
            'amount' => (int) $transaction->amount,
            'customer_name' => $transaction->customer_name,
            'customer_email' => $transaction->customer_email,
            'customer_phone' => $transaction->customer_phone,
            'order_items' => [
                [
                    'sku' => "TELEMED-{$consultation->id}",
                    'name' => "Telemedicine Consultation - Dr. {$consultation->doctor->name}",
                    'price' => (int) $consultation->consultation_fee,
                    'quantity' => 1,
                ],
            ],
            'callback_url' => route('healthcare.telemedicine.payment.callback', ['provider' => 'tripay']),
            'return_url' => route('healthcare.telemedicine.payment.finish'),
            'signature' => $signature,
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
        ])->post('https://tripay.co.id/api/transaction/create', $payload);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'success' => true,
                'gateway_transaction_id' => $data['data']['reference'] ?? $transaction->transaction_number,
                'checkout_url' => $data['data']['checkout_url'] ?? null,
                'qr_string' => $data['data']['qr_string'] ?? null,
                'va_number' => $data['data']['pay_code'] ?? null,
                'raw_response' => $data,
            ];
        }

        return [
            'success' => false,
            'error' => $data['message'] ?? 'Payment creation failed',
        ];
    }

    /**
     * Parse Midtrans callback
     */
    protected function parseMidtransCallback(array $payload): ?array
    {
        $statusMap = [
            'settlement' => 'paid',
            'capture' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'expire' => 'expired',
            'refund' => 'refunded',
        ];

        return [
            'gateway_transaction_id' => $payload['transaction_id'] ?? null,
            'transaction_number' => $payload['order_id'] ?? null,
            'status' => $statusMap[$payload['transaction_status'] ?? ''] ?? 'pending',
        ];
    }

    /**
     * Parse Xendit callback
     */
    protected function parseXenditCallback(array $payload): ?array
    {
        return [
            'gateway_transaction_id' => $payload['external_id'] ?? null,
            'transaction_number' => $payload['external_id'] ?? null,
            'status' => $payload['status'] === 'PAID' ? 'paid' : 'pending',
        ];
    }

    /**
     * Parse Duitku callback
     */
    protected function parseDuitkuCallback(array $payload): ?array
    {
        return [
            'gateway_transaction_id' => $payload['reference'] ?? null,
            'transaction_number' => $payload['merchantOrderId'] ?? null,
            'status' => $payload['resultCode'] === '00' ? 'paid' : 'failed',
        ];
    }

    /**
     * Parse Tripay callback
     */
    protected function parseTripayCallback(array $payload): ?array
    {
        $statusMap = [
            'PAID' => 'paid',
            'UNPAID' => 'pending',
            'EXPIRED' => 'expired',
            'FAILED' => 'failed',
        ];

        return [
            'gateway_transaction_id' => $payload['reference'] ?? null,
            'transaction_number' => $payload['merchant_ref'] ?? null,
            'status' => $statusMap[$payload['status'] ?? ''] ?? 'pending',
        ];
    }

    /**
     * Create invoice for paid consultation
     */
    protected function createInvoiceForConsultation(Teleconsultation $consultation, PaymentTransaction $payment): void
    {
        // Create healthcare invoice
        Invoice::create([
            'tenant_id' => $consultation->tenant_id,
            'patient_id' => $consultation->patient_id,
            'invoice_number' => 'INV-TELEMED-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT),
            'invoice_date' => now(),
            'due_date' => now(),
            'total_amount' => $consultation->consultation_fee,
            'paid_amount' => $consultation->consultation_fee,
            'status' => 'paid',
            'notes' => "Telemedicine consultation payment - Transaction: {$payment->transaction_number}",
        ]);

        Log::info("Invoice created for telemedicine consultation", [
            'consultation_id' => $consultation->id,
            'payment_transaction' => $payment->transaction_number,
        ]);
    }

    /**
     * Refund Midtrans payment
     */
    protected function refundMidtrans(PaymentGateway $gateway, PaymentTransaction $transaction, string $reason): array
    {
        $serverKey = $gateway->configuration['server_key'] ?? '';
        $isProduction = $gateway->configuration['is_production'] ?? false;
        $baseUrl = $isProduction ? 'https://api.midtrans.com' : 'https://api.sandbox.midtrans.com';

        $payload = [
            'refund_key' => 'REFUND-' . time(),
            'amount' => (int) $transaction->amount,
            'reason' => $reason,
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->post("{$baseUrl}/v2/{$transaction->gateway_transaction_id}/refund", $payload);

        if ($response->successful()) {
            return [
                'success' => true,
                'refund_id' => $response->json('refund_key'),
            ];
        }

        return [
            'success' => false,
            'error' => $response->json('status_message', 'Refund failed'),
        ];
    }

    /**
     * Refund Xendit payment
     */
    protected function refundXendit(PaymentGateway $gateway, PaymentTransaction $transaction, string $reason): array
    {
        // Xendit VA refunds are typically manual, but we can log the request
        Log::info("Xendit refund requested", [
            'transaction_id' => $transaction->id,
            'reason' => $reason,
        ]);

        return [
            'success' => true,
            'message' => 'Refund request logged. Please process manually in Xendit dashboard.',
        ];
    }

    /**
     * Map payment method to Xendit channel code
     */
    protected function mapToXenditChannelCode(string $method): string
    {
        $map = [
            'va_bca' => 'BCA',
            'va_bni' => 'BNI',
            'va_bri' => 'BRI',
            'va_mandiri' => 'MANDIRI',
            'qris' => 'QRIS',
        ];

        return $map[$method] ?? 'BCA';
    }

    /**
     * Map payment method to Tripay method
     */
    protected function mapToTripayMethod(string $method): string
    {
        $map = [
            'qris' => 'QRIS',
            'va_bca' => 'BCAVA',
            'va_bni' => 'BNIVA',
            'va_bri' => 'BRIVA',
            'va_mandiri' => 'MANDIRIVA',
            'ewallet_gopay' => 'GOPAY',
            'ewallet_ovo' => 'OVO',
            'ewallet_dana' => 'DANA',
        ];

        return $map[$method] ?? 'QRIS';
    }
}
