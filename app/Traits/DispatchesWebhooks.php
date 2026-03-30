<?php

namespace App\Traits;

use App\Services\WebhookService;

/**
 * Trait for controllers to easily dispatch outbound webhooks.
 *
 * Usage:
 *   $this->fireWebhook('invoice.created', $invoice->toArray());
 */
trait DispatchesWebhooks
{
    protected function fireWebhook(string $event, array $payload, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;

        if (!$tenantId) {
            return;
        }

        app(WebhookService::class)->dispatch($tenantId, $event, $payload);
    }
}
