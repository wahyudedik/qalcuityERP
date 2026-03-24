<?php

namespace App\Http\Middleware;

use App\Services\AiQuotaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckAiQuota — Middleware to enforce AI usage limits per tenant.
 *
 * Apply to any route that calls an external AI API (Gemini, etc.).
 * Returns 429 JSON for AJAX requests, or redirects back with error for web requests.
 */
class CheckAiQuota
{
    public function __construct(private AiQuotaService $quota) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Super admin and users without tenant bypass quota
        if (!$user || !$user->tenant_id || $user->isSuperAdmin()) {
            return $next($request);
        }

        $tenantId = $user->tenant_id;

        if (!$this->quota->isAllowed($tenantId)) {
            $status = $this->quota->status($tenantId);
            $message = "Kuota AI bulan ini sudah habis ({$status['used']}/{$status['limit']} pesan). "
                . "Upgrade paket untuk mendapatkan lebih banyak akses AI.";

            if ($request->expectsJson() || $request->is('*/ai/*') || $request->is('chat/*')) {
                return response()->json([
                    'error'          => 'quota_exceeded',
                    'message'        => $message,
                    'quota_exceeded' => true,
                    'used'           => $status['used'],
                    'limit'          => $status['limit'],
                ], 429);
            }

            return back()->with('error', $message);
        }

        return $next($request);
    }
}
