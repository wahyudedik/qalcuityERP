<?php

namespace App\Providers;

use App\Services\ChatSessionManager;
use App\Services\GeminiService;
use App\Services\GeminiWriteValidator;
use App\Services\PermissionService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // GeminiService TIDAK boleh singleton — state (language, tenantContext) harus fresh per request
        $this->app->bind(GeminiService::class);
        $this->app->singleton(ChatSessionManager::class);
        $this->app->singleton(GeminiWriteValidator::class);

        // Task 37: DocumentNumberService — singleton agar cache sequence per request
        $this->app->singleton(\App\Services\DocumentNumberService::class);

        // Task 35: TransactionStateMachine — singleton (stateless)
        $this->app->singleton(\App\Services\TransactionStateMachine::class);

        // Task 44-46: Cost Center, Business Constraints, Transaction Links
        $this->app->singleton(\App\Services\BusinessConstraintService::class);
        $this->app->singleton(\App\Services\TransactionLinkService::class);
    }

    public function boot(): void
    {
        $this->configureRateLimiting();
        $this->registerBladeDirectives();
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
                ? Limit::perMinute($limit)->by('api-read:tenant:' . $tenantId)->response(fn () => $this->rateLimitResponse('API read', $limit))
                : Limit::perMinute(10)->by('api-read:ip:' . $request->ip());
        });

        // ── REST API — write endpoints ────────────────────────────
        // 20 req/min base, scaled by plan
        RateLimiter::for('api-write', function (Request $request) {
            $tenantId = $request->get('_api_tenant_id');
            $multiplier = $this->planMultiplier($request);
            $limit = (int) (20 * $multiplier);

            return $tenantId
                ? Limit::perMinute($limit)->by('api-write:tenant:' . $tenantId)->response(fn () => $this->rateLimitResponse('API write', $limit))
                : Limit::perMinute(5)->by('api-write:ip:' . $request->ip());
        });

        // ── Inbound webhooks (Midtrans, Xendit, Telegram, WA) ────
        // 30 req/min per IP — generous for payment callbacks
        RateLimiter::for('webhook-inbound', function (Request $request) {
            return Limit::perMinute(30)
                ->by('webhook:ip:' . $request->ip())
                ->response(fn () => $this->rateLimitResponse('Webhook', 30));
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

        if (!$tenant) return 1.0;

        return match ($tenant->plan) {
            'starter'      => 1.0,
            'basic'        => 1.5,
            'business'     => 2.0,
            'professional' => 3.0,
            'pro'          => 3.0,
            'enterprise'   => 10.0,
            default        => 0.5,
        };
    }

    private function rateLimitResponse(string $label, int $limit): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'error'   => 'rate_limit_exceeded',
            'message' => "{$label} rate limit terlampaui ({$limit}/menit). Coba lagi nanti.",
        ], 429);
    }
}
