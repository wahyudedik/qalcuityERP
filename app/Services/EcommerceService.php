<?php

namespace App\Services;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;

class EcommerceService
{
    /**
     * Sync orders from a channel. Returns count of new orders.
     * In production, replace stubs with real API calls per platform.
     */
    public function syncOrders(EcommerceChannel $channel): int
    {
        $orders = match($channel->platform) {
            'shopee'    => $this->fetchShopeeOrders($channel),
            'tokopedia' => $this->fetchTokopediaOrders($channel),
            'lazada'    => $this->fetchLazadaOrders($channel),
            default     => [],
        };

        $count = 0;
        foreach ($orders as $order) {
            $exists = EcommerceOrder::where('tenant_id', $channel->tenant_id)
                ->where('external_order_id', $order['external_order_id'])
                ->exists();

            if (!$exists) {
                EcommerceOrder::create(array_merge($order, [
                    'tenant_id'  => $channel->tenant_id,
                    'channel_id' => $channel->id,
                ]));
                $count++;
            }
        }

        return $count;
    }

    // ── Platform stubs ──────────────────────────────────────────────────────

    private function fetchShopeeOrders(EcommerceChannel $channel): array
    {
        // TODO: Implement Shopee Open Platform API
        // https://open.shopee.com/documents
        return [];
    }

    private function fetchTokopediaOrders(EcommerceChannel $channel): array
    {
        // TODO: Implement Tokopedia API
        // https://developer.tokopedia.com
        return [];
    }

    private function fetchLazadaOrders(EcommerceChannel $channel): array
    {
        // TODO: Implement Lazada Open Platform API
        // https://open.lazada.com
        return [];
    }
}
