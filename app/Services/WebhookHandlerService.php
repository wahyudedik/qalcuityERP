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
            // Log incoming webhook
            $callback = PaymentCallback::create([
                'tenant_id' => $this->tenantId,
                'provider' => 'midtrans',
                'payload' => json_encode($payload),
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
            DB::transaction(function () use ($paymentTransaction, $transactionStatus, $fraudStatus, $grossAmount, $transactionId, $callback) {
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
                'provider' => 'xendit',
                'payload' => json_encode($payload),
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
     */
    private function verifyXenditSignature(array $payload, ?string $signature, string $secret): bool
    {
        if (!$signature) {
            return false;
        }

        // Xendit uses HMAC SHA256
        $payloadJson = json_encode($payload);
        $expectedSignature = hash_hmac('sha256', $payloadJson, $secret);

        return hash_equals($expectedSignature, $signature);
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
     * Deduct stock for completed order
     */
    private function deductStockForOrder(SalesOrder $order): void
    {
        foreach ($order->items as $item) {
            $stock = \App\Models\ProductStock::where('product_id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if ($stock) {
                if ($stock->quantity < $item->quantity) {
                    Log::warning("Insufficient stock for product {$item->product_id}");
                    continue;
                }

                $before = $stock->quantity;
                $stock->decrement('quantity', $item->quantity);

                \App\Models\StockMovement::create([
                    'tenant_id' => $order->tenant_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $stock->warehouse_id,
                    'user_id' => $order->user_id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'quantity_before' => $before,
                    'quantity_after' => $before - $item->quantity,
                    'reference' => $order->number,
                    'notes' => 'Stock deducted via webhook payment completion',
                ]);
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
                $result = match ($callback->provider) {
                    'midtrans' => $this->handleMidtrans($payload, $callback->signature),
                    'xendit' => $this->handleXendit($payload, $callback->signature),
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
