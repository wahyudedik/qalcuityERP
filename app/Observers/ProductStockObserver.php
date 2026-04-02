<?php

namespace App\Observers;

use App\Models\ProductStock;
use App\Models\EcommerceProductMapping;
use App\Jobs\SyncMarketplaceStock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProductStockObserver
{
    public function updated(ProductStock $stock): void
    {
        // Only trigger if quantity changed
        if (!$stock->isDirty('quantity')) {
            return;
        }

        try {
            // Find all active marketplace mappings for this product
            $channelIds = EcommerceProductMapping::where('product_id', $stock->product_id)
                ->where('is_active', true)
                ->distinct()
                ->pluck('channel_id');

            foreach ($channelIds as $channelId) {
                // Use cache lock to prevent duplicate dispatches within 60 seconds
                $lockKey = "marketplace_stock_sync_{$channelId}_{$stock->product_id}";

                if (Cache::lock($lockKey, 60)->get()) {
                    // Delay 30 seconds to batch nearby changes
                    SyncMarketplaceStock::dispatch(null)
                        ->delay(now()->addSeconds(30));

                    Log::info("Marketplace stock sync queued for channel {$channelId} (product {$stock->product_id})");
                }
            }
        } catch (\Throwable $e) {
            Log::error("ProductStockObserver error: {$e->getMessage()}");
        }
    }
}
