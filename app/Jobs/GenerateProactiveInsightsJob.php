<?php

namespace App\Jobs;

use App\Models\PushSubscription;
use App\Models\Tenant;
use App\Services\Agent\ProactiveInsightEngine;
use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * GenerateProactiveInsightsJob — Jalankan ProactiveInsightEngine untuk semua tenant aktif.
 *
 * Dijadwalkan setiap 6 jam via routes/console.php.
 * Untuk setiap tenant, memanggil ProactiveInsightEngine::analyze() dan
 * mengirim push notification untuk insight dengan urgency high/critical
 * jika fitur push notification aktif untuk tenant tersebut.
 *
 * Requirements: 4.1, 4.6
 */
class GenerateProactiveInsightsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 300; // 5 menit — banyak tenant bisa memakan waktu

    public function handle(ProactiveInsightEngine $engine, WebPushService $webPush): void
    {
        $tenants = Tenant::where('is_active', true)->get();

        foreach ($tenants as $tenant) {
            // Skip tenant yang tidak bisa akses (trial expired, plan expired)
            if (!$tenant->canAccess()) {
                continue;
            }

            try {
                $insights = $engine->analyze($tenant->id);

                if (empty($insights)) {
                    continue;
                }

                Log::info("GenerateProactiveInsightsJob: tenant={$tenant->id} insights=" . count($insights));

                // Kirim push notification untuk insight high/critical jika push aktif
                $this->sendPushNotificationsIfEnabled($tenant->id, $insights, $webPush);

            } catch (\Throwable $e) {
                // Satu tenant gagal tidak menghentikan tenant lain
                Log::error("GenerateProactiveInsightsJob: gagal untuk tenant={$tenant->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Kirim push notification untuk insight dengan urgency high atau critical,
     * hanya jika tenant memiliki push subscription aktif.
     *
     * @param  \App\Models\ProactiveInsight[]  $insights
     */
    private function sendPushNotificationsIfEnabled(int $tenantId, array $insights, WebPushService $webPush): void
    {
        // Cek apakah ada push subscription aktif untuk tenant ini
        $hasPushSubscriptions = PushSubscription::where('tenant_id', $tenantId)->exists();

        if (!$hasPushSubscriptions) {
            return;
        }

        // Hanya kirim jika WebPush terkonfigurasi (VAPID keys tersedia)
        if (!$webPush->isConfigured()) {
            return;
        }

        foreach ($insights as $insight) {
            if (!in_array($insight->urgency, ['high', 'critical'], true)) {
                continue;
            }

            try {
                $urgencyLabel = $insight->urgency === 'critical' ? '🚨 Kritis' : '⚠️ Penting';
                $title = "{$urgencyLabel}: {$insight->title}";
                $body  = $insight->description;
                $url   = '/agent/insights';
                $tag   = "proactive-insight-{$insight->id}";

                $sent = $webPush->sendToTenant($tenantId, $title, $body, $url, $tag);

                Log::info("GenerateProactiveInsightsJob: push sent tenant={$tenantId} insight={$insight->id} urgency={$insight->urgency} recipients={$sent}");

            } catch (\Throwable $e) {
                Log::warning("GenerateProactiveInsightsJob: push notification gagal untuk insight={$insight->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
