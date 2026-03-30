<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\AiFinancialAdvisorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiAdvisorRecommendations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180; // Gemini call can be slow

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $period = 'weekly',
    ) {
        $this->queue = 'ai';
    }

    public function handle(AiFinancialAdvisorService $service): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant || !$tenant->canAccess()) return;

        // Only for paid plans (not trial with < 7 days data)
        if ($tenant->plan === 'trial' && $tenant->created_at->diffInDays(now()) < 7) return;

        $recommendations = $service->generateRecommendations($this->tenantId, $this->period);

        Log::info("AiAdvisor: tenant={$this->tenantId} period={$this->period} recommendations=" . count($recommendations));
    }
}
