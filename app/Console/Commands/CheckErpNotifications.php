<?php

namespace App\Console\Commands;

use App\Jobs\SendErpNotificationBatch;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CheckErpNotifications extends Command
{
    protected $signature = 'erp:check-notifications';

    protected $description = 'Dispatch jobs untuk cek stok menipis dan laporan belum masuk per tenant.';

    public function handle(): void
    {
        $this->info('Dispatching ERP notification jobs...');

        $tenants = Tenant::where('is_active', true)->get();

        foreach ($tenants as $tenant) {
            SendErpNotificationBatch::dispatch($tenant->id, 'low_stock');
            $this->line("  Queued: tenant [{$tenant->slug}] low_stock check");
        }

        $this->info("Done. {$tenants->count()} jobs dispatched.");
    }
}
