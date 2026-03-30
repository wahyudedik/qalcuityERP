<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role'             => \App\Http\Middleware\RoleMiddleware::class,
            'permission'       => \App\Http\Middleware\PermissionMiddleware::class,
            'tenant.active'    => \App\Http\Middleware\CheckTenantActive::class,
            'tenant.isolation' => \App\Http\Middleware\EnforceTenantIsolation::class,
            'webhook.verify'   => \App\Http\Middleware\VerifyWebhookSignature::class,
            'api.token'        => \App\Http\Middleware\ApiTokenAuth::class,
            'ai.quota'         => \App\Http\Middleware\CheckAiQuota::class,
            'offline.sync'     => \App\Http\Middleware\HandleOfflineSync::class,
            'api.rate'         => \App\Http\Middleware\RateLimitApiRequests::class,
        ]);

        // CheckTenantActive di-append ke web group agar berjalan di semua request
        // EnforceTenantIsolation TIDAK di-append global — dipakai per-route group saja
        // karena middleware ini butuh route parameters yang belum tersedia di level global
        $middleware->appendToGroup('web', [
            \App\Http\Middleware\CheckTenantActive::class,
            \App\Http\Middleware\HandleOfflineSync::class,
        ]);

        // Offline-synced requests carry stale CSRF tokens — handled via HandleOfflineSync middleware
        // We exclude nothing here; the middleware itself manages token-less offline requests.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e): void {
            // Jangan capture exception yang tidak penting
            $ignore = [
                \Illuminate\Auth\AuthenticationException::class,
                \Illuminate\Auth\Access\AuthorizationException::class,
                \Illuminate\Validation\ValidationException::class,
                \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
                \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
            ];
            foreach ($ignore as $class) {
                if ($e instanceof $class) return;
            }
            \App\Models\ErrorLog::capture($e, $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException && $e->getStatusCode() >= 500 ? 'critical' : 'error');
        });
    })->create();
