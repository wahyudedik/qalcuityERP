<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\ErpNotification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAssetDepreciation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public readonly int $tenantId) {}

    public function handle(): void
    {
        $period = now()->format('Y-m'); // e.g. "2026-03"

        $assets = Asset::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->where('current_value', '>', 0)
            ->get();

        $processed = 0;

        foreach ($assets as $asset) {
            // Cegah duplikasi — skip jika periode ini sudah ada
            $alreadyRun = AssetDepreciation::where('asset_id', $asset->id)
                ->where('period', $period)
                ->exists();

            if ($alreadyRun) continue;

            $depreciation = $asset->monthlyDepreciation();
            if ($depreciation <= 0) continue;

            // Nilai buku tidak boleh di bawah salvage value
            $newValue = max($asset->salvage_value, $asset->current_value - $depreciation);
            $actualDep = $asset->current_value - $newValue;

            if ($actualDep <= 0) continue;

            AssetDepreciation::create([
                'tenant_id'          => $this->tenantId,
                'asset_id'           => $asset->id,
                'period'             => $period,
                'depreciation_amount'=> $actualDep,
                'book_value_after'   => $newValue,
            ]);

            $asset->update(['current_value' => $newValue]);
            $processed++;
        }

        if ($processed > 0) {
            // Notifikasi admin
            $admin = User::where('tenant_id', $this->tenantId)
                ->where('role', 'admin')
                ->first();

            if ($admin) {
                ErpNotification::create([
                    'tenant_id' => $this->tenantId,
                    'user_id'   => $admin->id,
                    'type'      => 'asset_depreciation',
                    'title'     => '🏭 Depresiasi Aset Bulanan',
                    'body'      => "Depresiasi otomatis periode {$period} telah dijalankan untuk {$processed} aset.",
                    'data'      => ['period' => $period, 'count' => $processed],
                ]);
            }
        }

        Log::info("RunAssetDepreciation: tenant={$this->tenantId} period={$period} processed={$processed}");
    }
}
