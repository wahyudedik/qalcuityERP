<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\Log;

/**
 * Enhanced error context enricher.
 *
 * Adds valuable debugging information to error logs:
 * - Current user context
 * - Request details
 * - System state
 * - Tenant information
 * - Recent activity
 */
class ErrorContextEnricher
{
    /**
     * Enrich error context with additional debugging information
     */
    public static function enrich(\Throwable $exception, array $context = []): array
    {
        return array_merge(
            self::getRequestContext(),
            self::getUserContext(),
            self::getTenantContext(),
            self::getSystemContext(),
            self::getExceptionContext($exception),
            $context
        );
    }

    /**
     * Get current HTTP request context
     */
    protected static function getRequestContext(): array
    {
        if (! request()) {
            return [];
        }

        // Safely retrieve session ID only when session is available
        $sessionId = null;
        try {
            if (request()->hasSession() && request()->session()->isStarted()) {
                $sessionId = request()->session()->getId();
            }
        } catch (\Throwable $e) {
            // Session not available (e.g. during queue jobs or console commands)
        }

        return [
            'request' => [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'referrer' => request()->headers->get('referer'),
                'query' => request()->query->all(),
                'input' => self::sanitizeInput(request()->all()),
                'headers' => request()->headers->all(),
                'cookies' => request()->cookies->all(),
                'session_id' => $sessionId,
            ],
        ];
    }

    /**
     * Get current user context
     */
    protected static function getUserContext(): array
    {
        if (! auth()->check()) {
            return ['user' => ['authenticated' => false]];
        }

        $user = auth()->user();

        return [
            'user' => [
                'authenticated' => true,
                'id' => $user->id ?? null,
                'email' => $user->email ?? null,
                'name' => $user->name ?? null,
                'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames()->toArray() : [],
                'permissions' => method_exists($user, 'getAllPermissions')
                    ? $user->getAllPermissions()->pluck('name')->toArray()
                    : [],
                'last_login' => $user->last_login_at ?? null,
            ],
        ];
    }

    /**
     * Get tenant context - PENTING: Selalu capture tenant_id untuk debugging
     */
    protected static function getTenantContext(): array
    {
        $tenantId = null;
        $tenantData = [];

        // Strategy 1: Get from authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            $tenantId = $user->tenant_id ?? null;

            if ($tenantId && method_exists($user, 'tenant')) {
                $tenant = $user->tenant;
                if ($tenant) {
                    $tenantData = [
                        'id' => $tenant->id,
                        'name' => $tenant->name ?? null,
                        'subdomain' => $tenant->subdomain ?? null,
                        'plan' => $tenant->plan ?? null,
                        'is_active' => $tenant->is_active ?? null,
                        'subscription_status' => $tenant->subscription_status ?? null,
                    ];
                }
            }
        }

        // Strategy 2: Fallback - get from route parameter
        if (! $tenantId && request()) {
            $tenantId = request()->route('tenant')
                ?? request()->route('tenant_id')
                ?? request()->input('tenant_id');
        }

        // Strategy 3: Fallback - get from request header (for API calls)
        if (! $tenantId && request()) {
            $tenantId = request()->header('X-Tenant-ID')
                ?? request()->header('X-Tenant');
        }

        // Jika tidak ada tenant_id, tetap return array dengan tenant_id = null
        // agar SuperAdmin tahu ini error dari system-level atau guest user
        return [
            'tenant' => array_merge([
                'id' => $tenantId ? (int) $tenantId : null,
                'source' => $tenantId ? (auth()->check() ? 'user' : 'request') : 'none',
            ], $tenantData),
        ];
    }

    /**
     * Get system context
     */
    protected static function getSystemContext(): array
    {
        return [
            'system' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'environment' => app()->environment(),
                'timezone' => config('app.timezone'),
                'locale' => app()->getLocale(),
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2).' MB',
                'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2).' MB',
                'server_time' => now()->toIso8601String(),
                'uptime' => self::getServerUptime(),
            ],
        ];
    }

    /**
     * Get exception-specific context
     */
    protected static function getExceptionContext(\Throwable $exception): array
    {
        return [
            'exception' => [
                'class' => get_class($exception),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'message' => $exception->getMessage(),
                'previous' => $exception->getPrevious() ? [
                    'class' => get_class($exception->getPrevious()),
                    'message' => $exception->getPrevious()->getMessage(),
                ] : null,
            ],
        ];
    }

    /**
     * Sanitize sensitive input data
     */
    protected static function sanitizeInput(array $input): array
    {
        $sensitive = ['password', 'password_confirmation', 'api_key', 'token', 'credit_card', 'cvv'];

        return collect($input)->mapWithKeys(function ($value, $key) use ($sensitive) {
            if (in_array(strtolower($key), $sensitive)) {
                return [$key => '***REDACTED***'];
            }

            if (is_array($value)) {
                return [$key => self::sanitizeInput($value)];
            }

            return [$key => $value];
        })->toArray();
    }

    /**
     * Get server uptime (if available)
     */
    protected static function getServerUptime(): ?string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $uptime = 'N/A (Windows)';
        } else {
            try {
                $uptime = trim(shell_exec('uptime -p 2>/dev/null') ?? 'N/A');
            } catch (\Throwable $e) {
                $uptime = 'N/A';
            }
        }

        return $uptime ?: null;
    }

    /**
     * Log enriched context to database - PASTIKAN tenant_id selalu terisi
     */
    public static function logToDatabase(
        \Throwable $exception,
        string $level,
        string $type = 'exception'
    ): ErrorLog {
        $enrichedContext = self::enrich($exception);

        // ✅ PENTING: Resolve tenant_id dari berbagai source dengan prioritas
        $tenantId = self::resolveTenantId();

        // Check for similar recent errors (within last hour)
        $similarError = ErrorLog::where('exception_class', get_class($exception))
            ->where('message', $exception->getMessage())
            ->where('created_at', '>=', now()->subHour())
            ->first();

        if ($similarError && $similarError->occurrence_count < 100) {
            // Increment occurrence instead of creating new record
            $similarError->incrementOccurrence();

            return $similarError;
        }

        // Create new error log dengan tenant_id yang sudah di-resolve
        return ErrorLog::create([
            'level' => $level,
            'type' => $type,
            'message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'tenant_id' => $tenantId, // ✅ Selalu terisi (bisa null jika memang system-wide error)
            'user_id' => auth()->id(),
            'url' => request()->fullUrl() ?? null,
            'ip_address' => request()->ip() ?? null,
            'user_agent' => request()->userAgent() ?? null,
            'context' => $enrichedContext,
            'request_data' => self::sanitizeInput(request()->all()),
            'exception_class' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'method' => request()->method() ?? null,
        ]);
    }

    /**
     * Resolve tenant_id dari berbagai source dengan prioritas
     *
     * Priority:
     * 1. Authenticated user's tenant_id
     * 2. Route parameter (tenant atau tenant_id)
     * 3. Request input/header
     * 4. Context dari exception (jika ada)
     */
    protected static function resolveTenantId(): ?int
    {
        // Priority 1: Authenticated user
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->tenant_id) {
                return (int) $user->tenant_id;
            }
        }

        // Priority 2: Route parameters
        if (request()) {
            $routeTenantId = request()->route('tenant')
                ?? request()->route('tenant_id');

            if ($routeTenantId) {
                return (int) $routeTenantId;
            }
        }

        // Priority 3: Request input/header (untuk API calls)
        if (request()) {
            $headerTenantId = request()->header('X-Tenant-ID')
                ?? request()->header('X-Tenant')
                ?? request()->input('tenant_id');

            if ($headerTenantId) {
                return (int) $headerTenantId;
            }
        }

        // Priority 4: Check if exception context has tenant_id
        // (untuk case custom exceptions yang pass tenant_id)
        // Ini bisa di-expand jika diperlukan

        // Return null jika memang tidak ada tenant context
        // (system-wide error, CLI command, atau guest user)
        return null;
    }
}
