<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Tokopedia Connector
 * 
 * Handles integration with Tokopedia Open API
 * Supports OAuth 2.0 authentication
 */
class TokopediaConnector extends BaseConnector
{
    /**
     * Tokopedia API base URL
     */
    protected string $apiUrl = 'https://partners.tokopedia.com/v2';

    /**
     * Shop ID
     */
    protected ?int $shopId = null;

    /**
     * Constructor
     */
    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $this->shopId = $integration->getConfigValue('shop_id');

        $this->httpClient = Http::timeout(30)
            ->retry($this->maxRetries, $this->retryDelay)
            ->withHeaders($this->getTokopediaHeaders());
    }

    /**
     * Get Tokopedia-specific headers
     */
    protected function getTokopediaHeaders(): array
    {
        $headers = parent::getDefaultHeaders();

        $token = $this->integration->getAccessToken();
        if ($token) {
            $headers['Authorization'] = "Bearer {$token}";
        }

        if ($this->shopId) {
            $headers['Shop-Id'] = $this->shopId;
        }

        return $headers;
    }

    /**
     * Authenticate with Tokopedia
     */
    public function authenticate(): bool
    {
        try {
            // Test by getting shop info
            $response = $this->get("{$this->apiUrl}/shop");

            if ($response->successful()) {
                $this->integration->markAsActive();
                Log::info('Tokopedia authentication successful');
                return true;
            }

            return false;
        } catch (Throwable $e) {
            Log::error('Tokopedia authentication failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Sync products from ERP to Tokopedia
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
                    $tokopediaProductId = $this->getMarketplaceProductId($product->id);

                    if ($tokopediaProductId) {
                        $result = $this->updateProduct($tokopediaProductId, $product);
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
     * Create product in Tokopedia
     */
    public function createProduct($product): array
    {
        try {
            $productData = $this->transformProductToTokopedia($product);

            $response = $this->post("{$this->apiUrl}/product", $productData);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'product_id' => $data['data']['id'] ?? null,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error_message'] ?? 'Failed to create product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createProduct');
        }
    }

    /**
     * Update product in Tokopedia
     */
    public function updateProduct(string $tokopediaProductId, $product): array
    {
        try {
            $productData = $this->transformProductToTokopedia($product);

            $response = $this->put("{$this->apiUrl}/product/{$tokopediaProductId}", $productData);

            if ($response->successful()) {
                return ['success' => true];
            }

            return [
                'success' => false,
                'error' => $response->json()['error_message'] ?? 'Failed to update product',
            ];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateProduct');
        }
    }

    /**
     * Transform ERP product to Tokopedia format
     */
    protected function transformProductToTokopedia($product): array
    {
        $stock = \App\Models\ProductStock::where('tenant_id', $this->integration->tenant_id)
            ->where('product_id', $product->id)
            ->first();

        return [
            'name' => $product->name,
            'description' => $product->description ?? '',
            'category_id' => $product->category_id ?? 0,
            'price' => $product->selling_price ?? 0,
            'stock' => $stock?->quantity ?? 0,
            'sku' => $product->sku,
            'weight' => $product->weight ?? 100, // grams
            'condition' => 'new',
            'min_order' => 1,
        ];
    }

    /**
     * Sync orders from Tokopedia to ERP
     */
    public function syncOrders(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;

        try {
            // Fetch orders from Tokopedia
            $response = $this->get("{$this->apiUrl}/orders", [
                'status' => 'paid',
            ]);

            if ($response->successful()) {
                $orders = $response->json()['data'] ?? [];

                foreach ($orders as $tokopediaOrder) {
                    try {
                        $exists = \App\Models\SalesOrder::where('tenant_id', $this->integration->tenant_id)
                            ->where('external_id', $tokopediaOrder['id'])
                            ->exists();

                        if (!$exists) {
                            $erpOrder = $this->transformOrderFromTokopedia($tokopediaOrder);
                            \App\Models\SalesOrder::create($erpOrder);
                            $processed++;
                        }
                    } catch (Throwable $e) {
                        $failed++;
                    }
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->logSync(
                'orders',
                'pull',
                $failed > 0 ? 'partial' : 'success',
                $processed,
                $failed,
                null,
                $duration
            );

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
     * Transform Tokopedia order to ERP format
     */
    protected function transformOrderFromTokopedia(array $order): array
    {
        $customer = \App\Models\Customer::firstOrCreate(
            [
                'tenant_id' => $this->integration->tenant_id,
                'email' => $order['buyer']['email'] ?? null,
            ],
            [
                'name' => $order['buyer']['name'] ?? 'Tokopedia Customer',
                'phone' => $order['buyer']['phone'] ?? null,
            ]
        );

        return [
            'tenant_id' => $this->integration->tenant_id,
            'customer_id' => $customer->id,
            'order_number' => $order['order_number'] ?? $order['id'],
            'external_id' => $order['id'],
            'order_date' => $order['created_at'] ?? now(),
            'status' => 'confirmed',
            'subtotal' => $order['total_amount'] ?? 0,
            'total_amount' => $order['total_amount'] ?? 0,
            'payment_status' => 'paid',
        ];
    }

    /**
     * Sync inventory to Tokopedia
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
                    $tokopediaProductId = $this->getMarketplaceProductId($stock->product_id);

                    if ($tokopediaProductId) {
                        $result = $this->updateInventoryLevel($tokopediaProductId, $stock->quantity);

                        if ($result['success']) {
                            $processed++;
                        } else {
                            $failed++;
                        }
                    }
                } catch (Throwable $e) {
                    $failed++;
                }
            }

            $duration = round(microtime(true) - $startTime, 2);

            $this->logSync(
                'inventory',
                'push',
                $failed > 0 ? 'partial' : 'success',
                $processed,
                $failed,
                null,
                $duration
            );

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
     * Update inventory level in Tokopedia
     */
    public function updateInventoryLevel(string $tokopediaProductId, int $quantity): array
    {
        try {
            $response = $this->put("{$this->apiUrl}/product/{$tokopediaProductId}/stock", [
                'stock' => $quantity,
            ]);

            return ['success' => $response->successful()];
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
        Log::info('Tokopedia webhook received', [
            'event' => $payload['event'] ?? 'unknown',
        ]);
    }
}
