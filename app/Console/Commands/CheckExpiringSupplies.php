<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class CheckExpiringSupplies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:supplies:check-expiry
                            {--days=30 : Check supplies expiring within this many days}
                            {--tenant= : Specific tenant ID}
                            {--notify : Send notifications}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expiring medical supplies and send alerts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $tenantId = $this->option('tenant');
        $shouldNotify = $this->option('notify');

        $this->info("🔍 Checking medical supplies expiring within {$days} days...");

        $query = \App\Models\PharmacyItem::whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $expiringSupplies = $query->with(['tenant'])->get();

        if ($expiringSupplies->isEmpty()) {
            $this->info("✅ No supplies expiring within {$days} days");
            return Command::SUCCESS;
        }

        $this->warn("⚠️ Found {$expiringSupplies->count()} expiring supplies");

        // Group by tenant
        $groupedByTenant = $expiringSupplies->groupBy('tenant_id');

        foreach ($groupedByTenant as $tenantId => $supplies) {
            $this->info("\nTenant: {$tenantId}");
            $this->table(
                ['SKU', 'Name', 'Stock', 'Expiry Date', 'Days Left'],
                $supplies->map(function ($item) {
                    $daysLeft = now()->diffInDays($item->expiry_date, false);
                    return [
                        $item->sku ?? '-',
                        $item->name,
                        $item->current_stock,
                        $item->expiry_date->format('Y-m-d'),
                        "{$daysLeft} days",
                    ];
                })->toArray()
            );

            // Send notifications
            if ($shouldNotify) {
                $this->sendExpiryAlerts($supplies);
            }
        }

        // Auto-mark expired supplies
        $expiredCount = \App\Models\PharmacyItem::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now())
            ->where('status', '!=', 'expired')
            ->update(['status' => 'expired']);

        if ($expiredCount > 0) {
            $this->warn("🚫 Marked {$expiredCount} supplies as expired");
        }

        return Command::SUCCESS;
    }

    /**
     * Send expiry alert notifications
     */
    protected function sendExpiryAlerts($supplies): void
    {
        $tenantId = $supplies->first()->tenant_id;

        // Get pharmacy managers and admins
        $recipients = \App\Models\User::where('tenant_id', $tenantId)
            ->whereHas('roles', function ($q) {
                $q->whereIn('name', ['admin', 'pharmacist']);
            })
            ->get();

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            Notification::send($recipients, new \App\Notifications\Healthcare\SupplyExpiryAlert($supplies));

            Log::info('Supply expiry alerts sent', [
                'tenant_id' => $tenantId,
                'recipient_count' => $recipients->count(),
                'supply_count' => $supplies->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send supply expiry alerts', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
