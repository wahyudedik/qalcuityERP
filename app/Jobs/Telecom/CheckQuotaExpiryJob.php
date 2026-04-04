<?php

namespace App\Jobs\Telecom;

use App\Models\TelecomSubscription;
use App\Models\HotspotUser;
use App\Models\NetworkAlert;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Scheduled job to check quota expiry and reset quotas.
 * 
 * Run daily at midnight.
 */
class CheckQuotaExpiryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->queue = 'telecom-daily';
    }

    public function handle(): void
    {
        Log::info("Starting quota expiry check");

        $this->checkSubscriptionQuotas();
        $this->checkHotspotUserQuotas();
        $this->resetExpiredQuotas();

        Log::info("Quota expiry check completed");
    }

    /**
     * Check subscription quotas.
     */
    protected function checkSubscriptionQuotas(): void
    {
        $subscriptions = TelecomSubscription::where('status', 'active')
            ->whereNotNull('quota_period_end')
            ->get();

        foreach ($subscriptions as $subscription) {
            // Check if quota period has ended
            if (now()->greaterThanOrEqualTo($subscription->quota_period_end)) {
                Log::info("Quota period ended for subscription", [
                    'subscription_id' => $subscription->id,
                    'customer' => $subscription->customer?->name
                ]);

                // Create alert
                NetworkAlert::create([
                    'tenant_id' => $subscription->tenant_id,
                    'subscription_id' => $subscription->id,
                    'alert_type' => 'quota_period_ended',
                    'severity' => 'medium',
                    'title' => "Quota Period Ended: {$subscription->customer?->name}",
                    'message' => "Quota period has ended. Waiting for reset or renewal.",
                ]);
            }

            // Check if quota exceeded
            if ($subscription->package && !$subscription->package->isUnlimited()) {
                $quotaUsedPercent = ($subscription->quota_used_bytes / $subscription->package->quota_bytes) * 100;

                if ($quotaUsedPercent >= 90 && !$subscription->quota_exceeded) {
                    // Mark as exceeded
                    $subscription->update(['quota_exceeded' => true]);

                    // Send notification
                    $this->sendQuotaWarning($subscription, $quotaUsedPercent);
                }
            }
        }
    }

    /**
     * Check hotspot user quotas.
     */
    protected function checkHotspotUserQuotas(): void
    {
        $hotspotUsers = HotspotUser::where('is_active', true)
            ->where('quota_bytes', '>', 0)
            ->get();

        foreach ($hotspotUsers as $user) {
            if ($user->isQuotaExceeded()) {
                Log::info("Hotspot user quota exceeded", [
                    'username' => $user->username,
                    'quota_used' => $user->quota_used_bytes,
                    'quota_limit' => $user->quota_bytes,
                ]);

                // Disconnect user from router
                try {
                    $adapter = \App\Services\Telecom\RouterAdapterFactory::create($user->device);
                    $adapter->disconnectUser($user->username);

                    $user->markAsOffline(0);
                } catch (\Exception $e) {
                    Log::error("Failed to disconnect user", [
                        'username' => $user->username,
                        'error' => $e->getMessage()
                    ]);
                }

                // Create alert
                NetworkAlert::create([
                    'tenant_id' => $user->tenant_id,
                    'device_id' => $user->device_id,
                    'alert_type' => 'user_quota_exceeded',
                    'severity' => 'low',
                    'title' => "User Quota Exceeded: {$user->username}",
                    'message' => "User has exceeded their quota limit and has been disconnected.",
                ]);
            }
        }
    }

    /**
     * Reset quotas for subscriptions with new period.
     */
    protected function resetExpiredQuotas(): void
    {
        $subscriptions = TelecomSubscription::where('status', 'active')
            ->whereNotNull('quota_period_end')
            ->where('quota_period_end', '<=', now())
            ->get();

        foreach ($subscriptions as $subscription) {
            $subscription->resetQuota();

            Log::info("Quota reset for subscription", [
                'subscription_id' => $subscription->id,
                'customer' => $subscription->customer?->name
            ]);
        }

        // Reset hotspot user quotas
        $hotspotUsers = HotspotUser::where('is_active', true)
            ->whereNotNull('quota_reset_at')
            ->where('quota_reset_at', '<=', now())
            ->get();

        foreach ($hotspotUsers as $user) {
            $user->resetQuota();

            Log::info("Quota reset for hotspot user", [
                'username' => $user->username
            ]);
        }
    }

    /**
     * Send quota warning notification.
     */
    protected function sendQuotaWarning(TelecomSubscription $subscription, float $usedPercent): void
    {
        try {
            $notificationService = app(NotificationService::class);

            // Notify admin
            $adminUsers = \App\Models\User::where('tenant_id', $subscription->tenant_id)
                ->where('role', 'admin')
                ->get();

            foreach ($adminUsers as $admin) {
                $notificationService->sendToUser($admin, [
                    'title' => "Kuota Hampir Habis: {$subscription->customer?->name}",
                    'message' => "Penggunaan kuota sudah mencapai " . round($usedPercent, 2) . "%",
                    'type' => 'warning',
                    'action_url' => route('telecom.subscriptions.show', $subscription->id),
                ]);
            }

            // Optionally notify customer via email/SMS if configured
            if ($subscription->customer?->email) {
                // Send email notification
                \Mail::raw(
                    "Penggunaan kuota internet Anda sudah mencapai " . round($usedPercent, 2) . "%. " .
                    "Silakan upgrade paket atau beli tambahan kuota.",
                    function ($message) use ($subscription) {
                        $message->to($subscription->customer->email)
                            ->subject('Peringatan Kuota Internet');
                    }
                );
            }

        } catch (\Exception $e) {
            Log::error("Failed to send quota warning", [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
