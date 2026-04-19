<?php

namespace App\Services;

use App\Models\PaymentTransaction;
use App\Models\PaymentCallback;
use App\Models\SalesOrder;
use App\Models\TenantPaymentGateway;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookHandlerService
{
    protected int $tenantId;

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    /**
     * Handle Midtrans webhook notification
     */
    public function handleMidtrans(array $payload, ?string $signature): array
    {
        try {
            // BUG-API-001 FIX: Check idempotency BEFORE processing
            $idempotencyResult = app(\App\Services\WebhookIdempotencyService::class)
                ->checkIdempotency('midtrans', $payload);
            $idempotencyKey = $idempotencyResult['idempotency_key'];

            if ($idempotencyResult['is_duplicate']) {
                Log::info('BUG-API-001: Duplicate Midtrans webhook ignored', [
                    'order_id' => $payload['order_id'] ?? 'unknown',
                    'transaction_id' => $payload['transaction_id'] ?? 'unknown',
                ]);

                return [
                    'success' => true,
                    'message' => 'Webhook already processed (duplicate ignored)',
                    'duplicate' => true,
                    'previous_callback_id' => $idempotencyResult['previous_callback']?->id,
                ];
            }

            // Log incoming webhook
            $callback = PaymentCallback::create([
                'tenant_id' => $this->tenantId,
                'gateway_provider' => 'midtrans',
                'event_type' => $payload['transaction_status'] ?? 'notification',
                'payload' => $payload,
                'signature' => $signature,
                'processed' => false,
            ]);

            // Verify signature if webhook secret is configured
            $gateway = TenantPaymentGateway::where('tenant_id', $this->tenantId)
                ->where('provider', 'midtrans')
                ->first();

            if ($gateway && $gateway->webhook_secret) {
                if (!$this->verifyMidtransSignature($payload, $signature, $gateway->webhook_secret)) {
                    $callback->update(['error_message' => 'Invalid signature']);
                    return ['success' => false, 'error' => 'Invalid signature'];
                }
            }

            // Extract transaction data
            $orderId = $payload['order_id'] ?? null;
            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;
            $grossAmount = $payload['gross_amount'] ?? 0;
            $transactionId = $payload['transaction_id'] ?? null;

            if (!$orderId || !$transactionStatus) {
                $callback->update(['error_message' => 'Missing required fields']);
                return ['success' => false, 'error' => 'Missing required fields'];
            }

            // Find payment transaction
            $paymentTransaction = PaymentTransaction::where('tenant_id', $this->tenantId)
                ->where('transaction_number', $orderId)
                ->first();

            if (!$paymentTransaction) {
                $callback->update(['error_message' => 'Transaction not found']);
                return ['success' => false, 'error' => 'Transaction not found'];
            }

            // Process based on transaction status
            DB::transaction(function () use ($paymentTransaction, $transactionStatus, $fraudStatus, $grossAmount, $transactionId, $callback, $payload, $orderId) {
                $newStatus = $this->mapMidtransStatus($transactionStatus, $fraudStatus);

                // Update payment transaction
                $paymentTransaction->update([
                    'gateway_transaction_id' => $transactionId,
                    'status' => $newStatus,
                    'amount' => $grossAmount,
                    'paid_at' => $newStatus === 'success' ? now() : null,
                    'gateway_response' => json_encode($payload),
                ]);

                // If payment successful, update sales order
                if ($newStatus === 'success' && $paymentTransaction->sales_order_id) {
                    $salesOrder = SalesOrder::find($paymentTransaction->sales_order_id);

                    if ($salesOrder) {
                        $salesOrder->update([
                            'status' => 'completed',
                            'payment_type' => 'qris',
                            'payment_method' => 'qris',
                            'paid_amount' => $grossAmount,
                            'completed_at' => now(),
                        ]);

                        // Trigger stock deduction if not already done
                        if (!$salesOrder->stock_deducted_at) {
                            $this->deductStockForOrder($salesOrder);
                        }
                    }
                }

                // Mark callback as processed
                $callback->update([
                    'processed' => true,
                    'processed_at' => now(),
                ]);

                // BUG-API-001 FIX: Mark as processed in idempotency service
                app(\App\Services\WebhookIdempotencyService::class)
                    ->markAsProcessed($idempotencyKey, $callback);

                Log::info("Midtrans webhook processed", [
                    'order_id' => $orderId,
                    'status' => $newStatus,
                    'amount' => $grossAmount,
                ]);
            });

            return ['success' => true, 'message' => 'Webhook processed successfully'];

        } catch (\Exception $e) {
            Log::error("Midtrans webhook error: {$e->getMessage()}", [
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Handle Xendit webhook notification
     */
    public function handleXendit(array $payload, ?string $signature): array
    {
        try {
            // Log incoming webhook
            $callback = PaymentCallback::create([
                'tenant_id' => $this->tenantId,
                'gateway_provider' => 'xendit',
                'event_type' => $payload['status'] ?? 'notification',
                'payload' => $payload,
                'signature' => $signature,
                'processed' => false,
            ]);

            // Verify signature if webhook secret is configured
            $gateway = TenantPaymentGateway::where('tenant_id', $this->tenantId)
                ->where('provider', 'xendit')
                ->first();

            if ($gateway && $gateway->webhook_secret) {
                if (!$this->verifyXenditSignature($payload, $signature, $gateway->webhook_secret)) {
                    $callback->update(['error_message' => 'Invalid signature']);
                    return ['success' => false, 'error' => 'Invalid signature'];
                }
            }

            // Extract transaction data
            $externalId = $payload['external_id'] ?? null;
            $status = $payload['status'] ?? null;
            $paidAmount = $payload['paid_amount'] ?? $payload['amount'] ?? 0;
            $paymentId = $payload['id'] ?? null;
            $paidAt = $payload['paid_at'] ?? null;

            if (!$externalId || !$status) {
                $callback->update(['error_message' => 'Missing required fields']);
                return ['success' => false, 'error' => 'Missing required fields'];
            }

            // Find payment transaction
            $paymentTransaction = PaymentTransaction::where('tenant_id', $this->tenantId)
                ->where('transaction_number', $externalId)
                ->first();

            if (!$paymentTransaction) {
                $callback->update(['error_message' => 'Transaction not found']);
                return ['success' => false, 'error' => 'Transaction not found'];
            }

            // Process based on status
            DB::transaction(function () use ($paymentTransaction, $status, $paidAmount, $paymentId, $paidAt, $callback) {
                $newStatus = $this->mapXenditStatus($status);

                // Update payment transaction
                $updateData = [
                    'gateway_transaction_id' => $paymentId,
                    'status' => $newStatus,
                    'amount' => $paidAmount,
                    'gateway_response' => json_encode($payload),
                ];

                if ($newStatus === 'success' && $paidAt) {
                    $updateData['paid_at'] = $paidAt;
                }

                $paymentTransaction->update($updateData);

                // If payment successful, update sales order
                if ($newStatus === 'success' && $paymentTransaction->sales_order_id) {
                    $salesOrder = SalesOrder::find($paymentTransaction->sales_order_id);

                    if ($salesOrder) {
                        $salesOrder->update([
                            'status' => 'completed',
                            'payment_type' => 'qris',
                            'payment_method' => 'qris',
                            'paid_amount' => $paidAmount,
                            'completed_at' => $paidAt ?? now(),
                        ]);

                        // Trigger stock deduction if not already done
                        if (!$salesOrder->stock_deducted_at) {
                            $this->deductStockForOrder($salesOrder);
                        }
                    }
                }

                // Mark callback as processed
                $callback->update([
                    'processed' => true,
                    'processed_at' => now(),
                ]);

                Log::info("Xendit webhook processed", [
                    'external_id' => $externalId,
                    'status' => $newStatus,
                    'amount' => $paidAmount,
                ]);
            });

            return ['success' => true, 'message' => 'Webhook processed successfully'];

        } catch (\Exception $e) {
            Log::error("Xendit webhook error: {$e->getMessage()}", [
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify Midtrans webhook signature
     */
    private function verifyMidtransSignature(array $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) {
            return false;
        }

        // Create hash from payload
        $hashInput = $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . $secret;
        $expectedSignature = hash('sha512', $hashInput);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Verify Xendit webhook signature
     * Xendit uses x-callback-token header — plain string comparison against configured token
     */
    private function verifyXenditSignature(array $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) {
            return false;
        }

        // Xendit uses x-callback-token: plain comparison (not HMAC)
        return hash_equals($secret, $signature);
    }

    /**
     * Map Midtrans status to internal status
     */
    private function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        // Check fraud status first
        if ($fraudStatus === 'challenge') {
            return 'processing';
        }

        if ($fraudStatus === 'deny') {
            return 'failed';
        }

        // Map transaction status
        return match ($transactionStatus) {
            'capture', 'settlement' => 'success',
            'pending' => 'waiting_payment',
            'deny', 'cancel', 'expire' => 'failed',
            'refund' => 'refund',
            default => 'pending',
        };
    }

    /**
     * Map Xendit status to internal status
     */
    private function mapXenditStatus(string $status): string
    {
        return match ($status) {
            'PAID' => 'success',
            'EXPIRED' => 'expired',
            'FAILED' => 'failed',
            'PENDING' => 'waiting_payment',
            default => 'pending',
        };
    }

    /**
     * Handle Duitku webhook notification
     * Duitku sends: merchantCode, amount, merchantOrderId, productDetail, additionalParam,
     *               paymentCode, resultCode, merchantUserId, reference, signature
     */
    public function handleDuitku(array $payload, ?string $signature): array
    {
        try {
            // Log incoming webhook
            $callback = PaymentCallback::create([
                'tenant_id' => $this->tenantId,
                'gateway_provider' => 'duitku',
                'event_type' => $payload['resultCode'] ?? 'notification',
                'payload' => $payload,
                'signature' => $signature,
                'processed' => false,
            ]);

            // Verify signature if webhook secret is configured
            $gateway = TenantPaymentGateway::where('tenant_id', $this->tenantId)
                ->where('provider', 'duitku')
                ->first();

            if ($gateway && $gateway->webhook_secret) {
                if (!$this->verifyDuitkuSignature($payload, $gateway->webhook_secret)) {
                    $callback->update(['error_message' => 'Invalid signature']);
                    return ['success' => false, 'error' => 'Invalid signature'];
                }
            }

            // Extract transaction data
            $merchantOrderId = $payload['merchantOrderId'] ?? null;
            $resultCode = $payload['resultCode'] ?? null;
            $amount = $payload['amount'] ?? 0;
            $reference = $payload['reference'] ?? null;

            if (!$merchantOrderId || !$resultCode) {
                $callback->update(['error_message' => 'Missing required fields']);
                return ['success' => false, 'error' => 'Missing required fields'];
            }

            // Find payment transaction
            $paymentTransaction = PaymentTransaction::where('tenant_id', $this->tenantId)
                ->where('transaction_number', $merchantOrderId)
                ->first();

            if (!$paymentTransaction) {
                $callback->update(['error_message' => 'Transaction not found']);
                return ['success' => false, 'error' => 'Transaction not found'];
            }

            // Process based on result code
            DB::transaction(function () use ($paymentTransaction, $resultCode, $amount, $reference, $callback, $payload) {
                $newStatus = $this->mapDuitkuStatus($resultCode);

                $updateData = [
                    'gateway_transaction_id' => $reference,
                    'status' => $newStatus,
                    'amount' => $amount,
                    'gateway_response' => json_encode($payload),
                ];

                if ($newStatus === 'success') {
                    $updateData['paid_at'] = now();
                }

                $paymentTransaction->update($updateData);

                // If payment successful, update sales order
                if ($newStatus === 'success' && $paymentTransaction->sales_order_id) {
                    $salesOrder = SalesOrder::find($paymentTransaction->sales_order_id);

                    if ($salesOrder) {
                        $salesOrder->update([
                            'status' => 'completed',
                            'payment_type' => 'qris',
                            'payment_method' => 'qris',
                            'paid_amount' => $amount,
                            'completed_at' => now(),
                        ]);

                        if (!$salesOrder->stock_deducted_at) {
                            $this->deductStockForOrder($salesOrder);
                        }
                    }
                }

                $callback->update([
                    'processed' => true,
                    'processed_at' => now(),
                ]);

                Log::info("Duitku webhook processed", [
                    'merchant_order_id' => $paymentTransaction->transaction_number,
                    'status' => $newStatus,
                    'result_code' => $resultCode,
                ]);
            });

            return ['success' => true, 'message' => 'Webhook processed successfully'];

        } catch (\Exception $e) {
            Log::error("Duitku webhook error: {$e->getMessage()}", [
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Verify Duitku webhook signature
     * Duitku signature: MD5(merchantCode + amount + merchantOrderId + merchantKey)
     */
    private function verifyDuitkuSignature(array $payload, string $merchantKey): bool
    {
        $merchantCode = $payload['merchantCode'] ?? '';
        $amount = $payload['amount'] ?? '';
        $merchantOrderId = $payload['merchantOrderId'] ?? '';

        $expectedSignature = md5($merchantCode . $amount . $merchantOrderId . $merchantKey);

        return hash_equals($expectedSignature, $payload['signature'] ?? '');
    }

    /**
     * Map Duitku result code to internal status
     * 00 = Success, 01 = Pending, 02 = Failed
     */
    private function mapDuitkuStatus(string $resultCode): string
    {
        return match ($resultCode) {
            '00' => 'success',
            '01' => 'waiting_payment',
            '02' => 'failed',
            default => 'pending',
        };
    }

    /**
     * Deduct stock for completed order
     */
    private function deductStockForOrder(SalesOrder $order): void
    {
        foreach ($order->items as $item) {
            // BUG-INV-001 FIX: Lock ALL stock rows and use atomic conditional update
            $stocks = \App\Models\ProductStock::where('product_id', $item->product_id)
                ->where('quantity', '>', 0)
                ->orderBy('quantity', 'desc')
                ->lockForUpdate()
                ->get();

            if ($stocks->isEmpty()) {
                Log::warning("Insufficient stock for product {$item->product_id}");
                continue;
            }

            $totalAvailable = $stocks->sum('quantity');
            if ($totalAvailable < $item->quantity) {
                Log::warning("Insufficient stock for product {$item->product_id}: need {$item->quantity}, have {$totalAvailable}");
                continue;
            }

            // Deduct stock across warehouses atomically
            $remainingToDeduct = $item->quantity;
            foreach ($stocks as $stock) {
                if ($remainingToDeduct <= 0)
                    break;

                $deductFromThis = min($remainingToDeduct, $stock->quantity);
                $before = $stock->quantity;

                // BUG-INV-001 FIX: Atomic update with condition
                $updated = \App\Models\ProductStock::where('id', $stock->id)
                    ->where('quantity', '>=', $deductFromThis)
                    ->decrement('quantity', $deductFromThis);

                if (!$updated) {
                    throw new \Exception("Failed to deduct stock for product {$item->product_id}");
                }

                \App\Models\StockMovement::create([
                    'tenant_id' => $order->tenant_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'user_id' => $order->user_id,
                    'type' => 'out',
                    'quantity' => $deductFromThis,
                    'quantity_before' => $before,
                    'quantity_after' => $before - $deductFromThis,
                    'reference' => $order->number,
                    'notes' => 'Stock deducted via webhook payment completion',
                ]);

                $remainingToDeduct -= $deductFromThis;
            }
        }

        // Mark order as stock deducted
        $order->update(['stock_deducted_at' => now()]);
    }

    /**
     * Retry failed webhook processing
     */
    public function retryFailedCallbacks(int $limit = 10): array
    {
        $failedCallbacks = PaymentCallback::where('tenant_id', $this->tenantId)
            ->where('processed', false)
            ->whereNotNull('error_message')
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $results = [];

        foreach ($failedCallbacks as $callback) {
            try {
                $payload = json_decode($callback->payload, true);
                $result = match ($callback->gateway_provider) {
                    'midtrans' => $this->handleMidtrans($payload, $callback->signature),
                    'xendit' => $this->handleXendit($payload, $callback->signature),
                    'duitku' => $this->handleDuitku($payload, $callback->signature),
                    default => ['success' => false, 'error' => 'Unknown provider'],
                };

                $results[] = [
                    'callback_id' => $callback->id,
                    'success' => $result['success'],
                    'message' => $result['message'] ?? $result['error'] ?? null,
                ];

            } catch (\Exception $e) {
                $results[] = [
                    'callback_id' => $callback->id,
                    'success' => false,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'total_retried' => count($results),
            'results' => $results,
        ];
    }
}
