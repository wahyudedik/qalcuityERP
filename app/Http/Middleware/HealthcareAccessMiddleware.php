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
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'Anda harus login untuk mengakses modul kesehatan.');
        }

        // Superadmin has full access
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Check if tenant has healthcare module enabled
        if ($user->tenant) {
            $enabledModules = $user->tenant->enabled_modules ?? [];

            if (is_array($enabledModules) && ! in_array('healthcare', $enabledModules)) {
                abort(403, 'Modul kesehatan tidak diaktifkan untuk tenant Anda.');
            }

            // Check subscription status
            if (
                $user->tenant->subscription_status === 'expired' ||
                $user->tenant->subscription_status === 'suspended'
            ) {
                abort(403, 'Akses modul kesehatan ditangguhkan. Silakan perpanjang langganan Anda.');
            }
        }

        // Check specific permission if provided
        if ($permission) {
            if (! $user->can($permission)) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses sumber daya ini.');
            }
        } else {
            // Default healthcare access check
            $hasHealthcareAccess = $user->hasRole('admin') ||
                $user->hasRole('doctor') ||
                $user->hasRole('nurse') ||
                $user->hasRole('receptionist') ||
                $user->hasPermission('healthcare.access');

            if (! $hasHealthcareAccess) {
                abort(403, 'Anda tidak memiliki akses ke modul kesehatan.');
            }
        }

        return $next($request);
    }
}
