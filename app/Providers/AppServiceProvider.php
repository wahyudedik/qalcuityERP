<?php

namespace App\Providers;

use App\Events\AllModelsUnavailable;
use App\Events\SettingsUpdated;
use App\Exceptions\CustomExceptionHandler;
use App\Listeners\ClearSettingsCache;
use App\Listeners\NotifyAllModelsUnavailable;
use App\Models\SystemSetting;
use App\Models\TenantApiSetting;
use App\Models\Product;
use App\Models\ProductStock;
use App\Observers\ProductObserver;
use App\Observers\ProductStockObserver;
use App\Observers\SystemSettingObserver; // BUG-SET-001 FIX
use App\Observers\TenantApiSettingObserver; // BUG-SET-001 FIX
use App\Services\ChatSessionManager;
use App\Services\GeminiWriteValidator;
use App\View\Composers\SidebarBadgeComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register CustomExceptionHandler as the application exception handler
        $this->app->singleton(
            \Illuminate\Contracts\Debug\ExceptionHandler::class,
            CustomExceptionHandler::class
        );

        // GeminiService, ModelSwitcher, AiProviderRouter, dan provider AI lainnya
        // didaftarkan di AiProviderServiceProvider (bootstrap/providers.php).
        // ModelSwitcher tetap didaftarkan di sini sebagai fallback agar tidak ada
        // circular dependency jika AppServiceProvider di-load sebelum AiProviderServiceProvider.
        $this->app->singleton(\App\Services\AI\ModelSwitcher::class, function ($app) {
            return new \App\Services\AI\ModelSwitcher($app['cache.store']);
        });
        $this->app->singleton(ChatSessionManager::class);
        $this->app->singleton(GeminiWriteValidator::class);

        // Task 37: DocumentNumberService — singleton agar cache sequence per request
        $this->app->singleton(\App\Services\DocumentNumberService::class);

        // Task 35: TransactionStateMachine — singleton (stateless)
        $this->app->singleton(\App\Services\TransactionStateMachine::class);

        // Task 44-46: Cost Center, Business Constraints, Transaction Links
        $this->app->singleton(\App\Services\BusinessConstraintService::class);
        $this->app->singleton(\App\Services\TransactionLinkService::class);

        // BUG-AI-002 FIX: ToolRegistry - Factory pattern with static caching
        // Uses internal static cache, so binding as singleton for DI compatibility
        $this->app->singleton(\App\Services\ERP\ToolRegistry::class, function ($app) {
            // This will use static cache internally, so multiple calls are efficient
            // Actual caching happens in ToolRegistry constructor
            $user = $app['auth']->user();
            if ($user && $user->tenant_id) {
                return new \App\Services\ERP\ToolRegistry($user->tenant_id, $user->id);
            }
            return null;
        });
    }

    public function boot(): void
    {
        // Register model observers
        Product::observe(ProductObserver::class);
        ProductStock::observe(ProductStockObserver::class);

        // BUG-SET-001 FIX: Register settings observers for cache invalidation
        SystemSetting::observe(SystemSettingObserver::class);
        TenantApiSetting::observe(TenantApiSettingObserver::class);

        // Register event listeners for settings cache invalidation
        Event::listen(SettingsUpdated::class, ClearSettingsCache::class);

        // Requirements: 10.1 — notify when all Gemini models are unavailable
        Event::listen(AllModelsUnavailable::class, NotifyAllModelsUnavailable::class);

        // Register View Composers for sidebar badge optimization (N+1 query fix)
        View::composer('layouts.app', SidebarBadgeComposer::class);

        $this->configureRateLimiting();
        $this->registerBladeDirectives();
        $this->loadSystemSettingsIntoConfig();
    }

    /**
     * Load system settings from DB into Laravel config.
     * Overrides .env values with DB-stored values (SuperAdmin managed).
     * Gracefully skips if DB is not available (first deploy / artisan migrate).
     */
    protected function loadSystemSettingsIntoConfig(): void
    {
        SystemSetting::loadIntoConfig([
            // AI / Gemini
            'gemini_api_key' => 'gemini.api_key',
            'gemini_model' => 'gemini.model',
            'gemini_timeout' => 'gemini.timeout',
            'ai_response_cache_enabled' => 'gemini.optimization.cache_enabled',
            'ai_cache_short_ttl' => 'gemini.optimization.cache_ttl.short',
            'ai_cache_default_ttl' => 'gemini.optimization.cache_ttl.default',
            'ai_cache_long_ttl' => 'gemini.optimization.cache_ttl.long',
            'ai_rule_based_enabled' => 'gemini.optimization.rule_based_enabled',
            'ai_streaming_enabled' => 'gemini.optimization.streaming_enabled',
            // Email / SMTP
            'mail_host' => 'mail.mailers.smtp.host',
            'mail_port' => 'mail.mailers.smtp.port',
            'mail_username' => 'mail.mailers.smtp.username',
            'mail_password' => 'mail.mailers.smtp.password',
            'mail_encryption' => 'mail.mailers.smtp.encryption',
            'mail_from_address' => 'mail.from.address',
            'mail_from_name' => 'mail.from.name',
            // Google OAuth
            'google_client_id' => 'services.google.client_id',
            'google_client_secret' => 'services.google.client_secret',
            // Push Notification (VAPID)
            'vapid_public_key' => 'services.vapid.public_key',
            'vapid_private_key' => 'services.vapid.private_key',
            'vapid_public_key_dev' => 'services.vapid.development.public_key',
            'vapid_private_key_dev' => 'services.vapid.development.private_key',
            'vapid_public_key_prod' => 'services.vapid.production.public_key',
            'vapid_private_key_prod' => 'services.vapid.production.private_key',
            // Error alerts
            'slack_error_webhook_url' => 'services.slack.error_webhook',
            'error_alert_email' => 'services.error_alert_email.recipients',
            // App settings
            'app_name' => 'app.name',
            'app_url' => 'app.url',
            'app_timezone' => 'app.timezone',
        ]);
    }

    protected function registerBladeDirectives(): void
    {
        // @canmodule('sales', 'delete') ... @endcanmodule
        Blade::directive('canmodule', function (string $expression) {
            [$module, $action] = array_map('trim', explode(',', $expression, 2));
            return "<?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), {$module}, {$action})): ?>";
        });

        Blade::directive('endcanmodule', function () {
            return '<?php endif; ?>';
        });

        // @cannotmodule('sales', 'delete') ... @endcannotmodule
        Blade::directive('cannotmodule', function (string $expression) {
            [$module, $action] = array_map('trim', explode(',', $expression, 2));
            return "<?php if(!auth()->check() || !app(\App\Services\PermissionService::class)->check(auth()->user(), {$module}, {$action})): ?>";
        });

        Blade::directive('endcannotmodule', function () {
            return '<?php endif; ?>';
        });

        // TASK 6.9: Indonesian number formatting directives
        // Usage: @idr($amount) → Rp 1.234.567
        Blade::directive('idr', function (string $expression) {
            return "<?php echo \App\Helpers\NumberHelper::currency({$expression}); ?>";
        });

        // Usage: @number($value) → 1.234.567
        Blade::directive('number', function (string $expression) {
            return "<?php echo \App\Helpers\NumberHelper::format({$expression}); ?>";
        });

        // Usage: @decimal($value, 2) → 1.234,56
        Blade::directive('decimal', function (string $expression) {
            return "<?php echo \App\Helpers\NumberHelper::format({$expression}); ?>";
        });

        // Usage: @percent($value) → 12,34%
        Blade::directive('percent', function (string $expression) {
            return "<?php echo \App\Helpers\NumberHelper::percentage({$expression}); ?>";
        });

        // Usage: @abbr($value) → 1,2 Jt
        Blade::directive('abbr', function (string $expression) {
            return "<?php echo \App\Helpers\NumberHelper::abbreviate({$expression}); ?>";
        });
    }

    protected function configureRateLimiting(): void
    {
        // ── AI Chat (existing) ────────────────────────────────────
        RateLimiter::for('ai-chat', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by('user:' . $request->user()->id)
                : Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('ai-media', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by('user:' . $request->user()->id)
                : Limit::perMinute(2)->by($request->ip());
        });

        // ── REST API — read endpoints ─────────────────────────────
        // 60 req/min base, scaled by plan
        RateLimiter::for('api-read', function (Request $request) {
            $tenantId = $request->get('_api_tenant_id');
            $multiplier = $this->planMultiplier($request);
            $limit = (int) (60 * $multiplier);

            return $tenantId
                ? Limit::perMinute($limit)->by('api-read:tenant:' . $tenantId)->response(fn() => $this->rateLimitResponse('API read', $limit))
                : Limit::perMinute(10)->by('api-read:ip:' . $request->ip());
        });

        // ── REST API — write endpoints ────────────────────────────
        // 20 req/min base, scaled by plan
        RateLimiter::for('api-write', function (Request $request) {
            $tenantId = $request->get('_api_tenant_id');
            $multiplier = $this->planMultiplier($request);
            $limit = (int) (20 * $multiplier);

            return $tenantId
                ? Limit::perMinute($limit)->by('api-write:tenant:' . $tenantId)->response(fn() => $this->rateLimitResponse('API write', $limit))
                : Limit::perMinute(5)->by('api-write:ip:' . $request->ip());
        });

        // ── Inbound webhooks (Midtrans, Xendit, Telegram, WA) ────
        // 30 req/min per IP — generous for payment callbacks
        RateLimiter::for('webhook-inbound', function (Request $request) {
            return Limit::perMinute(30)
                ->by('webhook:ip:' . $request->ip())
                ->response(fn() => $this->rateLimitResponse('Webhook', 30));
        });

        // ── POS checkout ──────────────────────────────────────────
        // 60 req/min per user — high throughput for busy cashiers
        RateLimiter::for('pos-checkout', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(60)->by('pos:user:' . $request->user()->id)
                : Limit::perMinute(10)->by('pos:ip:' . $request->ip());
        });

        // ── Export / Import ───────────────────────────────────────
        // Heavy operations — 10 exports/min, 5 imports/min
        RateLimiter::for('export', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by('export:user:' . $request->user()->id)
                : Limit::perMinute(2)->by('export:ip:' . $request->ip());
        });

        RateLimiter::for('import', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(5)->by('import:user:' . $request->user()->id)
                : Limit::perMinute(1)->by('import:ip:' . $request->ip());
        });

        // ── Global web fallback ───────────────────────────────────
        // 120 req/min per user for general authenticated routes
        RateLimiter::for('web-global', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(120)->by('web:user:' . $request->user()->id)
                : Limit::perMinute(30)->by('web:ip:' . $request->ip());
        });
    }

    /**
     * Plan-based rate limit multiplier.
     */
    private function planMultiplier(Request $request): float
    {
        $tenant = null;
        $apiToken = $request->attributes->get('api_token');
        if ($apiToken) {
            $tenant = $apiToken->tenant;
        } elseif ($request->user()?->tenant) {
            $tenant = $request->user()->tenant;
        }

        if (!$tenant)
            return 1.0;

        return match ($tenant->plan) {
            'starter' => 1.0,
            'basic' => 1.5,
            'business' => 2.0,
            'professional' => 3.0,
            'pro' => 3.0,
            'enterprise' => 10.0,
            default => 0.5,
        };
    }

    private function rateLimitResponse(string $label, int $limit): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error' => 'rate_limit_exceeded',
            'message' => "{$label} rate limit terlampaui ({$limit}/menit). Coba lagi nanti.",
        ], 429);
    }
}
