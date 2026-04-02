<?php

namespace App\Services;

use App\Models\EcommerceChannel;
use App\Models\EcommerceProductMapping;
use App\Models\MarketplaceSyncLog;
use App\Models\ProductStock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * MarketplaceSyncService — Outbound sync (push data TO marketplaces).
 *
 * Supported platforms:
 *   - Shopee (Open Platform API v2, HMAC-SHA256 signed)
 *   - Tokopedia (Fulfillment Service API, OAuth2 Bearer)
 *   - Lazada (Open Platform, Bearer token)
 *
 * Handles stock and price synchronization from ERP to marketplace channels.
 */
class MarketplaceSyncService
{
    // ─── Public API ───────────────────────────────────────────────

    /**
     * Push current stock levels to the given marketplace channel.
     *
     * Iterates all active EcommerceProductMapping records for the channel,
     * sums stock across all warehouses, and calls the platform-specific
     * stock update endpoint in batch.
     *
     * @return array{success: int, failed: int, errors: array<string>}
     */
    public function syncStock(EcommerceChannel $channel): array
    {
        $mappings = EcommerceProductMapping::where('channel_id', $channel->id)
            ->where('is_active', true)
            ->with('product')
            ->get();

        $successCount = 0;
        $failedCount  = 0;
        $errors       = [];

        foreach ($mappings as $mapping) {
            try {
                $qty = ProductStock::where('product_id', $mapping->product_id)->sum('quantity');

                match ($channel->platform) {
                    'shopee'    => $this->pushShopeeStock($channel, $mapping, (int) $qty),
                    'tokopedia' => $this->pushTokopediaStock($channel, $mapping, (int) $qty),
                    'lazada'    => $this->pushLazadaStock($channel, $mapping, (int) $qty),
                    default     => throw new \RuntimeException("Unsupported platform: {$channel->platform}"),
                };

                $mapping->update(['last_stock_sync_at' => now()]);
                $successCount++;

                MarketplaceSyncLog::create([
                    'tenant_id'  => $channel->tenant_id,
                    'channel_id' => $channel->id,
                    'mapping_id' => $mapping->id,
                    'type'       => 'stock',
                    'status'     => 'success',
                    'payload'    => ['external_sku' => $mapping->external_sku, 'value' => $qty],
                ]);
            } catch (\Throwable $e) {
                $failedCount++;
                $msg = "Stock sync failed for mapping #{$mapping->id} (channel #{$channel->id}): {$e->getMessage()}";
                $errors[] = $msg;
                Log::error($msg);

                MarketplaceSyncLog::create([
                    'tenant_id'     => $channel->tenant_id,
                    'channel_id'    => $channel->id,
                    'mapping_id'    => $mapping->id,
                    'type'          => 'stock',
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'attempt_count' => 1,
                    'next_retry_at' => now()->addSeconds(10),
                    'payload'       => ['external_sku' => $mapping->external_sku, 'value' => $qty ?? 0],
                ]);
            }
        }

        return [
            'success' => $successCount,
            'failed'  => $failedCount,
            'errors'  => $errors,
        ];
    }

    /**
     * Push current prices to the given marketplace channel.
     *
     * Uses price_override on the mapping if set, otherwise falls back to
     * the product's price_sell field.
     *
     * @return array{success: int, failed: int, errors: array<string>}
     */
    public function syncPrices(EcommerceChannel $channel): array
    {
        $mappings = EcommerceProductMapping::where('channel_id', $channel->id)
            ->where('is_active', true)
            ->with('product')
            ->get();

        $successCount = 0;
        $failedCount  = 0;
        $errors       = [];

        foreach ($mappings as $mapping) {
            try {
                $price = $mapping->price_override ?? $mapping->product->price_sell;

                match ($channel->platform) {
                    'shopee'    => $this->pushShopeePrice($channel, $mapping, (float) $price),
                    'tokopedia' => $this->pushTokopediaPrice($channel, $mapping, (float) $price),
                    'lazada'    => $this->pushLazadaPrice($channel, $mapping, (float) $price),
                    default     => throw new \RuntimeException("Unsupported platform: {$channel->platform}"),
                };

                $mapping->update(['last_price_sync_at' => now()]);
                $successCount++;

                MarketplaceSyncLog::create([
                    'tenant_id'  => $channel->tenant_id,
                    'channel_id' => $channel->id,
                    'mapping_id' => $mapping->id,
                    'type'       => 'price',
                    'status'     => 'success',
                    'payload'    => ['external_sku' => $mapping->external_sku, 'value' => $price],
                ]);
            } catch (\Throwable $e) {
                $failedCount++;
                $msg = "Price sync failed for mapping #{$mapping->id} (channel #{$channel->id}): {$e->getMessage()}";
                $errors[] = $msg;
                Log::error($msg);

                MarketplaceSyncLog::create([
                    'tenant_id'     => $channel->tenant_id,
                    'channel_id'    => $channel->id,
                    'mapping_id'    => $mapping->id,
                    'type'          => 'price',
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                    'attempt_count' => 1,
                    'next_retry_at' => now()->addSeconds(10),
                    'payload'       => ['external_sku' => $mapping->external_sku, 'value' => $price ?? 0],
                ]);
            }
        }

        return [
            'success' => $successCount,
            'failed'  => $failedCount,
            'errors'  => $errors,
        ];
    }

    // ─── Platform-Specific Stock Pushers ─────────────────────────

    private function pushShopeeStock(EcommerceChannel $channel, EcommerceProductMapping $mapping, int $qty): void
    {
        $body = [
            'item_id'   => (int) $mapping->external_product_id,
            'stock_list' => [
                [
                    'model_id' => 0,
                    'seller_stock' => [['stock' => $qty]],
                ],
            ],
        ];

        $response = $this->buildShopeeRequest($channel, '/api/v2/product/update_stock', $body);

        if (($response['error'] ?? '') !== '') {
            throw new \RuntimeException("Shopee stock update error: " . ($response['message'] ?? json_encode($response)));
        }
    }

    private function pushTokopediaStock(EcommerceChannel $channel, EcommerceProductMapping $mapping, int $qty): void
    {
        $fsId = $channel->api_key;
        $path = "/inventory/v1/fs/{$fsId}/stock/update";

        $body = [
            'data' => [
                [
                    'product_id' => (int) $mapping->external_product_id,
                    'sku'        => $mapping->external_sku,
                    'stock'      => $qty,
                    'warehouse_id' => 0,
                ],
            ],
        ];

        $response = $this->buildTokopediaRequest($channel, $path, $body);

        $status = $response['header']['process_time'] ?? null; // success check by HTTP 200
        // Errors are surfaced via thrown exceptions inside the helper
    }

    private function pushLazadaStock(EcommerceChannel $channel, EcommerceProductMapping $mapping, int $qty): void
    {
        $path = '/rest/product/price-quantity/update';

        $body = [
            'payload' => json_encode([
                'Request' => [
                    'Product' => [
                        'Skus' => [
                            [
                                'ItemId'            => (int) $mapping->external_product_id,
                                'SkuId'             => $mapping->external_sku,
                                'quantity'          => $qty,
                            ],
                        ],
                    ],
                ],
            ]),
        ];

        $response = $this->buildLazadaRequest($channel, $path, $body);

        if (($response['code'] ?? '0') !== '0') {
            throw new \RuntimeException("Lazada stock update error: " . ($response['detail'] ?? json_encode($response)));
        }
    }

    // ─── Platform-Specific Price Pushers ─────────────────────────

    private function pushShopeePrice(EcommerceChannel $channel, EcommerceProductMapping $mapping, float $price): void
    {
        $body = [
            'item_id'    => (int) $mapping->external_product_id,
            'price_list' => [
                [
                    'model_id'      => 0,
                    'original_price' => $price,
                ],
            ],
        ];

        $response = $this->buildShopeeRequest($channel, '/api/v2/product/update_price', $body);

        if (($response['error'] ?? '') !== '') {
            throw new \RuntimeException("Shopee price update error: " . ($response['message'] ?? json_encode($response)));
        }
    }

    private function pushTokopediaPrice(EcommerceChannel $channel, EcommerceProductMapping $mapping, float $price): void
    {
        $fsId = $channel->api_key;
        $path = "/product/v1/fs/{$fsId}/price/update";

        $body = [
            'data' => [
                [
                    'product_id' => (int) $mapping->external_product_id,
                    'sku'        => $mapping->external_sku,
                    'price'      => $price,
                ],
            ],
        ];

        $this->buildTokopediaRequest($channel, $path, $body);
        // Errors are surfaced via thrown exceptions inside the helper
    }

    private function pushLazadaPrice(EcommerceChannel $channel, EcommerceProductMapping $mapping, float $price): void
    {
        $path = '/rest/product/price-quantity/update';

        $body = [
            'payload' => json_encode([
                'Request' => [
                    'Product' => [
                        'Skus' => [
                            [
                                'ItemId'    => (int) $mapping->external_product_id,
                                'SkuId'     => $mapping->external_sku,
                                'price'     => $price,
                            ],
                        ],
                    ],
                ],
            ]),
        ];

        $response = $this->buildLazadaRequest($channel, $path, $body);

        if (($response['code'] ?? '0') !== '0') {
            throw new \RuntimeException("Lazada price update error: " . ($response['detail'] ?? json_encode($response)));
        }
    }

    // ─── Auth / Request Helpers ───────────────────────────────────

    /**
     * Build and execute a signed Shopee Open Platform v2 POST request.
     *
     * Signature pattern (same as EcommerceService):
     *   HMAC-SHA256( partner_id + path + timestamp + access_token + shop_id, partner_key )
     *
     * @throws \RuntimeException on HTTP failure or auth errors
     */
    private function buildShopeeRequest(EcommerceChannel $channel, string $path, array $body): array
    {
        $partnerId   = (int) $channel->api_key;
        $partnerKey  = $channel->api_secret;
        $shopId      = (int) $channel->shop_id;
        $accessToken = $channel->access_token;

        if (!$partnerId || !$partnerKey || !$shopId || !$accessToken) {
            throw new \RuntimeException("Shopee: missing credentials for channel #{$channel->id}");
        }

        $timestamp  = time();
        $baseString = "{$partnerId}{$path}{$timestamp}{$accessToken}{$shopId}";
        $sign       = hash_hmac('sha256', $baseString, $partnerKey);

        $url = 'https://partner.shopeemobile.com' . $path;

        $response = Http::timeout(30)->post($url, array_merge($body, [
            'partner_id'   => $partnerId,
            'timestamp'    => $timestamp,
            'access_token' => $accessToken,
            'shop_id'      => $shopId,
            'sign'         => $sign,
        ]));

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Shopee HTTP {$response->status()} on {$path}: " . $response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Build and execute a Tokopedia Fulfillment Service POST request using OAuth2 Bearer token.
     *
     * If access_token is missing, attempts client-credentials grant (same pattern as EcommerceService).
     *
     * @throws \RuntimeException on HTTP failure or auth errors
     */
    private function buildTokopediaRequest(EcommerceChannel $channel, string $path, array $body): array
    {
        $clientId     = $channel->api_key;
        $clientSecret = $channel->api_secret;

        if (!$clientId || !$clientSecret) {
            throw new \RuntimeException("Tokopedia: missing credentials for channel #{$channel->id}");
        }

        $accessToken = $channel->access_token;

        // Obtain token via client credentials if not cached
        if (!$accessToken) {
            $tokenResponse = Http::withBasicAuth($clientId, $clientSecret)
                ->asForm()
                ->post('https://accounts.tokopedia.com/token', [
                    'grant_type' => 'client_credentials',
                ]);

            if (!$tokenResponse->successful()) {
                throw new \RuntimeException(
                    "Tokopedia token error for channel #{$channel->id}: " . $tokenResponse->body()
                );
            }

            $accessToken = $tokenResponse->json()['access_token'] ?? null;
            if ($accessToken) {
                $channel->update(['access_token' => $accessToken]);
            }
        }

        if (!$accessToken) {
            throw new \RuntimeException("Tokopedia: could not obtain access token for channel #{$channel->id}");
        }

        $url = 'https://fs.tokopedia.net' . $path;

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post($url, $body);

        if ($response->status() === 401) {
            // Token expired — clear so next run re-fetches
            $channel->update(['access_token' => null]);
            throw new \RuntimeException("Tokopedia: access token expired for channel #{$channel->id}");
        }

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Tokopedia HTTP {$response->status()} on {$path}: " . $response->body()
            );
        }

        return $response->json() ?? [];
    }

    /**
     * Build and execute a Lazada Open Platform POST request using Bearer token.
     *
     * @throws \RuntimeException on HTTP failure or auth errors
     */
    private function buildLazadaRequest(EcommerceChannel $channel, string $path, array $body): array
    {
        $appKey      = $channel->api_key;
        $accessToken = $channel->access_token;

        if (!$appKey || !$accessToken) {
            throw new \RuntimeException("Lazada: missing credentials for channel #{$channel->id}");
        }

        $url = 'https://api.lazada.co.id' . $path;

        $response = Http::withToken($accessToken)
            ->timeout(30)
            ->post($url, array_merge($body, [
                'app_key'      => $appKey,
                'access_token' => $accessToken,
            ]));

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Lazada HTTP {$response->status()} on {$path}: " . $response->body()
            );
        }

        return $response->json() ?? [];
    }
}
