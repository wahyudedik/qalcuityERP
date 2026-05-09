<?php

namespace App\Services\Integrations;

use App\Models\EcommerceProductMapping;
use App\Models\Integration;
use App\Models\IntegrationSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Base Connector for all marketplace integrations
 *
 * All marketplace connectors should extend this class
 * and implement the required abstract methods.
 */
abstract class BaseConnector
{
    /**
     * Integration instance
     */
    protected Integration $integration;

    /**
     * HTTP client instance
     */
    protected $httpClient;

    /**
     * Rate limit settings
     */
    protected int $maxRequestsPerMinute = 60;

    protected int $requestCount = 0;

    protected $lastRequestTime;

    /**
     * Retry settings
     */
    protected int $maxRetries = 3;

    protected int $retryDelay = 1000; // milliseconds

    /**
     * Constructor
     */
    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
        $this->httpClient = Http::timeout(30)
            ->retry($this->maxRetries, $this->retryDelay)
            ->withHeaders($this->getDefaultHeaders());
    }

    /**
     * Get default HTTP headers
     */
    protected function getDefaultHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'QalcuityERP/1.0',
        ];
    }

    // ==========================================
    // ABSTRACT METHODS - Must be implemented by child classes
    // ==========================================

    /**
     * Authenticate with the marketplace
     *
     * @return bool Success status
     */
    abstract public function authenticate(): bool;

    /**
     * Sync products from ERP to marketplace
     *
     * @return array Sync results
     */
    abstract public function syncProducts(): array;

    /**
     * Sync orders from marketplace to ERP
     *
     * @return array Sync results
     */
    abstract public function syncOrders(): array;

    /**
     * Sync inventory levels
     *
     * @return array Sync results
     */
    abstract public function syncInventory(): array;

    /**
     * Register webhooks on marketplace
     *
     * @return array Registered webhooks
     */
    abstract public function registerWebhooks(): array;

    /**
     * Handle incoming webhook from marketplace
     *
     * @param  array  $payload  Webhook payload
     */
    abstract public function handleWebhook(array $payload): void;

    // ==========================================
    // COMMON METHODS - Available to all connectors
    // ==========================================

    /**
     * Make HTTP GET request
     */
    protected function get(string $url, array $params = [])
    {
        $this->checkRateLimit();

        $response = $this->httpClient->get($url, $params);

        $this->trackRequest();
        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Make HTTP POST request
     */
    protected function post(string $url, array $data = [])
    {
        $this->checkRateLimit();

        $response = $this->httpClient->post($url, $data);

        $this->trackRequest();
        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Make HTTP PUT request
     */
    protected function put(string $url, array $data = [])
    {
        $this->checkRateLimit();

        $response = $this->httpClient->put($url, $data);

        $this->trackRequest();
        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Make HTTP DELETE request
     */
    protected function delete(string $url)
    {
        $this->checkRateLimit();

        $response = $this->httpClient->delete($url);

        $this->trackRequest();
        $this->logResponse($url, $response);

        return $response;
    }

    /**
     * Check rate limit before making request
     */
    protected function checkRateLimit(): void
    {
        if ($this->requestCount >= $this->maxRequestsPerMinute) {
            $timeSinceLastRequest = time() - $this->lastRequestTime;

            if ($timeSinceLastRequest < 60) {
                $waitTime = 60 - $timeSinceLastRequest;
                Log::warning("Rate limit reached. Waiting {$waitTime} seconds.");
                sleep($waitTime);

                $this->requestCount = 0;
            }
        }
    }

    /**
     * Track request for rate limiting
     */
    protected function trackRequest(): void
    {
        $this->requestCount++;
        $this->lastRequestTime = time();
    }

    /**
     * Log API response
     */
    protected function logResponse(string $url, $response): void
    {
        Log::info('Integration API Call', [
            'integration' => $this->integration->slug,
            'method' => $response->effectiveUri()->getScheme(),
            'url' => $url,
            'status' => $response->status(),
        ]);
    }

    /**
     * Log sync operation
     */
    protected function logSync(
        string $syncType,
        string $direction,
        string $status,
        int $recordsProcessed = 0,
        int $recordsFailed = 0,
        ?string $errorMessage = null,
        ?int $durationSeconds = null,
        array $details = []
    ): IntegrationSyncLog {
        return IntegrationSyncLog::create([
            'tenant_id' => $this->integration->tenant_id,
            'integration_id' => $this->integration->id,
            'sync_type' => $syncType,
            'direction' => $direction,
            'status' => $status,
            'records_processed' => $recordsProcessed,
            'records_failed' => $recordsFailed,
            'error_message' => $errorMessage,
            'duration_seconds' => $durationSeconds,
            'details' => $details,
        ]);
    }

    /**
     * Handle API errors
     */
    protected function handleError(Throwable $e, string $context = ''): array
    {
        Log::error('Integration API Error', [
            'integration' => $this->integration->slug,
            'context' => $context,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return [
            'success' => false,
            'error' => $e->getMessage(),
            'context' => $context,
        ];
    }

    /**
     * Validate API response
     */
    protected function validateResponse($response, string $context = ''): bool
    {
        if ($response->failed()) {
            Log::error('Integration API Response Failed', [
                'integration' => $this->integration->slug,
                'context' => $context,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Check if integration is connected
     */
    public function isConnected(): bool
    {
        return $this->integration->isConnected();
    }

    /**
     * Get integration instance
     */
    public function getIntegration(): Integration
    {
        return $this->integration;
    }

    /**
     * Test connection
     */
    public function testConnection(): array
    {
        try {
            $startTime = microtime(true);

            // Try to authenticate
            $authenticated = $this->authenticate();

            $duration = round((microtime(true) - $startTime) * 1000);

            return [
                'success' => $authenticated,
                'duration_ms' => $duration,
                'message' => $authenticated ? 'Connection successful' : 'Authentication failed',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Transform product data (ERP → Marketplace format)
     * Override in child class
     */
    protected function transformProduct(array $product): array
    {
        return $product;
    }

    /**
     * Transform order data (Marketplace → ERP format)
     * Override in child class
     */
    protected function transformOrder(array $order): array
    {
        return $order;
    }

    /**
     * Get marketplace product ID from ERP product
     */
    protected function getMarketplaceProductId(int $erpProductId): ?string
    {
        // Check in product mappings
        $mapping = EcommerceProductMapping::where('tenant_id', $this->integration->tenant_id)
            ->where('product_id', $erpProductId)
            ->where('channel_id', $this->integration->id)
            ->first();

        return $mapping?->external_id;
    }

    /**
     * Get ERP product ID from marketplace product
     */
    protected function getErpProductId(string $marketplaceProductId): ?int
    {
        // Check in product mappings
        $mapping = EcommerceProductMapping::where('tenant_id', $this->integration->tenant_id)
            ->where('external_id', $marketplaceProductId)
            ->where('channel_id', $this->integration->id)
            ->first();

        return $mapping?->product_id;
    }
}
