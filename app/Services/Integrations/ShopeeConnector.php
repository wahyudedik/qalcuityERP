<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Shopee Connector
 * 
 * Handles integration with Shopee Open Platform API
 * Supports OAuth 2.0 authentication
 */
class ShopeeConnector extends BaseConnector
{
    /**
     * Shopee API base URL
     */
    protected string $apiUrl = 'https://partner.shopeemobile.com/api/v2';

    /**
     * Shop ID
     */
    protected ?int $shopId = null;

    /**
     * Partner ID
     */
    protected ?int $partnerId = null;

    /**
     * Partner Key
     */
    protected ?string $partnerKey = null;

    /**
     * Constructor
     */
    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $this->shopId = $integration->getConfigValue('shop_id');
        $this->partnerId = $integration->getConfigValue('partner_id');
        $this->partnerKey = $integration->getConfigValue('partner_key');
    }

    /**
     * Build Shopee API URL with authentication
     */
    protected function buildUrl(string $endpoint): string
    {
        $timestamp = time();
        $sign = $this->generateSign($endpoint, $timestamp);

        return "{$this->apiUrl}{$endpoint}?partner_id={$this->partnerId}&timestamp={$timestamp}&sign={$sign}&shop_id={$this->shopId}";
    }

    /**
     * Generate signature for Shopee API
     */
    protected function generateSign(string $endpoint, int $timestamp): string
    {
        $baseString = $this->partnerId . $endpoint . $timestamp;
        return hash_hmac('sha256', $baseString, $this->partnerKey);
    }

    /**
     * Authenticate with Shopee
     */
    public function authenticate(): bool
    {
        try {
            // Test by getting shop info
            $response = $this->get($this->buildUrl('/shop/get_shop_info'));

            if ($response->successful()) {
                $data = $response->json();

                if (($data['error'] ?? '') === 0) {
                    $this->integration->markAsActive();
                    Log::info('Shopee authentication successful');
                    return true;
                }
            }

            return false;
        } catch (Throwable $e) {
            Log::error('Shopee authentication failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Sync products from ERP to Shopee
     */
    public function syncProducts(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;
        $errors = [];

        try {
            $products = \App\Models\Product::where('tenant_id', $this->integration->tenant_id)
                ->where('is_active', true)
                ->get();

            foreach ($products as $product) {
                try {
                    $shopeeProductId = $this->getMarketplaceProductId($product->id);

                    if ($shopeeProductId) {
                        $result = $this->updateProduct($shopeeProductId, $product);
                    } else {
                        $result = $this->createProduct($product);

                        if ($result['success']) {
                            \App\Models\EcommerceProductMapping::create([
                                'tenant_id' => $this->integration->tenant_id,
                                'product_id' => $product->id,
                                'channel_id' => $this->integration->id,
                                'external_id' => $result['product_id'],
                                'external_sku' => $product->sku,
                                'is_active' => true,
                            ]);
                        }
                    }

                    if ($result['success']) {
                        $processed++;
                    } else {
                        $failed++;
                        $errors[] = $result['error'] ?? 'Unknown error';
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $errors[] = "Product {$product->id}: {$e->getMessage()}";
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->logSync(
                'products',
                'push',
                $failed > 0 ? 'partial' : 'success',
                $processed,
                $failed,
                $failed > 0 ? implode('; ', $errors) : null,
                $duration
            );

            return [
                'success' => $failed === 0,
                'processed' => $processed,
                'failed' => $failed,
                'errors' => $errors,
                'duration' => $duration,
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'syncProducts');
        }
    }

    /**
     * Create product in Shopee
     */
    public function createProduct($product): array
    {
        try {
            $productData = $this->transformProductToShopee($product);

            $response = $this->post($this->buildUrl('/product/add_item'), $productData);

            if ($response->successful()) {
                $data = $response->json();

                if (($data['error'] ?? '') === 0) {
                    return [
                        'success' => true,
                        'product_id' => $data['response']['item_id'] ?? null,
                    ];
                }
            }

            return [
                'success' => false,
                'error' => $data['message'] ?? 'Failed to create product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createProduct');
        }
    }

    /**
     * Update product in Shopee
     */
    public function updateProduct(string $shopeeProductId, $product): array
    {
        try {
            $productData = $this->transformProductToShopee($product);
            $productData['item_id'] = $shopeeProductId;

            $response = $this->post($this->buildUrl('/product/update_item'), $productData);

            if ($response->successful()) {
                $data = $response->json();
                return ['success' => ($data['error'] ?? '') === 0];
            }

            return ['success' => false, 'error' => 'Failed to update product'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateProduct');
        }
    }

    /**
     * Transform ERP product to Shopee format
     */
    protected function transformProductToShopee($product): array
    {
        $stock = \App\Models\ProductStock::where('tenant_id', $this->integration->tenant_id)
            ->where('product_id', $product->id)
            ->first();

        return [
            'name' => $product->name,
            'description' => $product->description ?? '',
            'category_id' => $product->category_id ?? 0,
            'price' => ($product->selling_price ?? 0) * 100000, // Shopee uses smallest currency unit
            'stock' => $stock?->quantity ?? 0,
            'sku' => $product->sku,
            'weight' => ($product->weight ?? 100) / 1000, // kg
            'condition' => 1, // 1 = new
            'status' => 1, // 1 = normal
        ];
    }

    /**
     * Sync orders from Shopee to ERP
     */
    public function syncOrders(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;

        try {
            $response = $this->get($this->buildUrl('/order/get_unfulfilled_order'));

            if ($response->successful()) {
                $data = $response->json();
                $orders = $data['response']['orders'] ?? [];

                foreach ($orders as $shopeeOrder) {
                    try {
                        $exists = \App\Models\SalesOrder::where('tenant_id', $this->integration->tenant_id)
                            ->where('external_id', $shopeeOrder['order_sn'])
                            ->exists();

                        if (!$exists) {
                            $erpOrder = $this->transformOrderFromShopee($shopeeOrder);
                            \App\Models\SalesOrder::create($erpOrder);
                            $processed++;
                        }
                    } catch (Throwable $e) {
                        $failed++;
                    }
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->logSync('orders', 'pull', $failed > 0 ? 'partial' : 'success', $processed, $failed, null, $duration);

            return [
                'success' => $failed === 0,
                'processed' => $processed,
                'failed' => $failed,
                'duration' => $duration,
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'syncOrders');
        }
    }

    /**
     * Transform Shopee order to ERP format
     */
    protected function transformOrderFromShopee(array $order): array
    {
        return [
            'tenant_id' => $this->integration->tenant_id,
            'order_number' => $order['order_sn'],
            'external_id' => $order['order_sn'],
            'order_date' => date('Y-m-d H:i:s', $order['create_time'] ?? time()),
            'status' => 'confirmed',
            'subtotal' => ($order['total_amount'] ?? 0) / 100000,
            'total_amount' => ($order['total_amount'] ?? 0) / 100000,
            'payment_status' => 'paid',
        ];
    }

    /**
     * Sync inventory to Shopee
     */
    public function syncInventory(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;

        try {
            $stocks = \App\Models\ProductStock::where('tenant_id', $this->integration->tenant_id)
                ->where('quantity', '>=', 0)
                ->get();

            foreach ($stocks as $stock) {
                try {
                    $shopeeProductId = $this->getMarketplaceProductId($stock->product_id);

                    if ($shopeeProductId) {
                        $result = $this->updateInventoryLevel($shopeeProductId, $stock->quantity);
                        $result['success'] ? $processed++ : $failed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->logSync('inventory', 'push', $failed > 0 ? 'partial' : 'success', $processed, $failed, null, $duration);

            return [
                'success' => $failed === 0,
                'processed' => $processed,
                'failed' => $failed,
                'duration' => $duration,
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'syncInventory');
        }
    }

    /**
     * Update inventory level in Shopee
     */
    public function updateInventoryLevel(string $shopeeProductId, int $quantity): array
    {
        try {
            $response = $this->post($this->buildUrl('/product/update_stock'), [
                'item_id' => $shopeeProductId,
                'stock_list' => [
                    ['simple_stock' => $quantity],
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return ['success' => ($data['error'] ?? '') === 0];
            }

            return ['success' => false];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateInventoryLevel');
        }
    }

    /**
     * Register webhooks
     */
    public function registerWebhooks(): array
    {
        return ['success' => true, 'registered' => []];
    }

    /**
     * Handle webhook
     */
    public function handleWebhook(array $payload): void
    {
        Log::info('Shopee webhook received', [
            'event' => $payload['data']['msg_type'] ?? 'unknown',
        ]);
    }
}
