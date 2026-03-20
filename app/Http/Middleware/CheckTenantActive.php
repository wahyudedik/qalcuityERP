<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin tidak terikat tenant
        if (! $user || $user->role === 'super_admin') {
            return $next($request);
        }

        // Skip halaman expired itu sendiri agar tidak loop
        if ($request->routeIs('subscription.expired', 'logout', 'login')) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            return redirect()->route('login');
        }

        if (! $tenant->canAccess()) {
            $status = $tenant->subscriptionStatus();
            return redirect()->route('subscription.expired', ['status' => $status]);
        }

        return $next($request);
    }
}
