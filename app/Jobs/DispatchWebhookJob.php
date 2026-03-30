<?php

namespace App\Jobs;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DispatchWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;
    public int $maxExceptions = 5;

    public function __construct(
        public WebhookSubscription $subscription,
        public string $event,
        public array $payload,
        public int $attempt = 1,
    ) {
        $this->queue = 'webhooks';
    }

    /**
     * Exponential backoff: 10s, 30s, 90s, 270s, 810s
     */
    public function backoff(): array
    {
        return [10, 30, 90, 270, 810];
    }

    public function handle(): void
    {
        $subscription = $this->subscription;

        if (!$subscription->is_active) {
            return;
        }

        $body = json_encode([
            'id'         => \Illuminate\Support\Str::uuid()->toString(),
            'event'      => $this->event,
            'created_at' => now()->toIso8601String(),
            'tenant_id'  => $subscription->tenant_id,
            'data'       => $this->payload,
        ], JSON_UNESCAPED_UNICODE);

        $headers = [
            'Content-Type'        => 'application/json',
            'User-Agent'          => 'QalcuityERP-Webhook/1.0',
            'X-Qalcuity-Event'    => $this->event,
            'X-Qalcuity-Delivery' => \Illuminate\Support\Str::uuid()->toString(),
            'X-Qalcuity-Attempt'  => (string) $this->attempt,
        ];

        if ($subscription->secret) {
            $headers['X-Qalcuity-Signature'] = 'sha256=' . hash_hmac('sha256', $body, $subscription->secret);
        }

        $delivery = WebhookDelivery::create([
            'webhook_subscription_id' => $subscription->id,
            'event'                   => $this->event,
            'payload'                 => $this->payload,
            'status'                  => 'pending',
            'attempt'                 => $this->attempt,
        ]);

        $startTime = microtime(true);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->connectTimeout(5)
                ->post($subscription->url, json_decode($body, true));

            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $delivery->update([
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 2000),
                'status'        => $response->successful() ? 'success' : 'failed',
                'duration_ms'   => $durationMs,
            ]);

            $subscription->update([
                'last_triggered_at' => now(),
                'retry_count'       => 0,
            ]);

            // If non-2xx, throw to trigger retry
            if (!$response->successful()) {
                $this->handleFailure($subscription, $delivery);
            }

        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $delivery->update([
                'response_body' => substr($e->getMessage(), 0, 2000),
                'status'        => 'failed',
                'duration_ms'   => $durationMs,
            ]);

            $this->handleFailure($subscription, $delivery);

            Log::warning("Webhook delivery failed [{$this->event}] to {$subscription->url}: {$e->getMessage()}");

            throw $e; // Let the queue retry
        }
    }

    private function handleFailure(WebhookSubscription $subscription, WebhookDelivery $delivery): void
    {
        $subscription->increment('retry_count');

        // Auto-disable after 50 consecutive failures
        if ($subscription->retry_count >= 50) {
            $subscription->update(['is_active' => false]);
            Log::warning("Webhook subscription #{$subscription->id} auto-disabled after 50 failures");
        }
    }
}
