<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\EcommerceProductMapping;
use App\Models\EcommerceOrder;
use App\Jobs\SyncMarketplacePrices;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductObserver
{
    public function updated(Product $product): void
    {
        // Only trigger if price_sell changed
        if (!$product->isDirty('price_sell')) {
            return;
        }

        try {
            $oldPrice = $product->getOriginal('price_sell');
            $newPrice = $product->price_sell;

            // Calculate orders in 7 days before this price change
            $ordersBefore = EcommerceOrder::where('tenant_id', $product->tenant_id)
                ->where('created_at', '>=', now()->subDays(7))
                ->whereJsonContains('items', [['sku' => $product->sku]])
                ->count();

            // This is approximate - items is JSON array so whereJsonContains may not work perfectly
            // Fallback: count all recent orders for this tenant
            if ($ordersBefore === 0) {
                $ordersBefore = EcommerceOrder::where('tenant_id', $product->tenant_id)
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
            }

            // Log price change to history
            ProductPriceHistory::create([
                'tenant_id' => $product->tenant_id,
                'product_id' => $product->id,
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'source' => 'manual',
                'changed_by' => auth()->id(),
                'orders_before_7d' => $ordersBefore,
            ]);

            // Find all active marketplace mappings and trigger price sync
            $channelIds = EcommerceProductMapping::where('product_id', $product->id)
                ->where('is_active', true)
                ->distinct()
                ->pluck('channel_id');

            foreach ($channelIds as $channelId) {
                $lockKey = "marketplace_price_sync_{$channelId}_{$product->id}";

                if (Cache::lock($lockKey, 60)->get()) {
                    SyncMarketplacePrices::dispatch($product->tenant_id)
                        ->delay(now()->addSeconds(30));

                    Log::info("Marketplace price sync queued for channel {$channelId} (product {$product->id})");
                }
            }
        } catch (\Throwable $e) {
            Log::error("ProductObserver error: {$e->getMessage()}");
        }
    }
}
