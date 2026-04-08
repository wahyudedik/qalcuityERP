<?php

namespace App\Services\Integrations;

use App\Models\WebhookSubscription;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Webhook Delivery Service
 * 
 * Handles webhook delivery with retry logic,
 * exponential backoff, and delivery tracking.
 */
class WebhookDeliveryService
{
    /**
     * HTTP timeout in seconds
     */
    protected int $timeout = 30;

    /**
     * Maximum retry attempts
     */
    protected int $maxAttempts = 5;

    /**
     * Deliver webhook to subscription
     * 
     * @param WebhookSubscription $subscription
     * @param string $eventType
     * @param array $payload
     * @return WebhookDelivery
     */
    public function deliver(WebhookSubscription $subscription, string $eventType, array $payload): WebhookDelivery
    {
        // Create delivery record
        $delivery = WebhookDelivery::create([
            'subscription_id' => $subscription->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'max_attempts' => $this->maxAttempts,
            'status' => 'pending',
            'attempt_count' => 0,
        ]);

        // Try to deliver immediately
        $this->attemptDelivery($delivery, $subscription);

        return $delivery;
    }

    /**
     * Attempt to deliver webhook
     * 
     * @param WebhookDelivery $delivery
     * @param WebhookSubscription $subscription
     * @return bool
     */
    public function attemptDelivery(WebhookDelivery $delivery, WebhookSubscription $subscription): bool
    {
        try {
            // Check if can retry
            if (!$delivery->canRetry()) {
                $delivery->markAsFailed('Max attempts exceeded');
                return false;
            }

            // Prepare payload
            $payload = $delivery->payload;
            $payloadJson = json_encode($payload);

            // Generate signature
            $signature = $subscription->generateSignature($payloadJson);

            // Make HTTP request
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $delivery->event_type,
                    'X-Webhook-Delivery-Id' => $delivery->id,
                    'User-Agent' => 'QalcuityERP/1.0',
                ])
                ->post($subscription->endpoint_url, $payload);

            // Update response
            $delivery->updateResponse($response->status(), $response->body());

            // Check if successful
            if ($response->successful()) {
                $delivery->markAsDelivered();
                $subscription->markAsTriggered();

                Log::info('Webhook delivered successfully', [
                    'delivery_id' => $delivery->id,
                    'subscription_id' => $subscription->id,
                    'event' => $delivery->event_type,
                    'endpoint' => $subscription->endpoint_url,
                    'response_code' => $response->status(),
                ]);

                return true;
            }

            // Failed delivery - schedule retry
            $delivery->incrementAttempt();

            if ($delivery->canRetry()) {
                $delivery->scheduleRetry($delivery->attempt_count);

                Log::warning('Webhook delivery failed, scheduling retry', [
                    'delivery_id' => $delivery->id,
                    'attempt' => $delivery->attempt_count,
                    'max_attempts' => $delivery->max_attempts,
                    'response_code' => $response->status(),
                    'next_retry' => $delivery->next_retry_at,
                ]);
            } else {
                $delivery->markAsFailed("Failed after {$delivery->attempt_count} attempts. Response: {$response->status()}");

                Log::error('Webhook delivery failed permanently', [
                    'delivery_id' => $delivery->id,
                    'attempts' => $delivery->attempt_count,
                    'response_code' => $response->status(),
                ]);
            }

            return false;
        } catch (Throwable $e) {
            $this->handleDeliveryError($delivery, $e);
            return false;
        }
    }

    /**
     * Retry failed webhook deliveries
     * 
     * @return int Number of deliveries retried
     */
    public function retryFailedDeliveries(): int
    {
        $count = 0;

        // Get deliveries due for retry
        $deliveries = WebhookDelivery::dueForRetry()->get();

        foreach ($deliveries as $delivery) {
            $subscription = $delivery->subscription;

            if ($subscription && $subscription->is_active) {
                $this->attemptDelivery($delivery, $subscription);
                $count++;
            } else {
                $delivery->markAsFailed('Subscription inactive or deleted');
            }
        }

        Log::info('Retried webhook deliveries', [
            'count' => $count,
        ]);

        return $count;
    }

    /**
     * Retry specific delivery
     * 
     * @param WebhookDelivery $delivery
     * @return bool
     */
    public function retryDelivery(WebhookDelivery $delivery): bool
    {
        $subscription = $delivery->subscription;

        if (!$subscription || !$subscription->is_active) {
            Log::warning('Cannot retry webhook: subscription inactive', [
                'delivery_id' => $delivery->id,
            ]);
            return false;
        }

        return $this->attemptDelivery($delivery, $subscription);
    }

    /**
     * Handle delivery error
     * 
     * @param WebhookDelivery $delivery
     * @param Throwable $e
     */
    protected function handleDeliveryError(WebhookDelivery $delivery, Throwable $e): void
    {
        $delivery->incrementAttempt();

        if ($delivery->canRetry()) {
            $delivery->scheduleRetry($delivery->attempt_count);

            Log::error('Webhook delivery error, scheduling retry', [
                'delivery_id' => $delivery->id,
                'attempt' => $delivery->attempt_count,
                'error' => $e->getMessage(),
                'next_retry' => $delivery->next_retry_at,
            ]);
        } else {
            $delivery->markAsFailed($e->getMessage());

            Log::error('Webhook delivery failed permanently', [
                'delivery_id' => $delivery->id,
                'attempts' => $delivery->attempt_count,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get delivery statistics for subscription
     * 
     * @param WebhookSubscription $subscription
     * @return array
     */
    public function getDeliveryStats(WebhookSubscription $subscription): array
    {
        $deliveries = $subscription->deliveries();

        return [
            'total' => $deliveries->count(),
            'delivered' => $deliveries->where('status', 'delivered')->count(),
            'failed' => $deliveries->where('status', 'failed')->count(),
            'pending' => $deliveries->where('status', 'pending')->count(),
            'success_rate' => $deliveries->count() > 0
                ? round(($deliveries->where('status', 'delivered')->count() / $deliveries->count()) * 100, 2)
                : 0,
            'average_response_time' => $this->getAverageResponseTime($subscription),
        ];
    }

    /**
     * Get average response time for subscription
     * 
     * @param WebhookSubscription $subscription
     * @return float
     */
    protected function getAverageResponseTime(WebhookSubscription $subscription): float
    {
        // This would require tracking response times
        // For now, return 0
        return 0;
    }

    /**
     * Clean up old deliveries
     * 
     * @param int $daysOld
     * @return int Number of deliveries deleted
     */
    public function cleanupOldDeliveries(int $daysOld = 30): int
    {
        $count = WebhookDelivery::where('created_at', '<', now()->subDays($daysOld))
            ->where('status', '!=', 'pending')
            ->delete();

        Log::info('Cleaned up old webhook deliveries', [
            'count' => $count,
            'days_old' => $daysOld,
        ]);

        return $count;
    }

    /**
     * Trigger webhook for event
     * 
     * @param string $eventType
     * @param array $payload
     * @param int $tenantId
     * @return array
     */
    public function triggerWebhook(string $eventType, array $payload, int $tenantId): array
    {
        $subscriptions = WebhookSubscription::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereJsonContains('events', $eventType)
            ->get();

        $results = [];

        foreach ($subscriptions as $subscription) {
            $delivery = $this->deliver($subscription, $eventType, $payload);

            $results[] = [
                'subscription_id' => $subscription->id,
                'delivery_id' => $delivery->id,
                'status' => $delivery->status,
                'endpoint' => $subscription->endpoint_url,
            ];
        }

        return $results;
    }

    /**
     * Test webhook delivery
     * 
     * @param WebhookSubscription $subscription
     * @return array
     */
    public function testWebhook(WebhookSubscription $subscription): array
    {
        $testPayload = [
            'event' => 'test.webhook',
            'message' => 'This is a test webhook from QalcuityERP',
            'timestamp' => now()->toISOString(),
            'data' => [
                'test' => true,
            ],
        ];

        $startTime = microtime(true);

        $delivery = $this->deliver($subscription, 'test.webhook', $testPayload);

        $duration = round((microtime(true) - $startTime) * 1000);

        return [
            'success' => $delivery->status === 'delivered',
            'delivery_id' => $delivery->id,
            'status' => $delivery->status,
            'response_code' => $delivery->response_code,
            'duration_ms' => $duration,
            'error' => $delivery->error_message,
        ];
    }

    /**
     * Get recent deliveries
     * 
     * @param int $tenantId
     * @param int $limit
     * @return array
     */
    public function getRecentDeliveries(int $tenantId, int $limit = 50): array
    {
        $deliveries = WebhookDelivery::whereHas('subscription', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })
            ->with('subscription.integration')
            ->latest()
            ->limit($limit)
            ->get();

        return $deliveries->map(function ($delivery) {
            return [
                'id' => $delivery->id,
                'event_type' => $delivery->event_type,
                'status' => $delivery->status,
                'attempts' => $delivery->attempt_count,
                'response_code' => $delivery->response_code,
                'endpoint' => $delivery->subscription->endpoint_url,
                'integration' => $delivery->subscription->integration->name ?? 'Unknown',
                'created_at' => $delivery->created_at->toISOString(),
                'delivered_at' => $delivery->delivered_at?->toISOString(),
            ];
        })->toArray();
    }
}
