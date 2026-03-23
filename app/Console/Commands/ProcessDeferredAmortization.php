<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\DeferredItemService;
use Illuminate\Console\Command;

class ProcessDeferredAmortization extends Command
{
    protected $signature   = 'deferred:amortize {--tenant= : Proses hanya tenant tertentu}';
    protected $description = 'Post jurnal amortisasi otomatis untuk deferred revenue dan prepaid expense yang jatuh tempo.';

    public function handle(DeferredItemService $service): int
    {
        $tenantId = $this->option('tenant');

        $query = Tenant::where('is_active', true);
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();
        $totalPosted = 0;

        foreach ($tenants as $tenant) {
            // Gunakan user admin pertama sebagai system user
            $adminUser = \App\Models\User::where('tenant_id', $tenant->id)
                ->where('role', 'admin')
                ->first();

            if (! $adminUser) continue;

            $posted = $service->processAutoAmortization($tenant->id, $adminUser->id);
            $totalPosted += $posted;

            if ($posted > 0) {
                $this->info("Tenant [{$tenant->name}]: {$posted} jurnal amortisasi diposting.");
            }
        }

        $this->info("Selesai. Total {$totalPosted} jurnal amortisasi diposting.");
        return self::SUCCESS;
    }
}
