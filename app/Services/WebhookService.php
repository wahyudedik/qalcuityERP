<?php

namespace App\Services;

use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Dispatch an event to all active subscriptions for a tenant.
     */
    public function dispatch(int $tenantId, string $event, array $payload): void
    {
        $subscriptions = WebhookSubscription::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(fn($s) => $s->listensTo($event));

        foreach ($subscriptions as $subscription) {
            $this->deliver($subscription, $event, $payload);
        }
    }

    public function deliver(WebhookSubscription $subscription, string $event, array $payload, int $attempt = 1): void
    {
        $body = json_encode([
            'event'      => $event,
            'payload'    => $payload,
            'timestamp'  => now()->toIso8601String(),
            'tenant_id'  => $subscription->tenant_id,
        ]);

        $headers = [
            'Content-Type'       => 'application/json',
            'X-Qalcuity-Event'   => $event,
            'X-Qalcuity-Attempt' => $attempt,
        ];

        if ($subscription->secret) {
            $headers['X-Qalcuity-Signature'] = 'sha256=' . hash_hmac('sha256', $body, $subscription->secret);
        }

        $delivery = WebhookDelivery::create([
            'webhook_subscription_id' => $subscription->id,
            'event'                   => $event,
            'payload'                 => $payload,
            'status'                  => 'pending',
            'attempt'                 => $attempt,
        ]);

        try {
            $response = Http::withHeaders($headers)
                ->timeout(10)
                ->post($subscription->url, json_decode($body, true));

            $delivery->update([
                'response_code' => $response->status(),
                'response_body' => substr($response->body(), 0, 1000),
                'status'        => $response->successful() ? 'success' : 'failed',
            ]);

            $subscription->update(['last_triggered_at' => now()]);

        } catch (\Throwable $e) {
            $delivery->update([
                'response_body' => $e->getMessage(),
                'status'        => 'failed',
            ]);
            Log::warning("Webhook delivery failed [{$event}] to {$subscription->url}: " . $e->getMessage());
        }
    }
}
