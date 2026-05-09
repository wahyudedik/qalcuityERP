<?php

namespace App\Jobs;

use App\Models\Asset;
use App\Models\AssetDepreciation;
use App\Models\ErpNotification;
use App\Models\User;
use App\Services\GlPostingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunAssetDepreciation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 120;

    public function __construct(public readonly int $tenantId) {}

    public function handle(GlPostingService $gl): void
    {
        $period = now()->format('Y-m');

        $assets = Asset::where('tenant_id', $this->tenantId)
            ->where('status', 'active')
            ->where('current_value', '>', 0)
            ->get();

        $processed = 0;
        $totalDep = 0;
        $assetLines = [];
        $depIds = [];

        foreach ($assets as $asset) {
            // Cegah duplikasi
            $alreadyRun = AssetDepreciation::where('asset_id', $asset->id)
                ->where('period', $period)
                ->exists();

            if ($alreadyRun) {
                continue;
            }

            $depreciation = $asset->monthlyDepreciation();
            if ($depreciation <= 0) {
                continue;
            }

            $newValue = max($asset->salvage_value, $asset->current_value - $depreciation);
            $actualDep = $asset->current_value - $newValue;

            if ($actualDep <= 0) {
                continue;
            }

            $dep = AssetDepreciation::create([
                'tenant_id' => $this->tenantId,
                'asset_id' => $asset->id,
                'period' => $period,
                'depreciation_amount' => $actualDep,
                'book_value_after' => $newValue,
            ]);

            $asset->update(['current_value' => $newValue]);

            $totalDep += $actualDep;
            $assetLines[] = ['asset_name' => $asset->name, 'amount' => $actualDep];
            $depIds[] = $dep->id;
            $processed++;
        }

        if ($processed > 0) {
            // GL Auto-Posting: Dr Beban Penyusutan / Cr Akumulasi Penyusutan
            // System user ID = 0 (job context, no authenticated user)
            $admin = User::where('tenant_id', $this->tenantId)->where('role', 'admin')->first();
            $userId = $admin?->id ?? 0;

            $glResult = $gl->postDepreciation(
                tenantId: $this->tenantId,
                userId: $userId,
                period: $period,
                totalAmount: $totalDep,
                assetLines: $assetLines,
            );

            // Link journal entry ID ke semua AssetDepreciation records
            if ($glResult->isSuccess() && $glResult->journal) {
                AssetDepreciation::whereIn('id', $depIds)
                    ->update(['journal_entry_id' => $glResult->journal->id]);
            } elseif ($glResult->isFailed()) {
                Log::warning("RunAssetDepreciation GL failed for tenant {$this->tenantId}: ".$glResult->reason);
            }

            // Notifikasi admin
            if ($admin) {
                $glStatus = $glResult->isSuccess()
                    ? " Jurnal GL: {$glResult->journal->number}."
                    : ($glResult->isFailed() ? " ⚠️ Jurnal GL gagal: {$glResult->reason}" : '');

                ErpNotification::create([
                    'tenant_id' => $this->tenantId,
                    'user_id' => $admin->id,
                    'type' => 'asset_depreciation',
                    'title' => '🏭 Depresiasi Aset Bulanan',
                    'body' => "Depresiasi periode {$period} untuk {$processed} aset. Total: Rp ".number_format($totalDep, 0, ',', '.').".{$glStatus}",
                    'data' => ['period' => $period, 'count' => $processed, 'total' => $totalDep],
                ]);
            }
        }

        Log::info("RunAssetDepreciation: tenant={$this->tenantId} period={$period} processed={$processed} total_dep={$totalDep}");
    }
}
