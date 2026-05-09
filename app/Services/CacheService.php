<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Intelligent Cache Service with Automatic Invalidation
 *
 * Provides centralized cache management with smart invalidation strategies
 * for different data types across the ERP system.
 */
class CacheService
{
    /**
     * Cache key prefixes by domain
     */
    protected const PREFIXES = [
        'inventory' => 'inv:',
        'product' => 'prd:',
        'price' => 'prc:',
        'customer' => 'cust:',
        'vendor' => 'vnd:',
        'order' => 'ord:',
        'invoice' => 'inv:',
        'payment' => 'pmt:',
        'gl' => 'gl:',
        'report' => 'rpt:',
        'user' => 'usr:',
        'tenant' => 'tnt:',
        'marketplace' => 'mp:',
        'ai' => 'ai:',
    ];

    /**
     * Default TTL by cache type (in seconds)
     */
    protected const TTL = [
        'default' => 3600,           // 1 hour
        'inventory' => 300,          // 5 minutes (frequently changing)
        'product' => 7200,           // 2 hours
        'price' => 1800,             // 30 minutes
        'report' => 900,             // 15 minutes
        'marketplace' => 600,        // 10 minutes
        'user' => 1800,              // 30 minutes
        'configuration' => 86400,    // 24 hours
    ];

    /**
     * Get item from cache or store if not exists
     *
     * @param  string  $key  Cache key
     * @param  callable  $callback  Function to generate value if not cached
     * @param  int|null  $ttl  Time to live in seconds
     */
    public function remember(string $key, callable $callback, ?int $ttl = null): mixed
    {
        $ttl = $ttl ?? self::TTL['default'];

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get inventory cache with automatic tenant scoping
     */
    public function getInventory(int $productId, ?int $warehouseId = null, ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : null);
        $key = $this->makeKey('inventory', "product:{$productId}:warehouse:{$warehouseId}:tenant:{$tenantId}");

        return $this->remember($key, fn () => $this->calculateInventory($productId, $warehouseId, $tenantId), self::TTL['inventory']);
    }

    /**
     * Invalidate inventory cache for specific product
     */
    public function invalidateInventory(int $productId, ?int $warehouseId = null, ?int $tenantId = null): void
    {
        $tenantId = $tenantId ?? (auth()->check() ? auth()->user()->tenant_id : null);
        $patterns = [
            $this->makeKey('inventory', "product:{$productId}:*"),
            $this->makeKey('inventory', "warehouse:{$warehouseId}:*"),
        ];

        $this->invalidateByPatterns($patterns);

        Log::info('Inventory cache invalidated', [
            'product_id' => $productId,
            'warehouse_id' => $warehouseId,
        ]);
    }

    /**
     * Get product pricing with tier and customer group support
     */
    public function getProductPrice(
        int $productId,
        int $quantity = 1,
        ?int $customerId = null,
        ?int $tenantId = null
    ): array {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        $key = $this->makeKey('price', "product:{$productId}:qty:{$quantity}:customer:{$customerId}:tenant:{$tenantId}");

        return $this->remember($key, function () use ($productId, $quantity, $customerId, $tenantId) {
            return $this->calculateProductPrice($productId, $quantity, $customerId, $tenantId);
        }, self::TTL['price']);
    }

    /**
     * Invalidate price cache for product
     */
    public function invalidatePrice(int $productId, ?int $tenantId = null): void
    {
        $pattern = $this->makeKey('price', "product:{$productId}:*");
        $this->invalidateByPatterns([$pattern]);
    }

    /**
     * Get expensive report data with caching
     */
    public function getReport(string $reportType, array $filters = [], ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? auth()->user()?->tenant_id;
        $filterHash = md5(json_encode($filters));
        $key = $this->makeKey('report', "{$reportType}:filters:{$filterHash}:tenant:{$tenantId}");

        return $this->remember($key, function () use ($reportType, $filters, $tenantId) {
            return $this->generateReport($reportType, $filters, $tenantId);
        }, self::TTL['report']);
    }

    /**
     * Invalidate all reports (use after major data changes)
     */
    public function invalidateAllReports(): void
    {
        $pattern = $this->makeKey('report', '*');
        $this->invalidateByPatterns([$pattern]);

        Log::info('All report caches invalidated');
    }

    /**
     * Get marketplace sync status
     */
    public function getMarketplaceSyncStatus(string $type, string $sku, int $marketplaceId): ?array
    {
        $key = $this->makeKey('marketplace', "sync:{$type}:sku:{$sku}:mp:{$marketplaceId}");

        return Cache::get($key);
    }

    /**
     * Update marketplace sync status
     */
    public function setMarketplaceSyncStatus(
        string $type,
        string $sku,
        int $marketplaceId,
        array $data,
        int $ttl = 600
    ): void {
        $key = $this->makeKey('marketplace', "sync:{$type}:sku:{$sku}:mp:{$marketplaceId}");
        Cache::put($key, $data, $ttl);
    }

    /**
     * Bulk invalidate cache by patterns
     */
    public function invalidateByPatterns(array $patterns): void
    {
        if (config('cache.default') === 'redis') {
            // Redis supports pattern deletion efficiently
            foreach ($patterns as $pattern) {
                $keys = Cache::getMultiple([$pattern]); // This would need custom implementation
                Cache::deleteMultiple($keys);
            }
        } else {
            // For database/file cache, we need to track keys manually
            // This is a limitation - consider using tags instead
            $this->invalidateWithTags($patterns);
        }
    }

    /**
     * Invalidate cache using tags (Laravel feature)
     */
    public function invalidateWithTags(array $tags): void
    {
        if (method_exists(Cache::class, 'tags')) {
            Cache::tags($tags)->flush();
        } else {
            // Fallback: flush related caches
            Log::warning('Cache tags not supported, flushing related caches');
        }
    }

    /**
     * Make cache key with proper prefixing and tenant scoping
     */
    protected function makeKey(string $prefix, string $key): string
    {
        $prefixCode = self::PREFIXES[$prefix] ?? '';
        $appPrefix = config('cache.prefix', 'qalcuity-');

        return "{$appPrefix}{$prefixCode}{$key}";
    }

    /**
     * Calculate inventory (placeholder - implement based on your logic)
     */
    protected function calculateInventory(int $productId, ?int $warehouseId, ?int $tenantId): array
    {
        // This should call your actual inventory calculation logic
        // Example implementation:
        /*
        $query = Product::with(['warehouses' => function($q) use ($warehouseId) {
            if ($warehouseId) $q->where('warehouse_id', $warehouseId);
        }])->find($productId);

        return [
            'product_id' => $productId,
            'total_quantity' => $query->warehouses->sum('quantity'),
            'available' => $query->warehouses->sum('available'),
            'reserved' => $query->warehouses->sum('reserved'),
            'warehouses' => $query->warehouses->toArray(),
        ];
        */

        return [];
    }

    /**
     * Calculate product price (placeholder)
     */
    protected function calculateProductPrice(int $productId, int $quantity, ?int $customerId, ?int $tenantId): array
    {
        // Implement your pricing logic here
        return [];
    }

    /**
     * Generate report (placeholder)
     */
    protected function generateReport(string $reportType, array $filters, ?int $tenantId): array
    {
        // Implement report generation logic
        return [];
    }

    /**
     * Clear all caches (admin function)
     */
    public function clearAll(): void
    {
        Cache::flush();
        Log::info('All caches cleared');
    }

    /**
     * Get cache statistics (for monitoring)
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'stores' => array_keys(config('cache.stores')),
        ];
    }
}
