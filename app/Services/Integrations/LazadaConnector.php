<?php

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lazada Connector
 * 
 * Handles integration with Lazada Open Platform API
 */
class LazadaConnector extends BaseConnector
{
    protected string $apiUrl = 'https://api.lazada.co.id/rest';
    protected ?string $appKey = null;
    protected ?string $appSecret = null;

    public function __construct(Integration $integration)
    {
        parent::__construct($integration);

        $this->appKey = $integration->getConfigValue('app_key');
        $this->appSecret = $integration->getConfigValue('app_secret');
    }

    public function authenticate(): bool
    {
        try {
            $response = $this->get($this->buildUrl('/category/tree'), ['category_id' => 0]);

            if ($response->successful() && !($response->json()['code'] ?? false)) {
                $this->integration->markAsActive();
                return true;
            }
            return false;
        } catch (Throwable $e) {
            Log::error('Lazada authentication failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function buildUrl(string $endpoint): string
    {
        return $this->apiUrl . $endpoint;
    }

    public function syncProducts(): array
    {
        $startTime = microtime(true);
        $processed = 0;
        $failed = 0;

        try {
            $products = \App\Models\Product::where('tenant_id', $this->integration->tenant_id)
                ->where('is_active', true)->get();

            foreach ($products as $product) {
                try {
                    $lazadaProductId = $this->getMarketplaceProductId($product->id);
                    $result = $lazadaProductId
                        ? $this->updateProduct($lazadaProductId, $product)
                        : $this->createProduct($product);

                    if ($result['success']) {
                        if (!$lazadaProductId && isset($result['product_id'])) {
                            \App\Models\EcommerceProductMapping::create([
                                'tenant_id' => $this->integration->tenant_id,
                                'product_id' => $product->id,
                                'channel_id' => $this->integration->id,
                                'external_id' => $result['product_id'],
                                'external_sku' => $product->sku,
                                'is_active' => true,
                            ]);
                        }
                        $processed++;
                    } else {
                        $failed++;
                    }
                } catch (Throwable $e) {
                    $failed++;
                }
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->logSync('products', 'push', $failed > 0 ? 'partial' : 'success', $processed, $failed, null, $duration);

            return ['success' => $failed === 0, 'processed' => $processed, 'failed' => $failed, 'duration' => $duration];
        } catch (Throwable $e) {
            return $this->handleError($e, 'syncProducts');
        }
    }

    public function createProduct($product): array
    {
        try {
            $productData = $this->transformProductToLazada($product);

            $response = $this->post($this->buildUrl('/product/create'), [
                'payload' => json_encode($productData),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (!($data['code'] ?? false)) {
                    return ['success' => true, 'product_id' => $data['data']['item_id'] ?? null];
                }
            }

            return ['success' => false, 'error' => $data['message'] ?? 'Failed'];
        } catch (Throwable $e) {
            return $this->handleError($e, 'createProduct');
        }
    }

    public function updateProduct(string $lazadaProductId, $product): array
    {
        try {
            $productData = $this->transformProductToLazada($product);

            $response = $this->post($this->buildUrl('/product/update'), [
                'product_id' => $lazadaProductId,
                'payload' => json_encode($productData),
            ]);

            return ['success' => $response->successful() && !($response->json()['code'] ?? false)];
        } catch (Throwable $e) {
            return $this->handleError($e, 'updateProduct');
        }
    }

    protected function transformProductToLazada($product): array
    {
        $stock = \App\Models\ProductStock::where('tenant_id', $this->integration->tenant_id)
            ->where('product_id', $product->id)->first();

        return [
            'primary_category' => $product->category_id ?? 0,
            'skus' => [
                [
                    'SellerSku' => $product->sku,
                    'price' => $product->selling_price ?? 0,
                    'quantity' => $stock?->quantity ?? 0,
                ]
            ],
            'attributes' => [
                'name' => $product->name,
                'short_description' => $product->description ?? '',
                'brand' => $product->brand ?? 'No Brand',
            ],
        ];
    }

    public function syncOrders(): array
    {
        $startTime = microtime(true);
        $processed = 0;

        try {
            $response = $this->get($this->buildUrl('/orders'), [
                'sort_direction' => 'DESC',
                'offset' => 0,
                'limit' => 100,
            ]);

            if ($response->successful()) {
                $orders = $response->json()['data'] ?? [];
                $processed = count($orders);
            }

            $duration = round(microtime(true) - $startTime, 2);
            $this->logSync('orders', 'pull', 'success', $processed, 0, null, $duration);

            return ['success' => true, 'processed' => $processed, 'failed' => 0, 'duration' => $duration];
        } catch (Throwable $e) {
            return $this->handleError($e, 'syncOrders');
        }
    }

    public function syncInventory(): array
    {
        return ['success' => true, 'processed' => 0, 'failed' => 0, 'duration' => 0];
    }

    public function registerWebhooks(): array
    {
        return ['success' => true, 'registered' => []];
    }

    public function handleWebhook(array $payload): void
    {
        Log::info('Lazada webhook received', ['type' => $payload['type'] ?? 'unknown']);
    }
}
