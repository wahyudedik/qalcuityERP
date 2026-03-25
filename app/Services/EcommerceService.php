<?php

namespace App\Services;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * EcommerceService — Sync orders from Indonesian marketplaces.
 *
 * Supported platforms:
 *   - Shopee (Open Platform API v2)
 *   - Tokopedia (Seller API)
 *   - Lazada (Open Platform)
 *
 * Each platform has its own auth flow and order format.
 * This service normalizes them into EcommerceOrder records.
 */
class EcommerceService
{
    /**
     * Sync orders from a marketplace channel.
     * Returns number of new orders imported.
     */
    public function syncOrders(EcommerceChannel $channel): int
    {
        return match ($channel->platform) {
            'shopee'     => $this->syncShopee($channel),
            'tokopedia'  => $this->syncTokopedia($channel),
            'lazada'     => $this->syncLazada($channel),
            default      => 0,
        };
    }

    // ─── Shopee Open Platform API v2 ──────────────────────────────

    private function syncShopee(EcommerceChannel $channel): int
    {
        $baseUrl = 'https://partner.shopeemobile.com/api/v2';
        $partnerId = (int) $channel->api_key;
        $partnerKey = $channel->api_secret;
        $shopId = (int) $channel->shop_id;
        $accessToken = $channel->access_token;

        if (!$partnerId || !$partnerKey || !$shopId || !$accessToken) {
            Log::warning("Shopee sync: missing credentials for channel #{$channel->id}");
            return 0;
        }

        // Build signature: SHA256(partner_id + path + timestamp + access_token + shop_id)
        $timestamp = time();
        $path = '/api/v2/order/get_order_list';
        $baseString = "{$partnerId}{$path}{$timestamp}{$accessToken}{$shopId}";
        $sign = hash_hmac('sha256', $baseString, $partnerKey);

        try {
            // Fetch orders from last 7 days
            $response = Http::timeout(30)->get($baseUrl . '/order/get_order_list', [
                'partner_id'   => $partnerId,
                'timestamp'    => $timestamp,
                'access_token' => $accessToken,
                'shop_id'      => $shopId,
                'sign'         => $sign,
                'time_range_field' => 'create_time',
                'time_from'    => now()->subDays(7)->timestamp,
                'time_to'      => now()->timestamp,
                'page_size'    => 50,
                'order_status' => 'READY_TO_SHIP',
            ]);

            if (!$response->successful()) {
                Log::warning("Shopee API error: " . $response->status() . ' ' . $response->body());
                return 0;
            }

            $data = $response->json();
            $orderList = $data['response']['order_list'] ?? [];

            if (empty($orderList)) return 0;

            // Fetch order details in batch
            $orderIds = collect($orderList)->pluck('order_sn')->implode(',');
            $detailPath = '/api/v2/order/get_order_detail';
            $detailTs = time();
            $detailSign = hash_hmac('sha256', "{$partnerId}{$detailPath}{$detailTs}{$accessToken}{$shopId}", $partnerKey);

            $detailResponse = Http::timeout(30)->get($baseUrl . '/order/get_order_detail', [
                'partner_id'   => $partnerId,
                'timestamp'    => $detailTs,
                'access_token' => $accessToken,
                'shop_id'      => $shopId,
                'sign'         => $detailSign,
                'order_sn_list' => $orderIds,
                'response_optional_fields' => 'buyer_user_id,item_list,recipient_address,total_amount',
            ]);

            $orders = $detailResponse->json()['response']['order_list'] ?? [];

            return $this->importShopeeOrders($channel, $orders);

        } catch (\Throwable $e) {
            Log::error("Shopee sync error: " . $e->getMessage());
            return 0;
        }
    }

    private function importShopeeOrders(EcommerceChannel $channel, array $orders): int
    {
        $imported = 0;

        foreach ($orders as $order) {
            $externalId = $order['order_sn'] ?? null;
            if (!$externalId) continue;

            // Skip if already imported
            if (EcommerceOrder::where('channel_id', $channel->id)->where('external_order_id', $externalId)->exists()) {
                continue;
            }

            $items = collect($order['item_list'] ?? [])->map(fn($item) => [
                'name'     => $item['item_name'] ?? '',
                'sku'      => $item['item_sku'] ?? '',
                'quantity' => $item['model_quantity_purchased'] ?? 1,
                'price'    => ($item['model_discounted_price'] ?? $item['model_original_price'] ?? 0),
            ])->toArray();

            $address = $order['recipient_address'] ?? [];

            EcommerceOrder::create([
                'tenant_id'        => $channel->tenant_id,
                'channel_id'       => $channel->id,
                'external_order_id'=> $externalId,
                'customer_name'    => $address['name'] ?? $order['buyer_username'] ?? 'Shopee Buyer',
                'customer_phone'   => $address['phone'] ?? null,
                'items'            => $items,
                'subtotal'         => (float) ($order['total_amount'] ?? 0),
                'shipping_cost'    => (float) ($order['estimated_shipping_fee'] ?? 0),
                'total'            => (float) ($order['total_amount'] ?? 0),
                'status'           => $this->mapShopeeStatus($order['order_status'] ?? ''),
                'payment_method'   => $order['payment_method'] ?? null,
                'shipping_address' => [
                    'full_address' => $address['full_address'] ?? '',
                    'city'         => $address['city'] ?? '',
                    'state'        => $address['state'] ?? '',
                    'zipcode'      => $address['zipcode'] ?? '',
                ],
                'courier'          => $order['shipping_carrier'] ?? null,
                'tracking_number'  => $order['tracking_no'] ?? null,
                'ordered_at'       => isset($order['create_time']) ? \Carbon\Carbon::createFromTimestamp($order['create_time']) : now(),
                'synced_at'        => now(),
            ]);

            $imported++;
        }

        return $imported;
    }

    private function mapShopeeStatus(string $status): string
    {
        return match ($status) {
            'UNPAID'                => 'pending',
            'READY_TO_SHIP'        => 'confirmed',
            'PROCESSED'            => 'processing',
            'SHIPPED'              => 'shipped',
            'COMPLETED'            => 'completed',
            'IN_CANCEL', 'CANCELLED' => 'cancelled',
            default                => 'pending',
        };
    }

    // ─── Tokopedia Seller API ─────────────────────────────────────

    private function syncTokopedia(EcommerceChannel $channel): int
    {
        $clientId = $channel->api_key;
        $clientSecret = $channel->api_secret;
        $shopId = (int) $channel->shop_id;

        if (!$clientId || !$clientSecret || !$shopId) {
            Log::warning("Tokopedia sync: missing credentials for channel #{$channel->id}");
            return 0;
        }

        try {
            // Step 1: Get access token (client credentials)
            $accessToken = $channel->access_token;
            if (!$accessToken) {
                $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
                    ->asForm()
                    ->post('https://accounts.tokopedia.com/token', [
                        'grant_type' => 'client_credentials',
                    ]);

                if (!$tokenResponse->successful()) {
                    Log::warning("Tokopedia token error: " . $tokenResponse->body());
                    return 0;
                }

                $accessToken = $tokenResponse->json()['access_token'] ?? null;
                if ($accessToken) {
                    $channel->update(['access_token' => $accessToken]);
                }
            }

            if (!$accessToken) return 0;

            // Step 2: Fetch orders
            $fromDate = now()->subDays(7)->timestamp;
            $toDate = now()->timestamp;

            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get("https://fs.tokopedia.net/v2/order/list", [
                    'fs_id'      => $clientId,
                    'shop_id'    => $shopId,
                    'from_date'  => $fromDate,
                    'to_date'    => $toDate,
                    'page'       => 1,
                    'per_page'   => 50,
                ]);

            if (!$response->successful()) {
                // Token might be expired — clear and retry next time
                if ($response->status() === 401) {
                    $channel->update(['access_token' => null]);
                }
                Log::warning("Tokopedia API error: " . $response->status());
                return 0;
            }

            $orders = $response->json()['data'] ?? [];
            return $this->importTokopediaOrders($channel, $orders);

        } catch (\Throwable $e) {
            Log::error("Tokopedia sync error: " . $e->getMessage());
            return 0;
        }
    }

    private function importTokopediaOrders(EcommerceChannel $channel, array $orders): int
    {
        $imported = 0;

        foreach ($orders as $order) {
            $externalId = (string) ($order['order_id'] ?? $order['invoice_ref_num'] ?? null);
            if (!$externalId) continue;

            if (EcommerceOrder::where('channel_id', $channel->id)->where('external_order_id', $externalId)->exists()) {
                continue;
            }

            $items = collect($order['products'] ?? $order['order_detail'] ?? [])->map(fn($item) => [
                'name'     => $item['name'] ?? $item['product_name'] ?? '',
                'sku'      => $item['sku'] ?? '',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'price'    => (float) ($item['price'] ?? 0),
            ])->toArray();

            $buyer = $order['buyer'] ?? [];
            $destination = $order['destination'] ?? $order['receiver'] ?? [];

            EcommerceOrder::create([
                'tenant_id'        => $channel->tenant_id,
                'channel_id'       => $channel->id,
                'external_order_id'=> $externalId,
                'customer_name'    => $destination['receiver_name'] ?? $buyer['name'] ?? 'Tokopedia Buyer',
                'customer_phone'   => $destination['receiver_phone'] ?? $buyer['phone'] ?? null,
                'items'            => $items,
                'subtotal'         => (float) ($order['amt']['ttl_product_price'] ?? $order['payment_amount'] ?? 0),
                'shipping_cost'    => (float) ($order['amt']['shipping_cost'] ?? 0),
                'total'            => (float) ($order['amt']['ttl_amount'] ?? $order['payment_amount'] ?? 0),
                'status'           => $this->mapTokopediaStatus((int) ($order['order_status'] ?? 0)),
                'payment_method'   => $order['payment_info']['gateway'] ?? null,
                'shipping_address' => [
                    'full_address' => $destination['address_full'] ?? '',
                    'city'         => $destination['address_city'] ?? '',
                    'district'     => $destination['address_district'] ?? '',
                    'postal_code'  => $destination['address_postal'] ?? '',
                ],
                'courier'          => $order['logistics']['shipping_agency'] ?? null,
                'tracking_number'  => $order['logistics']['awb'] ?? null,
                'ordered_at'       => isset($order['create_time']) ? \Carbon\Carbon::parse($order['create_time']) : now(),
                'synced_at'        => now(),
            ]);

            $imported++;
        }

        return $imported;
    }

    private function mapTokopediaStatus(int $status): string
    {
        // Tokopedia order status codes
        return match ($status) {
            0, 3     => 'pending',     // new order
            220      => 'confirmed',   // seller accepted
            400      => 'processing',  // in process
            500      => 'shipped',     // shipped
            600, 601 => 'completed',   // delivered
            10, 15   => 'cancelled',   // cancelled/rejected
            default  => 'pending',
        };
    }

    // ─── Lazada Open Platform ─────────────────────────────────────

    private function syncLazada(EcommerceChannel $channel): int
    {
        $appKey = $channel->api_key;
        $appSecret = $channel->api_secret;
        $accessToken = $channel->access_token;

        if (!$appKey || !$appSecret || !$accessToken) {
            Log::warning("Lazada sync: missing credentials for channel #{$channel->id}");
            return 0;
        }

        try {
            $response = Http::withToken($accessToken)
                ->timeout(30)
                ->get('https://api.lazada.co.id/rest/orders/get', [
                    'app_key'      => $appKey,
                    'access_token' => $accessToken,
                    'created_after'=> now()->subDays(7)->toIso8601String(),
                    'limit'        => 50,
                    'sort_by'      => 'created_at',
                    'sort_direction'=> 'DESC',
                ]);

            if (!$response->successful()) {
                Log::warning("Lazada API error: " . $response->status());
                return 0;
            }

            $orders = $response->json()['data']['orders'] ?? [];
            $imported = 0;

            foreach ($orders as $order) {
                $externalId = (string) ($order['order_id'] ?? null);
                if (!$externalId) continue;

                if (EcommerceOrder::where('channel_id', $channel->id)->where('external_order_id', $externalId)->exists()) {
                    continue;
                }

                $items = collect($order['order_items'] ?? [])->map(fn($item) => [
                    'name'     => $item['name'] ?? '',
                    'sku'      => $item['sku'] ?? '',
                    'quantity' => 1,
                    'price'    => (float) ($item['paid_price'] ?? 0),
                ])->toArray();

                $address = $order['address_shipping'] ?? [];

                EcommerceOrder::create([
                    'tenant_id'        => $channel->tenant_id,
                    'channel_id'       => $channel->id,
                    'external_order_id'=> $externalId,
                    'customer_name'    => $address['first_name'] . ' ' . ($address['last_name'] ?? ''),
                    'customer_phone'   => $address['phone'] ?? null,
                    'items'            => $items,
                    'subtotal'         => (float) ($order['price'] ?? 0),
                    'shipping_cost'    => (float) ($order['shipping_fee'] ?? 0),
                    'total'            => (float) ($order['price'] ?? 0),
                    'status'           => $this->mapLazadaStatus($order['statuses'][0] ?? ''),
                    'payment_method'   => $order['payment_method'] ?? null,
                    'shipping_address' => [
                        'full_address' => ($address['address1'] ?? '') . ' ' . ($address['address2'] ?? ''),
                        'city'         => $address['city'] ?? '',
                    ],
                    'ordered_at'       => isset($order['created_at']) ? \Carbon\Carbon::parse($order['created_at']) : now(),
                    'synced_at'        => now(),
                ]);

                $imported++;
            }

            return $imported;

        } catch (\Throwable $e) {
            Log::error("Lazada sync error: " . $e->getMessage());
            return 0;
        }
    }

    private function mapLazadaStatus(string $status): string
    {
        return match (strtolower($status)) {
            'pending'    => 'pending',
            'ready_to_ship', 'packed' => 'confirmed',
            'shipped'    => 'shipped',
            'delivered'  => 'completed',
            'canceled', 'cancelled' => 'cancelled',
            default      => 'pending',
        };
    }
}
