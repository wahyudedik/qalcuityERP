<?php

namespace App\Jobs;

use App\Models\EcommerceWebhookLog;
use App\Models\EcommerceProductMapping;
use App\Models\ProductStock;
use App\Services\EcommerceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessMarketplaceWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $webhookLogId) {}

    public function handle(): void
    {
        $log = EcommerceWebhookLog::with('channel')->find($this->webhookLogId);

        if (!$log || !$log->channel) {
            Log::warning("Webhook log #{$this->webhookLogId} not found or channel missing");
            return;
        }

        try {
            $eventType = $log->event_type;

            // Route by event type category
            if (str_starts_with($eventType, 'order') || str_contains($eventType, 'order')) {
                $this->handleOrderEvent($log);
            } elseif (str_starts_with($eventType, 'inventory') || str_contains($eventType, 'stock') || str_contains($eventType, 'inventory')) {
                $this->handleInventoryEvent($log);
            } elseif (str_starts_with($eventType, 'product') || str_contains($eventType, 'product') || str_contains($eventType, 'price')) {
                $this->handleProductEvent($log);
            } else {
                Log::info("Unhandled webhook event type: {$eventType} for platform {$log->platform}");
            }

            $log->update(['processed_at' => now()]);
        } catch (\Throwable $e) {
            $log->update(['error_message' => $e->getMessage()]);
            Log::error("Webhook processing failed for log #{$log->id}: {$e->getMessage()}");
        }
    }

    /**
     * Handle order events — trigger immediate order sync
     */
    private function handleOrderEvent(EcommerceWebhookLog $log): void
    {
        $service = app(EcommerceService::class);
        $newOrders = $service->syncOrders($log->channel);
        Log::info("Webhook order sync: {$newOrders} new orders from {$log->platform}");
    }

    /**
     * Handle inventory events — update local stock if mapping exists
     */
    private function handleInventoryEvent(EcommerceWebhookLog $log): void
    {
        $payload = $log->payload;

        // Try to extract SKU and stock from payload (platform-specific)
        $items = $this->extractInventoryItems($log->platform, $payload);

        foreach ($items as $item) {
            $mapping = EcommerceProductMapping::where('channel_id', $log->channel_id)
                ->where('external_sku', $item['sku'])
                ->where('is_active', true)
                ->first();

            if ($mapping) {
                // Log the marketplace stock level (informational)
                Log::info("Marketplace inventory update: SKU {$item['sku']} = {$item['stock']} on {$log->platform}");

                // Note: We do NOT auto-update local ProductStock from marketplace
                // Local stock is the source of truth; marketplace stock is pushed FROM ERP
                // This event is logged for reconciliation purposes
            }
        }
    }

    /**
     * Handle product events — log price/status changes
     */
    private function handleProductEvent(EcommerceWebhookLog $log): void
    {
        Log::info("Product event from {$log->platform}: {$log->event_type}", [
            'channel_id'  => $log->channel_id,
            'payload_keys' => array_keys($log->payload ?? []),
        ]);
    }

    /**
     * Extract inventory items from platform-specific payload
     */
    private function extractInventoryItems(string $platform, array $payload): array
    {
        $items = [];

        switch ($platform) {
            case 'shopee':
                // Shopee: { model_id, stock, item_id }
                if (isset($payload['model_id'])) {
                    $items[] = ['sku' => (string) $payload['model_id'], 'stock' => $payload['stock'] ?? 0];
                }
                foreach ($payload['items'] ?? [] as $item) {
                    $items[] = ['sku' => (string) ($item['model_id'] ?? $item['item_id'] ?? ''), 'stock' => $item['stock'] ?? 0];
                }
                break;

            case 'tokopedia':
                // Tokopedia: { product_id, stock }
                if (isset($payload['product_id'])) {
                    $items[] = ['sku' => (string) $payload['product_id'], 'stock' => $payload['stock'] ?? 0];
                }
                foreach ($payload['products'] ?? [] as $item) {
                    $items[] = ['sku' => (string) ($item['product_id'] ?? ''), 'stock' => $item['stock'] ?? 0];
                }
                break;

            case 'lazada':
                // Lazada: { sku_id, quantity }
                if (isset($payload['sku_id'])) {
                    $items[] = ['sku' => (string) $payload['sku_id'], 'stock' => $payload['quantity'] ?? 0];
                }
                foreach ($payload['skus'] ?? [] as $item) {
                    $items[] = ['sku' => (string) ($item['sku_id'] ?? $item['SellerSku'] ?? ''), 'stock' => $item['quantity'] ?? 0];
                }
                break;
        }

        return array_filter($items, fn($i) => !empty($i['sku']));
    }
}
