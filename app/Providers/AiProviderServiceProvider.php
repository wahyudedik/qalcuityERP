<?php

namespace App\Providers;

use App\Services\AI\AiProviderRouter;
use App\Services\AI\ModelSwitcher;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\ProviderSwitcher;
use App\Services\GeminiService;
use Illuminate\Support\ServiceProvider;

/**
 * AiProviderServiceProvider — mendaftarkan semua binding untuk multi-AI-provider.
 *
 * Binding yang didaftarkan:
 * - ModelSwitcher    : singleton (inject cache.store)
 * - ProviderSwitcher : singleton (inject cache.store)
 * - GeminiProvider   : singleton (inject ModelSwitcher)
 * - AnthropicProvider: singleton
 * - AiProviderRouter : singleton (inject GeminiProvider, AnthropicProvider, ProviderSwitcher)
 * - GeminiService    : singleton (inject AiProviderRouter, GeminiProvider) — backward compatibility
 *
 * Requirements: 6.1–6.7
 */
class AiProviderServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ModelSwitcher — mengelola fallback antar model dalam satu provider (Gemini)
        // Sudah didaftarkan di AppServiceProvider sebagai singleton, tapi kita daftarkan
        // di sini juga agar AiProviderServiceProvider self-contained.
        // Laravel akan menggunakan binding yang sudah ada jika sudah terdaftar.
        $this->app->singleton(ModelSwitcher::class, function ($app) {
            return new ModelSwitcher($app['cache.store']);
        });

        // ProviderSwitcher — mengelola cooldown dan fallback lintas-provider
        $this->app->singleton(ProviderSwitcher::class, function ($app) {
            return new ProviderSwitcher($app['cache.store']);
        });

        // GeminiProvider — implementasi AiProvider untuk Google Gemini
        $this->app->singleton(GeminiProvider::class, function ($app) {
            return new GeminiProvider($app->make(ModelSwitcher::class));
        });

        // AnthropicProvider — implementasi AiProvider untuk Anthropic Claude
        $this->app->singleton(AnthropicProvider::class, function ($app) {
            return new AnthropicProvider();
        });

        // AiProviderRouter — orkestrasi pemilihan provider dan fallback lintas-provider
        $this->app->singleton(AiProviderRouter::class, function ($app) {
            return new AiProviderRouter(
                $app->make(GeminiProvider::class),
                $app->make(AnthropicProvider::class),
                $app->make(ProviderSwitcher::class),
            );
        });

        // GeminiService — thin wrapper atas AiProviderRouter untuk backward compatibility
        // Didaftarkan sebagai singleton karena state (language, tenantContext) kini
        // dikelola oleh router dan provider, bukan oleh GeminiService itu sendiri.
        $this->app->singleton(GeminiService::class, function ($app) {
            return new GeminiService(
                $app->make(AiProviderRouter::class),
                $app->make(GeminiProvider::class),
            );
        });
    }
}
