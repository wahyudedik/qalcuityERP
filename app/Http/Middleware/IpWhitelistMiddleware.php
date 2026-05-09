<?php

namespace App\Http\Middleware;

use App\Models\IpWhitelist;
use App\Services\Security\IpWhitelistService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IpWhitelistMiddleware
{
    protected $ipWhitelistService;

    public function __construct(IpWhitelistService $ipWhitelistService)
    {
        $this->ipWhitelistService = $ipWhitelistService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $scope = 'admin'): Response
    {
        // Skip if user is not authenticated
        if (! auth()->check()) {
            return $next($request);
        }

        // Check if IP whitelisting is enabled for this tenant
        $tenantId = auth()->user()->tenant_id;

        // If there are any active whitelisted IPs for this scope, enforce whitelist
        $hasWhitelist = IpWhitelist::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where(function ($query) use ($scope) {
                $query->where('scope', $scope)
                    ->orWhere('scope', 'all');
            })
            ->exists();

        if ($hasWhitelist) {
            // Check if current IP is allowed
            if (! $this->ipWhitelistService->isIpAllowed($tenantId, $scope)) {
                // Log unauthorized access attempt
                \Log::warning('Unauthorized IP access attempt', [
                    'tenant_id' => $tenantId,
                    'ip_address' => $request->ip(),
                    'scope' => $scope,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Your IP address is not authorized to access this resource',
                ], 403);
            }
        }

        return $next($request);
    }
}
