<?php

namespace App\Jobs;

use App\Enums\AiUseCase;
use App\Models\AiProviderSwitchLog;
use App\Models\AiUsageCostLog;
use App\Models\User;
use App\Notifications\AiFallbackAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Job untuk memeriksa persentase fallback event per use case dan mengirim alert ke SuperAdmin.
 *
 * Requirements: 10.3
 */
class CheckAiFallbackRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $threshold = 20; // 20% threshold sesuai requirements
        $period = now()->subHour(); // 1 jam terakhir

        // Ambil semua use case yang terdaftar
        $useCases = array_column(AiUseCase::cases(), 'value');

        foreach ($useCases as $useCase) {
            // Hitung total request untuk use case ini dalam 1 jam terakhir
            $totalRequests = AiUsageCostLog::where('use_case', $useCase)
                ->where('created_at', '>=', $period)
                ->count();

            // Skip jika tidak ada request (hindari division by zero)
            if ($totalRequests === 0) {
                continue;
            }

            // Hitung jumlah fallback event untuk use case ini dalam 1 jam terakhir
            $fallbackCount = AiProviderSwitchLog::where('use_case', $useCase)
                ->where('created_at', '>=', $period)
                ->count();

            // Hitung persentase fallback
            $fallbackPercent = round(($fallbackCount / $totalRequests) * 100, 1);

            // Cek apakah melebihi threshold
            if ($fallbackPercent > $threshold) {
                // Cek apakah sudah pernah dikirim notifikasi dalam 1 jam terakhir untuk use case ini
                $cacheKey = "ai_fallback_alert_sent:{$useCase}:".now()->format('Y-m-d-H');

                if (Cache::has($cacheKey)) {
                    Log::debug("[CheckAiFallbackRates] Notifikasi untuk use case {$useCase} jam ini sudah dikirim, skip.");

                    continue;
                }

                // Kirim notifikasi ke semua SuperAdmin
                $superAdmins = User::where('role', User::ROLE_SUPER_ADMIN)->get();

                foreach ($superAdmins as $admin) {
                    $admin->notify(new AiFallbackAlertNotification(
                        useCase: $useCase,
                        totalRequests: $totalRequests,
                        fallbackCount: $fallbackCount,
                        fallbackPercent: $fallbackPercent,
                        period: '1 jam terakhir',
                    ));
                }

                // Tandai bahwa notifikasi sudah dikirim untuk jam ini
                // Cache selama 1 jam
                Cache::put($cacheKey, true, now()->addHour());

                Log::warning("[CheckAiFallbackRates] Alert fallback dikirim untuk use case {$useCase}. Fallback: {$fallbackPercent}% ({$fallbackCount}/{$totalRequests} requests)");
            }
        }
    }
}
