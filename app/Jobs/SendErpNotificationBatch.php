<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendErpNotificationBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $checkType, // 'low_stock' | 'missing_reports' | 'all'
        public readonly string $reportType = 'weekly',
    ) {}

    public function handle(NotificationService $service): void
    {
        match ($this->checkType) {
            'low_stock'             => $service->checkLowStock($this->tenantId),
            'missing_reports'       => $service->checkMissingReports($this->tenantId, $this->reportType),
            'invoice_overdue'       => $service->checkInvoiceOverdue($this->tenantId),
            'asset_maintenance_due' => $service->checkAssetMaintenanceDue($this->tenantId),
            'budget_exceeded'       => $service->checkBudgetExceeded($this->tenantId),
            'product_expiry'        => $service->checkProductExpiry($this->tenantId),
            default                 => $service->runChecksForTenant($this->tenantId),
        };

        Log::info("SendErpNotificationBatch: tenant={$this->tenantId} type={$this->checkType}");
    }
}
