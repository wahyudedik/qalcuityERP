<?php

use App\Http\Middleware\AddSecurityHeaders;
use App\Http\Middleware\ApiTokenAuth;
use App\Http\Middleware\AuditTrailMiddleware;
use App\Http\Middleware\BusinessHoursMiddleware;
use App\Http\Middleware\CacheApiResponse;
use App\Http\Middleware\CheckAiQuota;
use App\Http\Middleware\CheckModulePlanAccess;
use App\Http\Middleware\CheckPermissionMiddleware;
use App\Http\Middleware\CheckTenantActive;
use App\Http\Middleware\EnforceTenantIsolation;
use App\Http\Middleware\HandleOfflineSync;
use App\Http\Middleware\HealthcareAccessMiddleware;
use App\Http\Middleware\IpWhitelistMiddleware;
use App\Http\Middleware\PermissionMiddleware;
use App\Http\Middleware\RateLimitAiRequests;
use App\Http\Middleware\RateLimitApiRequests;
use App\Http\Middleware\RBACMiddleware;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\ValidateFileUpload;
use App\Http\Middleware\VerifyCsrfForUploads;
use App\Http\Middleware\VerifyWebhookSignature;
use App\Providers\AuthServiceProvider;
use App\Services\ErrorContextEnricher;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        AuthServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'check.permission' => CheckPermissionMiddleware::class,
            'ip.whitelist' => IpWhitelistMiddleware::class,
            'tenant.active' => CheckTenantActive::class,
            'tenant.isolation' => EnforceTenantIsolation::class,
            'webhook.verify' => VerifyWebhookSignature::class,
            'api.token' => ApiTokenAuth::class,
            'ai.quota' => CheckAiQuota::class,
            'offline.sync' => HandleOfflineSync::class,
            'api.rate' => RateLimitApiRequests::class,
            'ai.rate' => RateLimitAiRequests::class,
            'csrf.upload' => VerifyCsrfForUploads::class,
            'security.headers' => AddSecurityHeaders::class,
            'file.upload' => ValidateFileUpload::class, // SEC-004
            'cache.response' => CacheApiResponse::class,

            // Healthcare Module Middleware
            'healthcare.access' => HealthcareAccessMiddleware::class,
            'healthcare.audit' => AuditTrailMiddleware::class,
            'healthcare.rbac' => RBACMiddleware::class,
            'healthcare.hours' => BusinessHoursMiddleware::class,

            // Plan-based module access middleware
            // Usage: Route::middleware('check.module.plan:manufacturing')->group(...)
            'check.module.plan' => CheckModulePlanAccess::class,
        ]);

        // CheckTenantActive di-append ke web group agar berjalan di semua request
        // EnforceTenantIsolation TIDAK di-append global — dipakai per-route group saja
        // karena middleware ini butuh route parameters yang belum tersedia di level global
        // CATATAN: VerifyCsrfToken TIDAK di-append ulang — sudah ada di web group default Laravel
        $middleware->appendToGroup('web', [
            CheckTenantActive::class,
            HandleOfflineSync::class,
            AddSecurityHeaders::class,
        ]);

        // Offline-synced requests carry stale CSRF tokens — handled via HandleOfflineSync middleware
        // We exclude nothing here; the middleware itself manages token-less offline requests.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (Throwable $e): void {
            // Skip logging for certain non-critical exception types
            $ignore = [
                AuthenticationException::class,
                AuthorizationException::class,
                ValidationException::class,
                NotFoundHttpException::class,
                MethodNotAllowedHttpException::class,
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
                    level: $e instanceof Error || $e instanceof ParseError ? 'emergency' : 'error',
                    type: 'exception'
                );
            } catch (Throwable $logError) {
                Log::error('Failed to log exception to database', [
                    'original_exception' => get_class($e),
                    'log_error' => $logError->getMessage(),
                ]);
            }
        });
    })->create();
