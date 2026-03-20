<?php

namespace App\Providers;

use App\Services\ChatSessionManager;
use App\Services\GeminiService;
use App\Services\GeminiWriteValidator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(GeminiService::class);
        $this->app->singleton(ChatSessionManager::class);
        $this->app->singleton(GeminiWriteValidator::class);
    }

    public function boot(): void
    {
        //
    }
}
