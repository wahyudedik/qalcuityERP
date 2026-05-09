<?php

namespace App\Services\Integrations;

use App\Models\Customer;
use App\Models\EcommerceProductMapping;
use App\Models\Integration;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Shopify Connector
 *
 * Handles integration with Shopify REST Admin API 2024-01
 * Supports OAuth 2.0 authentication
 */
class ShopifyConnector extends BaseConnector
{
    /**
     * Shopify API version
     */
    protected string $apiVersion = '2024-01';

    /**
     * Shopify shop domain
     */
    protected string $shopDomain;

    /**
     * Access token
     */
    protected string $accessToken;

    /**
     * Constructor
     */
    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $this->shopDomain = $integration->getConfigValue('shop_domain');
        $this->accessToken = $integration->getAccessToken() ?? '';

        $this->httpClient = Http::timeout(30)
            ->retry($this->maxRetries, $this->retryDelay)
            ->withHeaders($this->getShopifyHeaders());
    }

    /**
     * Get Shopify-specific headers
     */
    protected function getShopifyHeaders(): array
    {
        return array_merge(parent::getDefaultHeaders(), [
            'X-Shopify-Access-Token' => $this->accessToken,
        ]);
    }

    /**
     * Build Shopify API URL
     */
    protected function buildUrl(string $endpoint): string
    {
        return "https://{$this->shopDomain}/admin/api/{$this->apiVersion}/{$endpoint}";
    }

    // ==========================================
    // Authentication
    // ==========================================

    /**
     * Authenticate with Shopify OAuth
     */
    public function authenticate(): bool
    {
        try {
            // Test authentication by fetching shop info
            $response = $this->get($this->buildUrl('shop.json'));

            if ($response->successful()) {
                $this->integration->markAsActive();
                Log::info('Shopify authentication successful', [
                    'shop' => $this->shopDomain,
                ]);

                return true;
            }

            Log::error('Shopify authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('Shopify authentication error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Start OAuth flow
     */
    public function getAuthUrl(string $clientId, string $redirectUri, string $scopes = 'read_products,write_products,read_orders,write_orders'): string
    {
        $params = [
            'client_id' => $clientId,
            'scope' => $scopes,
            'redirect_uri' => $redirectUri,
            'state' => bin2hex(random_bytes(16)),
            'grant_options[]' => 'per-user',
        ];

        return "https://{$this->shopDomain}/admin/oauth/authorize?".http_build_query($params);
    }

    /**
     * Complete OAuth flow and get access token
     */
    public function completeAuth(string $code, string $clientId, string $clientSecret): bool
    {
        try {
            $response = Http::post("https://{$this->shopDomain}/admin/oauth/access_token", [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $this->integration->update([
                    'oauth_tokens' => [
                        'access_token' => $data['access_token'],
                        'scope' => $data['scope'],
                        'expires_at' => null, // Shopify tokens don't expire
                    ],
                ]);

                $this->accessToken = $data['access_token'];
                $this->integration->markAsActive();

                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::error('Shopify OAuth completion failed', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // ==========================================
    // Product Sync
    // ==========================================

    /**
     * Sync products from ERP to Shopify
     */
    public function syncProducts(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;
        $errors = [];

        try {
            // Get products from ERP
            $products = Product::where('tenant_id', $this->integration->tenant_id)
                ->where('is_active', true)
                ->get();

            foreach ($products as $product) {
                try {
                    $shopifyProductId = $this->getMarketplaceProductId($product->id);

                    if ($shopifyProductId) {
                        // Update existing product
                        $result = $this->updateProduct($shopifyProductId, $product);
                    } else {
                        // Create new product
                        $result = $this->createProduct($product);

                        if ($result['success']) {
                            // Save mapping
                            EcommerceProductMapping::create([
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

            // Log sync
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
     * Create product in Shopify
     */
    public function createProduct($product): array
    {
        try {
            $productData = $this->transformProductToShopify($product);

            $response = $this->post($this->buildUrl('products.json'), [
                'product' => $productData,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'product_id' => $data['product']['id'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['errors'] ?? 'Failed to create product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createProduct');
        }
    }

    /**
     * Update product in Shopify
     */
    public function updateProduct(string $shopifyProductId, $product): array
    {
        try {
            $productData = $this->transformProductToShopify($product);

            $response = $this->put($this->buildUrl("products/{$shopifyProductId}.json"), [
                'product' => $productData,
            ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json()['errors'] ?? 'Failed to update product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateProduct');
        }
    }

    /**
     * Transform ERP product to Shopify format
     */
    protected function transformProductToShopify($product): array
    {
        $variants = [];

        // Get stock for this product
        $stock = ProductStock::where('tenant_id', $this->integration->tenant_id)
            ->where('product_id', $product->id)
            ->first();

        $variants[] = [
            'price' => number_format($product->selling_price ?? 0, 2, '.', ''),
            'sku' => $product->sku,
            'inventory_management' => 'shopify',
            'inventory_quantity' => $stock?->quantity ?? 0,
            'inventory_policy' => 'deny',
        ];

        return [
            'title' => $product->name,
            'body_html' => $product->description ?? '',
            'vendor' => $product->brand ?? 'QalcuityERP',
            'product_type' => $product->category ?? 'General',
            'tags' => $product->tags ?? '',
            'variants' => $variants,
            'status' => $product->is_active ? 'active' : 'draft',
        ];
    }

    // ==========================================
    // Order Sync
    // ==========================================

    /**
     * Sync orders from Shopify to ERP
     */
    public function syncOrders(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;
        $errors = [];

        try {
            // Fetch orders from Shopify
            $orders = $this->getOrdersFromShopify();

            foreach ($orders as $shopifyOrder) {
                try {
                    // Check if order already exists
                    $exists = SalesOrder::where('tenant_id', $this->integration->tenant_id)
                        ->where('external_id', $shopifyOrder['id'])
                        ->exists();

                    if (! $exists) {
                        $erpOrder = $this->transformOrderFromShopify($shopifyOrder);

                        // Create SalesOrder in ERP
                        SalesOrder::create($erpOrder);
                        $processed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $errors[] = "Order {$shopifyOrder['id']}: {$e->getMessage()}";
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            // Log sync
            $this->logSync(
                'orders',
                'pull',
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
            return $this->handleError($e, 'syncOrders');
        }
    }

    /**
     * Fetch orders from Shopify
     */
    protected function getOrdersFromShopify(): array
    {
        $response = $this->get($this->buildUrl('orders.json'), [
            'status' => 'any',
            'limit' => 250,
        ]);

        if ($response->successful()) {
            return $response->json()['orders'] ?? [];
        }

        return [];
    }

    /**
     * Transform Shopify order to ERP format
     */
    protected function transformOrderFromShopify(array $shopifyOrder): array
    {
        // Find or create customer
        $customer = Customer::firstOrCreate(
            [
                'tenant_id' => $this->integration->tenant_id,
                'email' => $shopifyOrder['customer']['email'] ?? null,
            ],
            [
                'name' => $shopifyOrder['customer']['name'] ?? 'Shopify Customer',
                'phone' => $shopifyOrder['customer']['phone'] ?? null,
            ]
        );

        return [
            'tenant_id' => $this->integration->tenant_id,
            'customer_id' => $customer->id,
            'order_number' => $shopifyOrder['name'],
            'external_id' => $shopifyOrder['id'],
            'order_date' => $shopifyOrder['created_at'],
            'status' => $this->mapShopifyOrderStatus($shopifyOrder['financial_status']),
            'subtotal' => $shopifyOrder['subtotal_price'],
            'tax_amount' => $shopifyOrder['total_tax'] ?? 0,
            'shipping_cost' => $shopifyOrder['total_shipping_price_set']['shop_money']['amount'] ?? 0,
            'total_amount' => $shopifyOrder['total_price'],
            'payment_status' => $shopifyOrder['financial_status'],
            'notes' => $shopifyOrder['note'] ?? null,
        ];
    }

    /**
     * Map Shopify order status to ERP status
     */
    protected function mapShopifyOrderStatus(string $shopifyStatus): string
    {
        return match ($shopifyStatus) {
            'paid' => 'confirmed',
            'pending' => 'pending',
            'refunded' => 'cancelled',
            'partially_refunded' => 'completed',
            default => 'pending',
        };
    }

    // ==========================================
    // Inventory Sync
    // ==========================================

    /**
     * Sync inventory levels to Shopify
     */
    public function syncInventory(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;
        $errors = [];

        try {
            // Get all product stocks
            $stocks = ProductStock::where('tenant_id', $this->integration->tenant_id)
                ->where('quantity', '>=', 0)
                ->get();

            foreach ($stocks as $stock) {
                try {
                    $shopifyProductId = $this->getMarketplaceProductId($stock->product_id);

                    if ($shopifyProductId) {
                        $result = $this->updateInventoryLevel($shopifyProductId, $stock->quantity);

                        if ($result['success']) {
                            $processed++;
                        } else {
                            $failed++;
                            $errors[] = $result['error'] ?? 'Unknown error';
                        }
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $errors[] = "Stock {$stock->id}: {$e->getMessage()}";
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            // Log sync
            $this->logSync(
                'inventory',
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
            return $this->handleError($e, 'syncInventory');
        }
    }

    /**
     * Update inventory level in Shopify
     */
    public function updateInventoryLevel(string $shopifyProductId, int $quantity): array
    {
        try {
            $response = $this->post($this->buildUrl('inventory_levels/set.json'), [
                'location_id' => $this->integration->getConfigValue('location_id'),
                'inventory_item_id' => $shopifyProductId,
                'available' => $quantity,
            ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json()['errors'] ?? 'Failed to update inventory',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateInventoryLevel');
        }
    }

    // ==========================================
    // Webhooks
    // ==========================================

    /**
     * Register webhooks in Shopify
     */
    public function registerWebhooks(): array
    {
        $webhooks = [
            ['topic' => 'orders/create', 'address' => config('app.url').'/api/integrations/webhooks/shopify'],
            ['topic' => 'orders/updated', 'address' => config('app.url').'/api/integrations/webhooks/shopify'],
            ['topic' => 'products/create', 'address' => config('app.url').'/api/integrations/webhooks/shopify'],
            ['topic' => 'products/update', 'address' => config('app.url').'/api/integrations/webhooks/shopify'],
        ];

        $registered = [];

        foreach ($webhooks as $webhook) {
            $response = $this->post($this->buildUrl('webhooks.json'), [
                'webhook' => $webhook,
            ]);

            if ($response->successful()) {
                $registered[] = $webhook['topic'];
            }
        }

        return [
            'success' => count($registered) > 0,
            'registered' => $registered,
        ];
    }

    /**
     * Handle incoming webhook from Shopify
     */
    public function handleWebhook(array $payload): void
    {
        $eventType = $payload['topic'] ?? 'unknown';

        Log::info('Shopify webhook received', [
            'event' => $eventType,
            'shop' => $payload['shop'] ?? 'unknown',
        ]);

        // Process webhook based on event type
        match ($eventType) {
            'orders/create', 'orders/updated' => $this->handleOrderWebhook($payload),
            'products/create', 'products/update' => $this->handleProductWebhook($payload),
            default => Log::warning('Unknown Shopify webhook event', ['event' => $eventType]),
        };
    }

    /**
     * Handle order webhook
     */
    protected function handleOrderWebhook(array $payload): void
    {
        $order = $payload['order'] ?? [];

        Log::info('Shopify order webhook', [
            'order_id' => $order['id'] ?? null,
            'order_number' => $order['name'] ?? null,
        ]);

        // Queue order sync job
        // SyncOrdersJob::dispatch($this->integration);
    }

    /**
     * Handle product webhook
     */
    protected function handleProductWebhook(array $payload): void
    {
        $product = $payload['product'] ?? [];

        Log::info('Shopify product webhook', [
            'product_id' => $product['id'] ?? null,
            'title' => $product['title'] ?? null,
        ]);

        // Queue product sync job
        // SyncProductsJob::dispatch($this->integration);
    }

    /**
     * Verify Shopify webhook signature
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $secret = $this->integration->getConfigValue('webhook_secret');
        $computedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computedSignature, $signature);
    }
}
