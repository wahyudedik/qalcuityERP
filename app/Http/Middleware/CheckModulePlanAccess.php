<?php

namespace App\Http\Middleware;

use App\Services\PlanModuleMap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckModulePlanAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        $user = $request->user();

        // 1. Not authenticated → redirect to login
        if (!$user) {
            return redirect()->route('login');
        }

        // 2. Super-admin has full access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $request->user()->tenant;

        // 4. Null enabled_modules → backward compat for legacy tenants
        if ($tenant->enabled_modules === null) {
            return $next($request);
        }

        // 5. Resolve plan slug
        $planSlug = $tenant->subscriptionPlan->slug ?? $tenant->plan ?? null;

        // 6. Check if module is allowed for plan
        if (!PlanModuleMap::isModuleAllowedForPlan($moduleKey, $planSlug)) {
            // 7. Not allowed
            if ($request->expectsJson()) {
                return response()->json(['message' => "Modul {$moduleKey} memerlukan upgrade paket"], 403);
            }

            try {
                return redirect()->route('subscription.upgrade')->with('module', $moduleKey);
            } catch (\Exception $e) {
                abort(403, "Modul {$moduleKey} memerlukan upgrade paket");
            }
        }

        // 8. Allowed → proceed
        return $next($request);
    }
}
