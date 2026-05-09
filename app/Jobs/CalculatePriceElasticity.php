<?php

namespace App\Jobs;

use App\Models\EcommerceOrder;
use App\Models\ProductPriceHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CalculatePriceElasticity implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Find price changes older than 7 days that haven't been calculated yet
        $histories = ProductPriceHistory::where('orders_after_7d', 0)
            ->where('created_at', '<=', now()->subDays(7))
            ->with('product')
            ->cursor();

        foreach ($histories as $history) {
            try {
                if (! $history->product) {
                    continue;
                }

                $startDate = $history->created_at;
                $endDate = $history->created_at->copy()->addDays(7);

                // Count orders containing this product's SKU in the 7 days after price change
                $ordersAfter = EcommerceOrder::where('tenant_id', $history->tenant_id)
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->get()
                    ->filter(function ($order) use ($history) {
                        $items = is_array($order->items) ? $order->items : json_decode($order->items, true) ?? [];
                        foreach ($items as $item) {
                            if (($item['sku'] ?? '') === $history->product->sku) {
                                return true;
                            }
                        }

                        return false;
                    });

                $orderCount = $ordersAfter->count();
                $revenue = $ordersAfter->sum('total');

                $history->update([
                    'orders_after_7d' => $orderCount,
                    'revenue_after_7d' => $revenue,
                ]);

                Log::info("Price elasticity calculated for product #{$history->product_id}: {$orderCount} orders, Rp {$revenue} revenue after price change");
            } catch (\Throwable $e) {
                Log::error("Price elasticity calculation failed for history #{$history->id}: {$e->getMessage()}");
            }
        }
    }
}
