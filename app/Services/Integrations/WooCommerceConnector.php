<?php

namespace App\Services\Integrations;

use App\Models\Customer;
use App\Models\EcommerceProductMapping;
use App\Models\Integration;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * WooCommerce Connector
 *
 * Handles integration with WooCommerce REST API v3
 * Supports OAuth 1.0a authentication (Consumer Key/Secret)
 */
class WooCommerceConnector extends BaseConnector
{
    /**
     * WooCommerce store URL
     */
    protected string $storeUrl;

    /**
     * Consumer Key
     */
    protected string $consumerKey;

    /**
     * Consumer Secret
     */
    protected string $consumerSecret;

    /**
     * Constructor
     */
    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $this->storeUrl = rtrim($integration->getConfigValue('store_url'), '/');
        $this->consumerKey = $integration->getConfigValue('consumer_key');
        $this->consumerSecret = $integration->getConfigValue('consumer_secret');
    }

    /**
     * Build WooCommerce API URL with authentication
     */
    protected function buildUrl(string $endpoint): string
    {
        return "{$this->storeUrl}/wp-json/wc/v3/{$endpoint}?consumer_key={$this->consumerKey}&consumer_secret={$this->consumerSecret}";
    }

    // ==========================================
    // Authentication
    // ==========================================

    /**
     * Authenticate with WooCommerce
     */
    public function authenticate(): bool
    {
        try {
            // Test authentication by fetching store info
            $response = $this->get($this->buildUrl(''));

            if ($response->successful()) {
                $this->integration->markAsActive();
                Log::info('WooCommerce authentication successful', [
                    'store' => $this->storeUrl,
                ]);

                return true;
            }

            Log::error('WooCommerce authentication failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Throwable $e) {
            Log::error('WooCommerce authentication error', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    // ==========================================
    // Product Sync
    // ==========================================

    /**
     * Sync products from ERP to WooCommerce
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
                    $wooProductId = $this->getMarketplaceProductId($product->id);

                    if ($wooProductId) {
                        // Update existing product
                        $result = $this->updateProduct($wooProductId, $product);
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
     * Create product in WooCommerce
     */
    public function createProduct($product): array
    {
        try {
            $productData = $this->transformProductToWooCommerce($product);

            $response = $this->post($this->buildUrl('products'), $productData);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'product_id' => $data['id'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to create product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createProduct');
        }
    }

    /**
     * Update product in WooCommerce
     */
    public function updateProduct(string $wooProductId, $product): array
    {
        try {
            $productData = $this->transformProductToWooCommerce($product);

            $response = $this->put($this->buildUrl("products/{$wooProductId}"), $productData);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to update product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateProduct');
        }
    }

    /**
     * Transform ERP product to WooCommerce format
     */
    protected function transformProductToWooCommerce($product): array
    {
        // Get stock for this product
        $stock = ProductStock::where('tenant_id', $this->integration->tenant_id)
            ->where('product_id', $product->id)
            ->first();

        return [
            'name' => $product->name,
            'description' => $product->description ?? '',
            'short_description' => $product->short_description ?? '',
            'regular_price' => number_format($product->selling_price ?? 0, 2, '.', ''),
            'sku' => $product->sku,
            'manage_stock' => true,
            'stock_quantity' => $stock?->quantity ?? 0,
            'stock_status' => ($stock?->quantity ?? 0) > 0 ? 'instock' : 'outofstock',
            'categories' => $this->getProductCategories($product),
            'images' => $this->getProductImages($product),
            'status' => $product->is_active ? 'publish' : 'draft',
        ];
    }

    /**
     * Get product categories for WooCommerce
     */
    protected function getProductCategories($product): array
    {
        if (! $product->category) {
            return [];
        }

        return [
            ['name' => $product->category],
        ];
    }

    /**
     * Get product images for WooCommerce
     */
    protected function getProductImages($product): array
    {
        if (! $product->image_url) {
            return [];
        }

        return [
            ['src' => $product->image_url],
        ];
    }

    // ==========================================
    // Order Sync
    // ==========================================

    /**
     * Sync orders from WooCommerce to ERP
     */
    public function syncOrders(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;
        $errors = [];

        try {
            // Fetch orders from WooCommerce
            $orders = $this->getOrdersFromWooCommerce();

            foreach ($orders as $wooOrder) {
                try {
                    // Check if order already exists
                    $exists = SalesOrder::where('tenant_id', $this->integration->tenant_id)
                        ->where('external_id', $wooOrder['id'])
                        ->exists();

                    if (! $exists) {
                        $erpOrder = $this->transformOrderFromWooCommerce($wooOrder);

                        // Create SalesOrder in ERP
                        SalesOrder::create($erpOrder);
                        $processed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                    $errors[] = "Order {$wooOrder['id']}: {$e->getMessage()}";
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
     * Fetch orders from WooCommerce
     */
    protected function getOrdersFromWooCommerce(): array
    {
        $response = $this->get($this->buildUrl('orders'), [
            'per_page' => 100,
            'status' => 'any',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }

    /**
     * Transform WooCommerce order to ERP format
     */
    protected function transformOrderFromWooCommerce(array $wooOrder): array
    {
        // Find or create customer
        $customer = Customer::firstOrCreate(
            [
                'tenant_id' => $this->integration->tenant_id,
                'email' => $wooOrder['billing']['email'] ?? null,
            ],
            [
                'name' => $wooOrder['billing']['first_name'].' '.$wooOrder['billing']['last_name'],
                'phone' => $wooOrder['billing']['phone'] ?? null,
            ]
        );

        return [
            'tenant_id' => $this->integration->tenant_id,
            'customer_id' => $customer->id,
            'order_number' => $wooOrder['number'] ?? $wooOrder['id'],
            'external_id' => $wooOrder['id'],
            'order_date' => $wooOrder['date_created'],
            'status' => $this->mapWooCommerceOrderStatus($wooOrder['status']),
            'subtotal' => $wooOrder['subtotal'],
            'tax_amount' => $wooOrder['total_tax'] ?? 0,
            'shipping_cost' => $wooOrder['shipping_total'] ?? 0,
            'total_amount' => $wooOrder['total'],
            'payment_status' => $wooOrder['payment_method'],
            'notes' => $wooOrder['customer_note'] ?? null,
        ];
    }

    /**
     * Map WooCommerce order status to ERP status
     */
    protected function mapWooCommerceOrderStatus(string $wooStatus): string
    {
        return match ($wooStatus) {
            'processing', 'completed' => 'confirmed',
            'pending', 'on-hold' => 'pending',
            'cancelled', 'refunded', 'failed' => 'cancelled',
            default => 'pending',
        };
    }

    // ==========================================
    // Inventory Sync
    // ==========================================

    /**
     * Sync inventory levels to WooCommerce
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
                    $wooProductId = $this->getMarketplaceProductId($stock->product_id);

                    if ($wooProductId) {
                        $result = $this->updateInventoryLevel($wooProductId, $stock->quantity);

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
     * Update inventory level in WooCommerce
     */
    public function updateInventoryLevel(string $wooProductId, int $quantity): array
    {
        try {
            $response = $this->put($this->buildUrl("products/{$wooProductId}"), [
                'manage_stock' => true,
                'stock_quantity' => $quantity,
                'stock_status' => $quantity > 0 ? 'instock' : 'outofstock',
            ]);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Failed to update inventory',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateInventoryLevel');
        }
    }

    // ==========================================
    // Webhooks
    // ==========================================

    /**
     * Register webhooks in WooCommerce
     */
    public function registerWebhooks(): array
    {
        $webhooks = [
            ['topic' => 'order.created', 'delivery_url' => config('app.url').'/api/integrations/webhooks/woocommerce'],
            ['topic' => 'order.updated', 'delivery_url' => config('app.url').'/api/integrations/webhooks/woocommerce'],
            ['topic' => 'product.created', 'delivery_url' => config('app.url').'/api/integrations/webhooks/woocommerce'],
            ['topic' => 'product.updated', 'delivery_url' => config('app.url').'/api/integrations/webhooks/woocommerce'],
        ];

        $registered = [];

        foreach ($webhooks as $webhook) {
            $response = $this->post($this->buildUrl('webhooks'), $webhook);

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
     * Handle incoming webhook from WooCommerce
     */
    public function handleWebhook(array $payload): void
    {
        $headers = getallheaders();
        $event = $headers['X-Wc-Webhook-Event'] ?? 'unknown';
        $action = $headers['X-Wc-Webhook-Action'] ?? 'unknown';

        Log::info('WooCommerce webhook received', [
            'event' => $event,
            'action' => $action,
        ]);

        // Process webhook based on event type
        match ($event) {
            'order' => $this->handleOrderWebhook($payload),
            'product' => $this->handleProductWebhook($payload),
            default => Log::warning('Unknown WooCommerce webhook event', ['event' => $event]),
        };
    }

    /**
     * Handle order webhook
     */
    protected function handleOrderWebhook(array $payload): void
    {
        Log::info('WooCommerce order webhook', [
            'order_id' => $payload['id'] ?? null,
            'status' => $payload['status'] ?? null,
        ]);

        // Queue order sync job
        // SyncOrdersJob::dispatch($this->integration);
    }

    /**
     * Handle product webhook
     */
    protected function handleProductWebhook(array $payload): void
    {
        Log::info('WooCommerce product webhook', [
            'product_id' => $payload['id'] ?? null,
            'name' => $payload['name'] ?? null,
        ]);

        // Queue product sync job
        // SyncProductsJob::dispatch($this->integration);
    }

    /**
     * Verify WooCommerce webhook signature
     */
    public function verifyWebhook(string $payload, string $signature): bool
    {
        $secret = $this->integration->getConfigValue('webhook_secret');
        $computedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($computedSignature, $signature);
    }
}
