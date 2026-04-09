<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HealthcareAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission = null): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (!$user) {
            return redirect()->route('login')
                ->with('error', 'You must be logged in to access healthcare module.');
        }

        // Superadmin has full access
        if ($user->hasRole('superadmin') || $user->is_superadmin) {
            return $next($request);
        }

        // Check if tenant has healthcare module enabled
        if ($user->tenant) {
            $enabledModules = $user->tenant->enabled_modules ?? [];

            if (is_array($enabledModules) && !in_array('healthcare', $enabledModules)) {
                abort(403, 'Healthcare module is not enabled for your tenant.');
            }

            // Check subscription status
            if (
                $user->tenant->subscription_status === 'expired' ||
                $user->tenant->subscription_status === 'suspended'
            ) {
                abort(403, 'Healthcare module access suspended. Please renew your subscription.');
            }
        }

        // Check specific permission if provided
        if ($permission) {
            if (!$user->can($permission)) {
                abort(403, 'You do not have permission to access this resource.');
            }
        } else {
            // Default healthcare access check
            $hasHealthcareAccess = $user->hasRole('admin') ||
                $user->hasRole('doctor') ||
                $user->hasRole('nurse') ||
                $user->hasRole('receptionist') ||
                $user->hasPermission('healthcare.access');

            if (!$hasHealthcareAccess) {
                abort(403, 'You do not have access to the healthcare module.');
            }
        }

        return $next($request);
    }
}
