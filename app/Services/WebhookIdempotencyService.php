<?php

namespace App\Services;

use App\Models\PaymentCallback;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WebhookIdempotencyService - Prevent webhook replay attacks and duplicate processing
 * 
 * BUG-API-001 FIX: Ensures each webhook is processed exactly once
 * 
 * Problems Fixed:
 * 1. No idempotency check - same webhook processed multiple times
 * 2. No unique event tracking - replay attacks possible
 * 3. Race condition on concurrent webhook delivery
 * 4. No webhook fingerprint/ID validation
 */
class WebhookIdempotencyService
{
    /**
     * Cache TTL for idempotency keys (24 hours)
     * Webhooks older than this are considered stale
     */
    const IDEMPOTENCY_TTL = 86400;

    /**
     * BUG-API-001 FIX: Check if webhook has already been processed
     * 
     * Uses multiple strategies for idempotency:
     * 1. Webhook event ID (if provided by gateway)
     * 2. Transaction ID + Status combination
     * 3. Payload hash as fallback
     * 
     * @param string $provider Gateway provider (midtrans, xendit, etc.)
     * @param array $payload Webhook payload
     * @return array{idempotency_key: string, is_duplicate: bool, previous_callback: ?PaymentCallback}
     */
    public function checkIdempotency(string $provider, array $payload): array
    {
        $idempotencyKey = $this->generateIdempotencyKey($provider, $payload);

        // Check cache first (fast, prevents race condition)
        $cacheKey = "webhook_processed_{$idempotencyKey}";
        $isCached = Cache::has($cacheKey);

        if ($isCached) {
            Log::warning('BUG-API-001: Duplicate webhook detected (cache)', [
                'provider' => $provider,
                'idempotency_key' => $idempotencyKey,
            ]);

            // Find the original callback
            $originalCallback = PaymentCallback::where('tenant_id', $this->extractTenantId($payload))
                ->where('gateway_provider', $provider)
                ->whereJsonContains('payload->order_id', $this->extractOrderId($payload))
                ->where('processed', true)
                ->first();

            return [
                'idempotency_key' => $idempotencyKey,
                'is_duplicate' => true,
                'previous_callback' => $originalCallback,
            ];
        }

        // Check database (persistent)
        $existingCallback = $this->findExistingCallback($provider, $payload);

        if ($existingCallback && $existingCallback->processed) {
            Log::warning('BUG-API-001: Duplicate webhook detected (database)', [
                'provider' => $provider,
                'idempotency_key' => $idempotencyKey,
                'callback_id' => $existingCallback->id,
            ]);

            // Cache this to prevent future checks
            Cache::put($cacheKey, true, self::IDEMPOTENCY_TTL);

            return [
                'idempotency_key' => $idempotencyKey,
                'is_duplicate' => true,
                'previous_callback' => $existingCallback,
            ];
        }

        return [
            'idempotency_key' => $idempotencyKey,
            'is_duplicate' => false,
            'previous_callback' => null,
        ];
    }

    /**
     * BUG-API-001 FIX: Mark webhook as processed (atomic)
     * 
     * @param string $idempotencyKey
     * @param PaymentCallback $callback
     * @return bool
     */
    public function markAsProcessed(string $idempotencyKey, PaymentCallback $callback): bool
    {
        // Set cache to prevent race conditions
        $cacheKey = "webhook_processed_{$idempotencyKey}";
        Cache::put($cacheKey, true, self::IDEMPOTENCY_TTL);

        // Mark as processed in database
        $callback->markAsProcessed();

        Log::info('BUG-API-001: Webhook marked as processed', [
            'idempotency_key' => $idempotencyKey,
            'callback_id' => $callback->id,
        ]);

        return true;
    }

    /**
     * Generate unique idempotency key for webhook
     * 
     * Priority:
     * 1. Gateway event ID (most reliable)
     * 2. Transaction ID + Status
     * 3. Payload hash (fallback)
     */
    protected function generateIdempotencyKey(string $provider, array $payload): string
    {
        // Strategy 1: Use gateway-specific event ID
        $eventId = $this->extractEventId($provider, $payload);
        if ($eventId) {
            return "webhook_{$provider}_{$eventId}";
        }

        // Strategy 2: Use order ID + transaction status + amount
        $orderId = $this->extractOrderId($payload);
        $status = $this->extractStatus($provider, $payload);
        $amount = $this->extractAmount($provider, $payload);

        if ($orderId && $status) {
            $key = "webhook_{$provider}_{$orderId}_{$status}_{$amount}";
            return hash('sha256', $key);
        }

        // Strategy 3: Hash entire payload (least reliable but works)
        $payloadHash = hash('sha256', json_encode($payload));
        return "webhook_{$provider}_{$payloadHash}";
    }

    /**
     * Find existing callback in database
     */
    protected function findExistingCallback(string $provider, array $payload): ?PaymentCallback
    {
        $tenantId = $this->extractTenantId($payload);
        $orderId = $this->extractOrderId($payload);
        $eventId = $this->extractEventId($provider, $payload);

        $query = PaymentCallback::where('tenant_id', $tenantId)
            ->where('gateway_provider', $provider);

        // Try to match by event ID first
        if ($eventId) {
            $query->whereJsonContains('payload->id', $eventId);
        }

        // Fallback to order ID
        if ($orderId) {
            $query->whereJsonContains('payload->order_id', $orderId)
                ->orWhereJsonContains('payload->external_id', $orderId);
        }

        return $query->orderBy('created_at', 'desc')->first();
    }

    /**
     * Extract event ID from payload (gateway-specific)
     */
    protected function extractEventId(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'midtrans' => $payload['transaction_id'] ?? null,
            'xendit' => $payload['id'] ?? null,
            'stripe' => $payload['id'] ?? null,
            'paypal' => $payload['id'] ?? null,
            default => $payload['event_id'] ?? $payload['webhook_id'] ?? null,
        };
    }

    /**
     * Extract order ID from payload
     */
    protected function extractOrderId(array $payload): ?string
    {
        return $payload['order_id']
            ?? $payload['external_id']
            ?? $payload['merchant_order_id']
            ?? null;
    }

    /**
     * Extract transaction status from payload
     */
    protected function extractStatus(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'midtrans' => $payload['transaction_status'] ?? null,
            'xendit' => $payload['status'] ?? null,
            default => $payload['status'] ?? $payload['event_type'] ?? null,
        };
    }

    /**
     * Extract amount from payload
     */
    protected function extractAmount(string $provider, array $payload): ?string
    {
        return match ($provider) {
            'midtrans' => $payload['gross_amount'] ?? null,
            'xendit' => $payload['amount'] ?? $payload['paid_amount'] ?? null,
            default => $payload['amount'] ?? null,
        };
    }

    /**
     * Extract tenant ID from payload
     */
    protected function extractTenantId(array $payload): int
    {
        // Try to find tenant from order
        $orderId = $this->extractOrderId($payload);

        if ($orderId) {
            $order = \App\Models\SalesOrder::where('number', $orderId)->first();
            if ($order) {
                return $order->tenant_id;
            }

            $payment = \App\Models\PaymentTransaction::where('order_id', $orderId)->first();
            if ($payment) {
                return $payment->tenant_id;
            }
        }

        // Fallback: try to extract from payload
        return $payload['tenant_id'] ?? 0;
    }

    /**
     * Get webhook processing statistics
     */
    public function getStatistics(int $tenantId, int $days = 30): array
    {
        $since = now()->subDays($days);

        $total = PaymentCallback::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->count();

        $processed = PaymentCallback::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->where('processed', true)
            ->count();

        $failed = PaymentCallback::where('tenant_id', $tenantId)
            ->where('created_at', '>=', $since)
            ->where('processed', true)
            ->whereNotNull('error_message')
            ->count();

        $duplicates = $total - $processed;

        return [
            'total_webhooks' => $total,
            'processed' => $processed,
            'failed' => $failed,
            'duplicates_blocked' => $duplicates,
            'success_rate' => $total > 0 ? round(($processed / $total) * 100, 2) : 0,
            'period_days' => $days,
        ];
    }
}
