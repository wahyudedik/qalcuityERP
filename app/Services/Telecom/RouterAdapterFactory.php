<?php

namespace App\Services\Telecom;

use App\Models\NetworkDevice;
use InvalidArgumentException;

/**
 * Factory for creating router adapter instances.
 * 
 * Usage:
 * $adapter = RouterAdapterFactory::create($device);
 */
class RouterAdapterFactory
{
    /**
     * Map of supported brands to their adapter classes.
     */
    protected static array $adapters = [
        'mikrotik' => MikroTikRouterOSAdapter::class,
        'ubiquiti' => UbiquitiUniFiAdapter::class,
        'openwrt' => OpenWRTAdapter::class,
        // 'cisco' => CiscoMerakiAdapter::class, // TODO
    ];

    /**
     * Create a router adapter instance.
     * 
     * @param NetworkDevice $device
     * @return RouterAdapter
     * @throws InvalidArgumentException If brand is not supported
     */
    public static function create(NetworkDevice $device): RouterAdapter
    {
        $brand = strtolower($device->brand);

        if (!isset(self::$adapters[$brand])) {
            throw new InvalidArgumentException(
                "Unsupported router brand: {$brand}. Supported brands: " .
                implode(', ', array_keys(self::$adapters))
            );
        }

        $adapterClass = self::$adapters[$brand];

        if (!class_exists($adapterClass)) {
            throw new InvalidArgumentException("Adapter class not found: {$adapterClass}");
        }

        return new $adapterClass($device);
    }

    /**
     * Register a custom adapter.
     * 
     * @param string $brand
     * @param string $adapterClass
     */
    public static function register(string $brand, string $adapterClass): void
    {
        if (!is_subclass_of($adapterClass, RouterAdapter::class)) {
            throw new InvalidArgumentException(
                "Adapter class must extend RouterAdapter: {$adapterClass}"
            );
        }

        self::$adapters[strtolower($brand)] = $adapterClass;
    }

    /**
     * Get list of supported brands.
     * 
     * @return array
     */
    public static function getSupportedBrands(): array
    {
        return array_keys(self::$adapters);
    }

    /**
     * Check if brand is supported.
     * 
     * @param string $brand
     * @return bool
     */
    public static function isSupported(string $brand): bool
    {
        return isset(self::$adapters[strtolower($brand)]);
    }
}
