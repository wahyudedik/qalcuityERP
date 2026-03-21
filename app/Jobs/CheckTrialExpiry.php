<?php

namespace App\Jobs;

use App\Models\ErpNotification;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\TrialExpiryNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckTrialExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public function handle(): void
    {
        // Tenant trial yang berakhir dalam 3 hari ke depan
        $expiringSoon = Tenant::where('is_active', true)
            ->where('plan', 'trial')
            ->whereNotNull('trial_ends_at')
            ->whereBetween('trial_ends_at', [now(), now()->addDays(3)])
            ->get();

        foreach ($expiringSoon as $tenant) {
            $daysLeft = (int) now()->diffInDays($tenant->trial_ends_at, false);
            $this->notifyTenant($tenant, "trial_expiring", "⏰ Trial Akan Berakhir",
                "Trial Anda akan berakhir dalam **{$daysLeft} hari** ({$tenant->trial_ends_at->format('d M Y')}). Upgrade sekarang untuk melanjutkan akses."
            );
        }

        // Tenant plan berbayar yang berakhir dalam 7 hari
        $paidExpiring = Tenant::where('is_active', true)
            ->whereNotIn('plan', ['trial'])
            ->whereNotNull('plan_expires_at')
            ->whereBetween('plan_expires_at', [now(), now()->addDays(7)])
            ->get();

        foreach ($paidExpiring as $tenant) {
            $daysLeft = (int) now()->diffInDays($tenant->plan_expires_at, false);
            $this->notifyTenant($tenant, "plan_expiring", "💳 Langganan Akan Berakhir",
                "Langganan **{$tenant->plan}** Anda akan berakhir dalam **{$daysLeft} hari** ({$tenant->plan_expires_at->format('d M Y')}). Perpanjang sekarang."
            );
        }

        Log::info("CheckTrialExpiry: expiring_soon={$expiringSoon->count()}, paid_expiring={$paidExpiring->count()}");
    }

    private function notifyTenant(Tenant $tenant, string $type, string $title, string $body): void
    {
        // Hindari duplikat notifikasi hari ini
        $exists = ErpNotification::where('tenant_id', $tenant->id)
            ->where('type', $type)
            ->whereDate('created_at', today())
            ->exists();

        if ($exists) return;

        $admins = User::where('tenant_id', $tenant->id)
            ->where('role', 'admin')
            ->get();

        $daysLeft = (int) now()->diffInDays($tenant->trial_ends_at ?? $tenant->plan_expires_at, false);

        foreach ($admins as $admin) {
            // In-app notification
            ErpNotification::create([
                'tenant_id' => $tenant->id,
                'user_id'   => $admin->id,
                'type'      => $type,
                'title'     => $title,
                'body'      => $body,
                'data'      => ['plan' => $tenant->plan, 'expires_at' => $tenant->trial_ends_at ?? $tenant->plan_expires_at],
            ]);

            // Email notification
            if ($type === 'trial_expiring') {
                $admin->notify(new TrialExpiryNotification($tenant, $daysLeft));
            }
        }
    }
}
