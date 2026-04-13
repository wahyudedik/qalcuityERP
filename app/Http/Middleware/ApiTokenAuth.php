<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next, string $ability = 'read')
    {
        $token = $request->bearerToken()
            ?? $request->header('X-API-Token');
        // NOTE: query string fallback dihapus — token di URL tercatat di server logs (security risk)

        if (!$token) {
            return response()->json(['error' => 'API token diperlukan.'], 401);
        }

        // BUG-API-002 FIX: Add database-level filtering for active and non-expired tokens
        // This prevents loading expired tokens into memory at all
        $apiToken = ApiToken::where('token', $token)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if (!$apiToken) {
            // Log failed authentication attempts for security audit
            Log::warning('API token authentication failed', [
                'reason' => 'token_not_found_or_expired',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'token_prefix' => substr($token, 0, 8) . '...',
            ]);

            return response()->json([
                'error' => 'Token tidak valid atau sudah kadaluarsa.',
                'error_code' => 'TOKEN_EXPIRED_OR_INVALID'
            ], 401);
        }

        if (!$apiToken->can($ability)) {
            Log::warning('API token permission denied', [
                'token_id' => $apiToken->id,
                'tenant_id' => $apiToken->tenant_id,
                'required_ability' => $ability,
                'token_abilities' => $apiToken->abilities,
            ]);

            return response()->json([
                'error' => "Token tidak memiliki izin '{$ability}'.",
                'error_code' => 'INSUFFICIENT_PERMISSIONS'
            ], 403);
        }

        // Attach tenant context to request
        $request->merge(['_api_tenant_id' => $apiToken->tenant_id]);
        $request->attributes->set('api_token', $apiToken);

        // Update last used (safe because we already verified token is active and not expired)
        $apiToken->update(['last_used_at' => now()]);

        return $next($request);
    }
}
