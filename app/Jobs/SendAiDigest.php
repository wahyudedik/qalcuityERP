<?php

namespace App\Jobs;

use App\Models\NotificationPreference;
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
        public readonly ?int   $userId = null,
    ) {}

    public function handle(AiInsightService $service): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant || !$tenant->canAccess()) return;

        $insights = $service->analyze($this->tenantId);
        if (empty($insights)) return;

        // Get users who should receive digest — optionally scoped to a single user
        $query = User::where('tenant_id', $this->tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->where('digest_frequency', '!=', 'off');

        if ($this->userId !== null) {
            $query->where('id', $this->userId);
        }

        $users = $query->get();

        $sent = 0;
        foreach ($users as $user) {
            // Check per-user frequency preference against today's schedule
            if (!$this->shouldSendToday($user)) continue;

            // Check notification preference for ai_digest via email channel
            if (!NotificationPreference::isEnabled($user->id, 'ai_digest', 'email')) continue;

            $user->notify(new AiDigestNotification($tenant, $insights, $this->period));
            $sent++;
        }

        Log::info("SendAiDigest: tenant={$this->tenantId} period={$this->period} recipients={$sent}");
    }

    /**
     * Determine if the digest should be sent to this user today
     * based on their digest_frequency and digest_day preferences.
     */
    private function shouldSendToday(User $user): bool
    {
        return match ($user->digest_frequency) {
            'daily'   => true,
            'weekly'  => strtolower(now()->format('l')) === strtolower($user->digest_day ?? ''),
            'monthly' => now()->day === 1,
            default   => false,
        };
    }
}
