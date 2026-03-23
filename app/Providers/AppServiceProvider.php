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
        // 30 request per menit per user untuk AI chat
        RateLimiter::for('ai-chat', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(30)->by('user:' . $request->user()->id)
                : Limit::perMinute(5)->by($request->ip());
        });

        // Upload media lebih berat — 10 per menit per user
        RateLimiter::for('ai-media', function (Request $request) {
            return $request->user()
                ? Limit::perMinute(10)->by('user:' . $request->user()->id)
                : Limit::perMinute(2)->by($request->ip());
        });
    }
}
