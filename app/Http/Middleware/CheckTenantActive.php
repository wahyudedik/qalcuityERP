<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika route belum di-resolve, skip saja
        if (! $request->route()) {
            return $next($request);
        }

        // Skip semua halaman auth & public — cegah redirect loop
        if ($request->routeIs(
            'login', 'register',
            'password.*', 'two-factor.*',
            'verification.*', 'auth.google', 'auth.google.callback',
            'logout', 'subscription.expired',
            'resources.*', 'legal.*', 'landing', 'about.*',
            'documentation', 'offline', 'api-docs',
            'clear.cookies.temp', 'up'
        )) {
            return $next($request);
        }

        // Skip API & webhook
        if ($request->is('api/*', 'webhook/*')) {
            return $next($request);
        }

        $user = $request->user();

        // Belum login — biarkan auth middleware yang handle
        if (! $user) {
            return $next($request);
        }

        // Super admin & affiliate tidak terikat tenant
        if (in_array($user->role, ['super_admin', 'affiliate'])) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant) {
            Auth::logout();

            return redirect()->route('login')->with('error', 'Akun tidak terhubung dengan tenant.');
        }

        if (! $tenant->canAccess()) {
            return redirect()->route('subscription.expired', [
                'status' => $tenant->subscriptionStatus(),
            ]);
        }

        return $next($request);
    }
}
