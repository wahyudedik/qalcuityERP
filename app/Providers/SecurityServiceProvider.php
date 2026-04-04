<?php

namespace App\Providers;

use App\Services\OutputEscaper;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * SecurityServiceProvider - Register security-related services and Blade directives.
 */
class SecurityServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Blade directive for HTML escaping (shorthand)
        Blade::directive('e', function ($expression) {
            return "<?php echo \App\Services\OutputEscaper::e({$expression}); ?>";
        });

        // Blade directive for cleaning text content
        Blade::directive('clean', function ($expression) {
            return "<?php echo \App\Services\OutputEscaper::cleanText({$expression}); ?>";
        });

        // Blade directive for JavaScript escaping
        Blade::directive('escapeJs', function ($expression) {
            return "<?php echo \App\Services\OutputEscaper::js({$expression}); ?>";
        });

        // Blade directive for attribute escaping
        Blade::directive('escapeAttr', function ($expression) {
            return "<?php echo \App\Services\OutputEscaper::attr({$expression}); ?>";
        });

        // Blade directive for URL escaping
        Blade::directive('escapeUrl', function ($expression) {
            return "<?php echo \App\Services\OutputEscaper::url({$expression}); ?>";
        });
    }
}
