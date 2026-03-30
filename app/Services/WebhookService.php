<?php

namespace App\Services;

use App\Jobs\DispatchWebhookJob;
use App\Models\WebhookDelivery;
use App\Models\WebhookSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * All supported outbound webhook events, grouped by module.
     */
    public const EVENTS = [
        'Sales' => [
            'order.created',
            'order.updated',
            'order.status_changed',
            'order.cancelled',
        ],
        'Invoice' => [
            'invoice.created',
            'invoice.updated',
            'invoice.paid',
            'invoice.overdue',
            'invoice.cancelled',
        ],
        'Customer' => [
            'customer.created',
            'customer.updated',
            'customer.deleted',
        ],
        'Product' => [
            'product.created',
            'product.updated',
            'product.deleted',
            'product.low_stock',
        ],
        'Inventory' => [
            'inventory.adjusted',
            'inventory.transferred',
            'stock.received',
        ],
        'Purchasing' => [
            'purchase.created',
            'purchase.received',
            'purchase.cancelled',
        ],
        'Payment' => [
            'payment.received',
            'payment.refunded',
            'expense.created',
        ],
        'HRM' => [
            'employee.created',
            'employee.updated',
            'payroll.processed',
            'attendance.recorded',
        ],
        'Project' => [
            'project.created',
            'project.updated',
            'task.completed',
        ],
        'System' => [
            'test.ping',
            '*',
        ],
    ];

    /**
     * Flat list of all event names.
     */
    public static function allEventNames(): array
    {
        $events = [];
        foreach (self::EVENTS as $group => $items) {
            foreach ($items as $item) {
                $events[] = $item;
            }
        }
        return $events;
    }

    /**
     * Dispatch an event to all matching subscriptions (async via queue).
     */
    public function dispatch(int $tenantId, string $event, array $payload): void
    {
        $subscriptions = WebhookSubscription::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->get()
            ->filter(fn ($s) => $s->listensTo($event));

        foreach ($subscriptions as $subscription) {
            DispatchWebhookJob::dispatch($subscription, $event, $payload);
        }
    }

    /**
     * Synchronous delivery — used for test pings and backward compat.
     */
    public function deliver(WebhookSubscription $subscription, string $event, array $payload, int $attempt = 1): WebhookDelivery
    {
        $body = json_encode([
            'id'         => \Illuminate\Support\Str::uuid()->toString(),
            'event'      => $event,
            'created_at' => now()->toIso8601String(),
            'tenant_id'  => $subscription->tenant_id,
            'data'       => $payload,
        ], JSON_UNESCAPED_UNICODE);

        $headers = [
            'Content-Type'        => 'application/json',
            'User-Agent'          => 'QalcuityERP-Webhook/1.0',
            'X-Qalcuity-Event'    => $event,
            'X-Qalcuity-Attempt'  => (string) $attempt,
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

            $subscription->update(['last_triggered_at' => now()]);

        } catch (\Throwable $e) {
            $durationMs = (int) ((microtime(true) - $startTime) * 1000);

            $delivery->update([
                'response_body' => substr($e->getMessage(), 0, 2000),
                'status'        => 'failed',
                'duration_ms'   => $durationMs,
            ]);

            Log::warning("Webhook delivery failed [{$event}] to {$subscription->url}: " . $e->getMessage());
        }

        return $delivery;
    }
}
