<?php

namespace App\Services;

use Illuminate\Http\Request;

class MobileOptimizationService
{
    /**
     * Check if request is from mobile device
     */
    public function isMobile(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');

        $mobileKeywords = [
            'Android',
            'iPhone',
            'iPad',
            'iPod',
            'BlackBerry',
            'Windows Phone',
            'Mobile',
            'webOS',
            'Opera Mini',
        ];

        foreach ($mobileKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        // Check for mobile-specific header from PWA
        if ($request->header('X-Mobile-App')) {
            return true;
        }

        return false;
    }

    /**
     * Check if device is tablet
     */
    public function isTablet(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');

        $tabletKeywords = ['iPad', 'Android Tablet', 'Silk'];

        foreach ($tabletKeywords as $keyword) {
            if (stripos($userAgent, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get device type
     */
    public function getDeviceType(Request $request): string
    {
        if ($this->isTablet($request)) {
            return 'tablet';
        }

        if ($this->isMobile($request)) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Optimize response for mobile devices
     */
    public function optimizeForMobile(array $data, string $deviceType): array
    {
        if ($deviceType === 'mobile') {
            // Reduce data payload for mobile
            return $this->optimizeForLowBandwidth($data);
        }

        return $data;
    }

    /**
     * Optimize data for low bandwidth connections
     */
    protected function optimizeForLowBandwidth(array $data): array
    {
        // Remove heavy fields
        unset($data['full_description']);
        unset($data['high_res_image']);

        // Limit arrays to essential items
        if (isset($data['items']) && is_array($data['items'])) {
            $data['items'] = array_slice($data['items'], 0, 20);
        }

        return $data;
    }

    /**
     * Generate mobile-optimized pagination
     */
    public function getMobilePagination(int $perPage = 10): int
    {
        return $perPage;
    }

    /**
     * Check if touch gestures should be enabled
     */
    public function shouldEnableTouchGestures(Request $request): bool
    {
        return $this->isMobile($request) || $this->isTablet($request);
    }

    /**
     * Get optimal image size for device
     */
    public function getOptimalImageSize(string $deviceType): string
    {
        return match ($deviceType) {
            'mobile' => 'small',    // 400x400
            'tablet' => 'medium',   // 800x800
            default => 'large'      // 1200x1200
        };
    }

    /**
     * Check if push notifications are supported
     */
    public function supportsPushNotifications(): bool
    {
        return function_exists('curl_init') && extension_loaded('openssl');
    }

    /**
     * Get camera capabilities
     */
    public function getCameraCapabilities(): array
    {
        return [
            'barcode_scanning' => true,
            'qr_scanning' => true,
            'receipt_capture' => true,
            'document_scan' => true,
            'max_resolution' => '1920x1080',
        ];
    }
}
