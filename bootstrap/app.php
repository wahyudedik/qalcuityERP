<?php

use App\Services\ErrorContextEnricher;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'check.permission' => \App\Http\Middleware\CheckPermissionMiddleware::class,
            'ip.whitelist' => \App\Http\Middleware\IpWhitelistMiddleware::class,
            'tenant.active' => \App\Http\Middleware\CheckTenantActive::class,
            'tenant.isolation' => \App\Http\Middleware\EnforceTenantIsolation::class,
            'webhook.verify' => \App\Http\Middleware\VerifyWebhookSignature::class,
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
            'ai.quota' => \App\Http\Middleware\CheckAiQuota::class,
            'offline.sync' => \App\Http\Middleware\HandleOfflineSync::class,
            'api.rate' => \App\Http\Middleware\RateLimitApiRequests::class,
            'ai.rate' => \App\Http\Middleware\RateLimitAiRequests::class,
            'csrf.upload' => \App\Http\Middleware\VerifyCsrfForUploads::class,
            'security.headers' => \App\Http\Middleware\AddSecurityHeaders::class,
            'file.upload' => \App\Http\Middleware\ValidateFileUpload::class, // SEC-004
    
            // Healthcare Module Middleware
            'healthcare.access' => \App\Http\Middleware\HealthcareAccessMiddleware::class,
            'healthcare.audit' => \App\Http\Middleware\AuditTrailMiddleware::class,
            'healthcare.rbac' => \App\Http\Middleware\RBACMiddleware::class,
            'healthcare.hours' => \App\Http\Middleware\BusinessHoursMiddleware::class,
        ]);

        // CheckTenantActive di-append ke web group agar berjalan di semua request
        // EnforceTenantIsolation TIDAK di-append global — dipakai per-route group saja
        // karena middleware ini butuh route parameters yang belum tersedia di level global
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckTenantActive::class,
            \App\Http\Middleware\HandleOfflineSync::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \App\Http\Middleware\AddSecurityHeaders::class,
        ]);

        // Offline-synced requests carry stale CSRF tokens — handled via HandleOfflineSync middleware
        // We exclude nothing here; the middleware itself manages token-less offline requests.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            // Skip logging for certain non-critical exception types
            $ignore = [
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Auth\Access\AuthorizationException::class,
                \Illuminate\Validation\ValidationException::class,
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            ];

            foreach ($ignore as $class) {
                if ($e instanceof $class) {
                    return;
                }
            }

            // Directly log to database without going through the exception handler
            // to avoid infinite recursion
            try {
                ErrorContextEnricher::logToDatabase(
                    exception: $e,
                    level: $e instanceof \Error || $e instanceof \ParseError ? 'emergency' : 'error',
                    type: 'exception'
                );
            } catch (\Throwable $logError) {
                \Illuminate\Support\Facades\Log::error('Failed to log exception to database', [
                    'original_exception' => get_class($e),
                    'log_error' => $logError->getMessage(),
                ]);
            }
        });
    })->create();
