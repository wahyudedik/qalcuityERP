<?php

namespace App\Services;

use App\Models\EcommerceChannel;
use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcommerceService
{
    public function syncOrders(EcommerceChannel $channel): int
    {
        try {
            $orders = match ($channel->platform) {
                'shopee'    => $this->fetchShopeeOrders($channel),
                'tokopedia' => $this->fetchTokopediaOrders($channel),
                'lazada'    => $this->fetchLazadaOrders($channel),
                default     => [],
            };
        } catch (\Throwable $e) {
            Log::error("EcommerceService sync error [{$channel->platform}]: " . $e->getMessage());
            throw $e;
        }

        $count = 0;
        foreach ($orders as $order) {
            $exists = EcommerceOrder::where('tenant_id', $channel->tenant_id)
                ->where('external_order_id', $order['external_order_id'])
                ->exists();

            if (!$exists) {
                EcommerceOrder::create(array_merge($order, [
                    'tenant_id'  => $channel->tenant_id,
                    'channel_id' => $channel->id,
                    'synced_at'  => now(),
                ]));
                $count++;
            }
        }

        return $count;
    }

    private function fetchShopeeOrders(EcommerceChannel $channel): array
    {
        $partnerId   = $channel->api_key;
        $partnerKey  = $channel->api_secret;
        $shopId      = $channel->shop_id;
        $accessToken = $channel->access_token;

        if (!$partnerId || !$partnerKey || !$shopId || !$accessToken) {
            Log::warning("Shopee: kredensial tidak lengkap untuk channel #{$channel->id}");
            return [];
        }

        $path      = '/api/v2/order/get_order_list';
        $timestamp = time();
        $baseStr   = $partnerId . $path . $timestamp . $accessToken . $shopId;
        $sign      = hash_hmac('sha256', $baseStr, $partnerKey);

        $response = Http::timeout(15)->get('https://partner.shopeemobile.com' . $path, [
            'partner_id'               => (int) $partnerId,
            'timestamp'                => $timestamp,
            'access_token'             => $accessToken,
            'shop_id'                  => (int) $shopId,
            'sign'                     => $sign,
            'time_range_field'         => 'create_time',
            'time_from'                => now()->subDays(7)->timestamp,
            'time_to'                  => now()->timestamp,
            'page_size'                => 50,
            'order_status'             => 'READY_TO_SHIP',
            'response_optional_fields' => 'order_status',
        ]);

        if (!$response->successful()) {
            Log::warning("Shopee API error: " . $response->body());
            return [];
        }

        $orders = [];
        foreach ($response->json('response.order_list') ?? [] as $item) {
            $orders[] = [
                'external_order_id' => (string) $item['order_sn'],
                'customer_name'     => $item['buyer_username'] ?? 'Pembeli Shopee',
                'customer_phone'    => null,
                'items'             => $item['item_list'] ?? [],
                'subtotal'          => (float) ($item['total_amount'] ?? 0),
                'shipping_cost'     => (float) ($item['actual_shipping_fee'] ?? 0),
                'total'             => (float) ($item['total_amount'] ?? 0),
                'status'            => strtolower($item['order_status'] ?? 'pending'),
                'payment_method'    => $item['payment_method'] ?? null,
                'shipping_address'  => $item['recipient_address'] ?? null,
                'courier'           => $item['shipping_carrier'] ?? null,
                'tracking_number'   => $item['tracking_no'] ?? null,
                'ordered_at'        => isset($item['create_time'])
                    ? \Carbon\Carbon::createFromTimestamp($item['create_time'])
                    : now(),
            ];
        }

        return $orders;
    }

    private function fetchTokopediaOrders(EcommerceChannel $channel): array
    {
        $clientId     = $channel->api_key;
        $clientSecret = $channel->api_secret;
        $shopId       = $channel->shop_id;

        if (!$clientId || !$clientSecret || !$shopId) {
            Log::warning("Tokopedia: kredensial tidak lengkap untuk channel #{$channel->id}");
            return [];
        }

        $tokenResponse = Http::timeout(10)
            ->withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://accounts.tokopedia.com/token', ['grant_type' => 'client_credentials']);

        if (!$tokenResponse->successful()) {
            Log::warning("Tokopedia OAuth error: " . $tokenResponse->body());
            return [];
        }

        $accessToken = $tokenResponse->json('access_token');

        $response = Http::timeout(15)
            ->withToken($accessToken)
            ->get("https://fs.tokopedia.net/v1/order/list", [
                'fs_id'     => $clientId,
                'shop_id'   => $shopId,
                'from_date' => now()->subDays(7)->format('Y-m-d'),
                'to_date'   => now()->format('Y-m-d'),
                'page'      => 1,
                'per_page'  => 50,
            ]);

        if (!$response->successful()) {
            Log::warning("Tokopedia order list error: " . $response->body());
            return [];
        }

        $statusMap = [0=>'pending',220=>'payment_verified',400=>'processing',450=>'shipped',500=>'delivered',600=>'completed'];
        $orders = [];

        foreach ($response->json('data') ?? [] as $item) {
            $orders[] = [
                'external_order_id' => (string) ($item['order_id'] ?? $item['invoice_num'] ?? uniqid()),
                'customer_name'     => $item['buyer_info']['buyer_name'] ?? 'Pembeli Tokopedia',
                'customer_phone'    => $item['buyer_info']['buyer_phone'] ?? null,
                'items'             => $item['products'] ?? [],
                'subtotal'          => (float) ($item['total_price'] ?? 0),
                'shipping_cost'     => (float) ($item['shipping_info']['actual_shipping_cost'] ?? 0),
                'total'             => (float) ($item['total_price'] ?? 0),
                'status'            => $statusMap[$item['order_status'] ?? 0] ?? 'pending',
                'payment_method'    => $item['payment_info']['payment_type_name'] ?? null,
                'shipping_address'  => $item['shipping_info']['receiver_address_info'] ?? null,
                'courier'           => $item['shipping_info']['logistic_name'] ?? null,
                'tracking_number'   => $item['shipping_info']['awb_number'] ?? null,
                'ordered_at'        => isset($item['create_time']) ? \Carbon\Carbon::parse($item['create_time']) : now(),
            ];
        }

        return $orders;
    }

    private function fetchLazadaOrders(EcommerceChannel $channel): array
    {
        $appKey      = $channel->api_key;
        $appSecret   = $channel->api_secret;
        $accessToken = $channel->access_token;

        if (!$appKey || !$appSecret || !$accessToken) {
            Log::warning("Lazada: kredensial tidak lengkap untuk channel #{$channel->id}");
            return [];
        }

        $apiPath   = '/orders/get';
        $timestamp = (string) (time() * 1000);
        $params    = [
            'app_key'       => $appKey,
            'timestamp'     => $timestamp,
            'sign_method'   => 'sha256',
            'access_token'  => $accessToken,
            'created_after' => now()->subDays(7)->toIso8601String(),
            'status'        => 'pending',
            'limit'         => 50,
        ];

        ksort($params);
        $paramStr = '';
        foreach ($params as $k => $v) {
            $paramStr .= $k . $v;
        }

        $sign = strtoupper(hash_hmac('sha256', $apiPath . $paramStr, $appSecret));
        $params['sign'] = $sign;

        $response = Http::timeout(15)->get('https://api.lazada.co.id/rest' . $apiPath, $params);

        if (!$response->successful() || ($response->json('code') !== '0' && $response->json('code') !== 0)) {
            Log::warning("Lazada API error: " . $response->body());
            return [];
        }

        $orders = [];
        foreach ($response->json('data.orders') ?? [] as $item) {
            $orders[] = [
                'external_order_id' => (string) ($item['order_id'] ?? uniqid()),
                'customer_name'     => trim(($item['customer_first_name'] ?? '') . ' ' . ($item['customer_last_name'] ?? '')),
                'customer_phone'    => null,
                'items'             => $item['items'] ?? [],
                'subtotal'          => (float) ($item['price'] ?? 0),
                'shipping_cost'     => (float) ($item['shipping_fee'] ?? 0),
                'total'             => (float) ($item['price'] ?? 0),
                'status'            => strtolower($item['status'] ?? 'pending'),
                'payment_method'    => $item['payment_method'] ?? null,
                'shipping_address'  => ['address' => $item['address_shipping']['address1'] ?? null],
                'courier'           => null,
                'tracking_number'   => null,
                'ordered_at'        => isset($item['created_at']) ? \Carbon\Carbon::parse($item['created_at']) : now(),
            ];
        }

        return $orders;
    }
}
