<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Services\AiInsightService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateAiInsights implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $period = 'daily', // 'daily' | 'weekly'
    ) {}

    public function handle(AiInsightService $service): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant || !$tenant->canAccess()) return;

        $insights = $service->generateAndSave($this->tenantId);

        Log::info("GenerateAiInsights: tenant={$this->tenantId} period={$this->period} insights=" . count($insights));

        // Kirim digest email jika ada insight penting
        $hasCritical = collect($insights)->contains('severity', 'critical');
        $hasWarning  = collect($insights)->contains('severity', 'warning');

        if ($this->period === 'daily' && ($hasCritical || $hasWarning)) {
            SendAiDigest::dispatch($this->tenantId, $this->period);
        }

        if ($this->period === 'weekly') {
            SendAiDigest::dispatch($this->tenantId, $this->period);
        }
    }
}
