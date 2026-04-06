<?php

namespace App\Http\Middleware;

use App\Services\MobileOptimizationService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileOptimization
{
    protected $mobileService;

    public function __construct(MobileOptimizationService $mobileService)
    {
        $this->mobileService = $mobileService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detect device type
        $deviceType = $this->mobileService->getDeviceType($request);

        // Share device info with views
        view()->share('deviceType', $deviceType);
        view()->share('isMobile', $deviceType === 'mobile');
        view()->share('isTablet', $deviceType === 'tablet');
        view()->share('isDesktop', $deviceType === 'desktop');

        // Add device type to request
        $request->merge(['device_type' => $deviceType]);

        // Set touch gestures flag
        if ($this->mobileService->shouldEnableTouchGestures($request)) {
            view()->share('enableTouchGestures', true);
        }

        return $next($request);
    }
}
