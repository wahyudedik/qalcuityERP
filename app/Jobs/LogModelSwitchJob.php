<?php

namespace App\Jobs;

use App\Models\AiModelSwitchLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogModelSwitchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly string $fromModel,
        public readonly string $toModel,
        public readonly string $reason,
        public readonly ?string $errorMessage,
        public readonly ?string $requestContext,
        public readonly ?int $triggeredByTenantId,
    ) {}

    public function handle(): void
    {
        AiModelSwitchLog::create([
            'from_model' => $this->fromModel,
            'to_model' => $this->toModel,
            'reason' => $this->reason,
            'error_message' => $this->errorMessage,
            'request_context' => $this->requestContext,
            'triggered_by_tenant_id' => $this->triggeredByTenantId,
            'switched_at' => now(),
        ]);
    }
}
