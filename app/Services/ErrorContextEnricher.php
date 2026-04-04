<?php

namespace App\Services;

use App\Models\ErrorLog;
use Illuminate\Support\Facades\DB;
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
        if (!request()) {
            return [];
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
                'session_id' => request()->session()->getId(),
            ],
        ];
    }

    /**
     * Get current user context
     */
    protected static function getUserContext(): array
    {
        if (!auth()->check()) {
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
     * Get tenant context
     */
    protected static function getTenantContext(): array
    {
        if (!auth()->check() || !method_exists(auth()->user(), 'tenant')) {
            return [];
        }

        $tenant = auth()->user()->tenant;

        if (!$tenant) {
            return [];
        }

        return [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name ?? null,
                'subdomain' => $tenant->subdomain ?? null,
                'plan' => $tenant->plan ?? null,
                'is_active' => $tenant->is_active ?? null,
                'subscription_status' => $tenant->subscription_status ?? null,
            ],
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
                'memory_usage' => round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB',
                'peak_memory' => round(memory_get_peak_usage(true) / 1024 / 1024, 2) . ' MB',
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
        try {
            if (PHP_OS_FAMILY === 'Linux') {
                $uptime = shell_exec('uptime -p');
                return trim($uptime) ?: null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Log enriched context to database
     */
    public static function logToDatabase(
        \Throwable $exception,
        string $level,
        string $type = 'exception'
    ): ErrorLog {
        $enrichedContext = self::enrich($exception);

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

        // Create new error log
        return ErrorLog::create([
            'level' => $level,
            'type' => $type,
            'message' => $exception->getMessage(),
            'stack_trace' => $exception->getTraceAsString(),
            'tenant_id' => auth()->check() ? auth()->user()->tenant_id : null,
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
}
