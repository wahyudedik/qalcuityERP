<?php

namespace App\Http\Middleware;

use App\Models\ApiToken;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    public function handle(Request $request, Closure $next, string $ability = 'read')
    {
        $token = $request->bearerToken()
            ?? $request->header('X-API-Token')
            ?? $request->query('api_token');

        if (!$token) {
            return response()->json(['error' => 'API token diperlukan.'], 401);
        }

        $apiToken = ApiToken::where('token', $token)->first();

        if (!$apiToken || !$apiToken->isValid()) {
            return response()->json(['error' => 'Token tidak valid atau sudah kadaluarsa.'], 401);
        }

        if (!$apiToken->can($ability)) {
            return response()->json(['error' => "Token tidak memiliki izin '{$ability}'."], 403);
        }

        // Attach tenant context to request
        $request->merge(['_api_tenant_id' => $apiToken->tenant_id]);
        $request->attributes->set('api_token', $apiToken);

        // Update last used
        $apiToken->update(['last_used_at' => now()]);

        return $next($request);
    }
}
