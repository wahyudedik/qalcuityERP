<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AiDigestNotification;
use App\Services\AiInsightService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendAiDigest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 60;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $period = 'daily',
    ) {}

    public function handle(AiInsightService $service): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant || !$tenant->canAccess()) return;

        $insights = $service->analyze($this->tenantId);
        if (empty($insights)) return;

        // Kirim ke admin & manager
        $admins = User::where('tenant_id', $this->tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        foreach ($admins as $admin) {
            $admin->notify(new AiDigestNotification($tenant, $insights, $this->period));
        }

        Log::info("SendAiDigest: tenant={$this->tenantId} period={$this->period} recipients={$admins->count()}");
    }
}
