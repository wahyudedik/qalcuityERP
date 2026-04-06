<?php

namespace App\Services\Integrations;

use App\Models\EcommercePlatform;
use App\Models\EcommerceOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EcommerceIntegrationService
{
    /**
     * Sync orders from Shopify
     */
    public function syncShopifyOrders(int $platformId): array
    {
        $platform = EcommercePlatform::findOrFail($platformId);

        try {
            $response = Http::withHeaders([
                'X-Shopify-Access-Token' => $platform->access_token,
            ])->get("{$platform->store_url}/admin/api/2024-01/orders.json", [
                        'status' => 'any',
                        'limit' => 250,
                    ]);

            if (!$response->ok()) {
                throw new \Exception('Shopify API error: ' . $response->status());
            }

            $orders = $response->json()['orders'] ?? [];
            $synced = 0;

            foreach ($orders as $orderData) {
                $this->saveEcommerceOrder($platform, $orderData);
                $synced++;
            }

            $platform->update([
                'last_order_sync_at' => now(),
                'last_sync_at' => now(),
            ]);

            return ['success' => true, 'synced' => $synced];

        } catch (\Throwable $e) {
            Log::error('Shopify order sync failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync orders from WooCommerce
     */
    public function syncWooCommerceOrders(int $platformId): array
    {
        $platform = EcommercePlatform::findOrFail($platformId);

        try {
            $consumerKey = $platform->api_key;
            $consumerSecret = $platform->api_secret;

            $response = Http::get("{$platform->store_url}/wp-json/wc/v3/orders", [
                'consumer_key' => $consumerKey,
                'consumer_secret' => $consumerSecret,
                'per_page' => 100,
            ]);

            if (!$response->ok()) {
                throw new \Exception('WooCommerce API error');
            }

            $orders = $response->json();
            $synced = 0;

            foreach ($orders as $orderData) {
                $this->saveEcommerceOrder($platform, $orderData);
                $synced++;
            }

            $platform->update(['last_order_sync_at' => now(), 'last_sync_at' => now()]);

            return ['success' => true, 'synced' => $synced];

        } catch (\Throwable $e) {
            Log::error('WooCommerce order sync failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync orders from Tokopedia
     */
    public function syncTokopediaOrders(int $platformId): array
    {
        $platform = EcommercePlatform::findOrFail($platformId);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $platform->access_token,
                'Content-Type' => 'application/json',
            ])->get('https://openapi.tokopedia.com/v1.1/fs/orders', [
                        'shop_id' => $platform->configuration['shop_id'] ?? null,
                        'per_page' => 50,
                    ]);

            if (!$response->ok()) {
                throw new \Exception('Tokopedia API error');
            }

            $orders = $response->json()['data']['orders'] ?? [];
            $synced = 0;

            foreach ($orders as $orderData) {
                $this->saveEcommerceOrder($platform, $orderData);
                $synced++;
            }

            $platform->update(['last_order_sync_at' => now(), 'last_sync_at' => now()]);

            return ['success' => true, 'synced' => $synced];

        } catch (\Throwable $e) {
            Log::error('Tokopedia order sync failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Save e-commerce order to database
     */
    protected function saveEcommerceOrder(EcommercePlatform $platform, array $orderData): void
    {
        EcommerceOrder::updateOrCreate(
            [
                'tenant_id' => $platform->tenant_id,
                'platform_id' => $platform->id,
                'external_order_id' => (string) ($orderData['id'] ?? $orderData['order_number'] ?? ''),
            ],
            [
                'customer_name' => $orderData['customer']['name'] ?? $orderData['billing_address']['first_name'] ?? 'Unknown',
                'customer_email' => $orderData['customer']['email'] ?? $orderData['billing_address']['email'] ?? null,
                'customer_phone' => $orderData['customer']['phone'] ?? $orderData['billing_address']['phone'] ?? null,
                'shipping_address' => json_encode($orderData['shipping_address'] ?? null),
                'subtotal' => $orderData['subtotal_price'] ?? $orderData['total_price'] ?? 0,
                'shipping_cost' => $orderData['total_shipping_price_set']['shop_money']['amount'] ?? 0,
                'total_amount' => $orderData['total_price'] ?? 0,
                'payment_status' => $orderData['financial_status'] ?? $orderData['payment_status'] ?? 'pending',
                'fulfillment_status' => $orderData['fulfillment_status'] ?? 'unfulfilled',
                'line_items' => $orderData['line_items'] ?? [],
                'raw_data' => $orderData,
                'ordered_at' => $orderData['created_at'] ?? now(),
                'synced_at' => now(),
            ]
        );
    }

    /**
     * Sync inventory to e-commerce platform
     */
    public function syncInventory(int $platformId, array $products): array
    {
        $platform = EcommercePlatform::findOrFail($platformId);

        try {
            $synced = 0;

            foreach ($products as $product) {
                match ($platform->platform) {
                    'shopify' => $this->syncShopifyInventory($platform, $product),
                    'woocommerce' => $this->syncWooCommerceInventory($platform, $product),
                    'tokopedia' => $this->syncTokopediaInventory($platform, $product),
                    default => null
                };
                $synced++;
            }

            $platform->update(['last_inventory_sync_at' => now(), 'last_sync_at' => now()]);

            return ['success' => true, 'synced' => $synced];

        } catch (\Throwable $e) {
            Log::error('Inventory sync failed: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Sync single product to Shopify
     */
    protected function syncShopifyInventory(EcommercePlatform $platform, array $product): void
    {
        Http::withHeaders([
            'X-Shopify-Access-Token' => $platform->access_token,
        ])->put("{$platform->store_url}/admin/api/2024-01/products/{$product['shopify_product_id']}.json", [
                    'product' => [
                        'id' => $product['shopify_product_id'],
                        'variants' => [
                            [
                                'id' => $product['shopify_variant_id'],
                                'inventory_quantity' => $product['quantity'],
                            ]
                        ]
                    ]
                ]);
    }

    /**
     * Get pending orders that need fulfillment
     */
    public function getPendingOrders(int $tenantId, int $limit = 50): array
    {
        return EcommerceOrder::where('tenant_id', $tenantId)
            ->where('fulfillment_status', 'unfulfilled')
            ->orderBy('ordered_at', 'asc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get sales statistics
     */
    public function getSalesStats(int $tenantId, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $totalOrders = EcommerceOrder::where('tenant_id', $tenantId)
            ->where('ordered_at', '>=', $startDate)
            ->count();

        $totalRevenue = EcommerceOrder::where('tenant_id', $tenantId)
            ->where('ordered_at', '>=', $startDate)
            ->sum('total_amount');

        $byPlatform = EcommerceOrder::where('tenant_id', $tenantId)
            ->where('ordered_at', '>=', $startDate)
            ->join('ecommerce_platforms', 'ecommerce_orders.platform_id', '=', 'ecommerce_platforms.id')
            ->selectRaw('ecommerce_platforms.platform, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('ecommerce_platforms.platform')
            ->get()
            ->toArray();

        return [
            'total_orders' => $totalOrders,
            'total_revenue' => round($totalRevenue, 2),
            'by_platform' => $byPlatform,
            'period_days' => $days,
        ];
    }
}
