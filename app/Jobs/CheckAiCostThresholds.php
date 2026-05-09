<?php

namespace App\Jobs;

use App\Models\AiUsageCostLog;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AiCostThresholdExceededNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk memeriksa threshold biaya AI per tenant dan mengirim notifikasi ke SuperAdmin.
 *
 * Requirements: 6.10
 */
class CheckAiCostThresholds implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Ambil threshold dari config (dalam IDR)
        $threshold = config('ai.cost_threshold_idr', 1000000); // Default: Rp 1.000.000

        if ($threshold <= 0) {
            Log::debug('[CheckAiCostThresholds] Threshold tidak dikonfigurasi atau <= 0, skip check.');

            return;
        }

        $period = now()->format('Y-m');
        $startOfMonth = Carbon::parse($period.'-01')->startOfDay();
        $endOfMonth = now()->endOfDay();

        // Ambil semua tenant aktif
        $tenants = Tenant::where('is_active', true)->get();

        foreach ($tenants as $tenant) {
            // Hitung total biaya AI tenant bulan ini
            $totalCost = AiUsageCostLog::where('tenant_id', $tenant->id)
                ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->sum('estimated_cost_idr');

            // Cek apakah melebihi threshold
            if ($totalCost > $threshold) {
                // Cek apakah sudah pernah dikirim notifikasi untuk periode ini
                $cacheKey = "ai_cost_alert_sent:{$tenant->id}:{$period}";

                if (Cache::has($cacheKey)) {
                    Log::debug("[CheckAiCostThresholds] Notifikasi untuk tenant {$tenant->id} periode {$period} sudah dikirim, skip.");

                    continue;
                }

                // Kirim notifikasi ke semua SuperAdmin
                $superAdmins = User::where('role', User::ROLE_SUPER_ADMIN)->get();

                foreach ($superAdmins as $admin) {
                    $admin->notify(new AiCostThresholdExceededNotification(
                        tenantName: $tenant->name,
                        tenantId: $tenant->id,
                        totalCost: $totalCost,
                        threshold: $threshold,
                        period: $period,
                    ));
                }

                // Tandai bahwa notifikasi sudah dikirim untuk periode ini
                // Cache sampai akhir bulan + 1 hari
                $cacheExpiry = Carbon::parse($period.'-01')->endOfMonth()->addDay();
                Cache::put($cacheKey, true, $cacheExpiry);

                Log::info("[CheckAiCostThresholds] Notifikasi threshold biaya AI dikirim untuk tenant {$tenant->name} (ID: {$tenant->id}). Total: Rp {$totalCost}, Threshold: Rp {$threshold}");
            }
        }
    }
}
