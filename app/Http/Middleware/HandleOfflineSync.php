<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Detects requests that were queued offline and synced later.
 *
 * Adds context so controllers can handle potential conflicts
 * (e.g. duplicate prevention, stale data).
 *
 * Offline-synced requests include a fresh CSRF token obtained
 * at queue time, so normal CSRF verification still applies.
 */
class HandleOfflineSync
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->header('X-Offline-Sync') !== '1') {
            return $next($request);
        }

        // Mark request as offline-synced for downstream use
        $request->attributes->set('is_offline_sync', true);

        // If the response would be a redirect (typical for form POST),
        // convert it to JSON for the service worker to handle
        $response = $next($request);

        if ($response->isRedirection() && $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Offline sync berhasil',
                'redirect' => $response->headers->get('Location'),
            ]);
        }

        return $response;
    }
}
