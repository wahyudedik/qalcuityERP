<?php

namespace App\Jobs;

use App\Models\ErpNotification;
use App\Models\SalesOrder;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateTenantReport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 2;
    public int $timeout = 180;

    public function __construct(
        public readonly int    $tenantId,
        public readonly string $reportType, // 'monthly_summary'
        public readonly string $period,     // 'YYYY-MM'
    ) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);
        if (!$tenant) return;

        [$year, $month] = explode('-', $this->period);

        $salesTotal = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->whereNotIn('status', ['cancelled'])
            ->sum('total');

        $salesCount = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->whereNotIn('status', ['cancelled'])
            ->count();

        $income  = Transaction::where('tenant_id', $this->tenantId)
            ->where('type', 'income')
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->sum('amount');

        $expense = Transaction::where('tenant_id', $this->tenantId)
            ->where('type', 'expense')
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->sum('amount');

        $summary = [
            'period'       => $this->period,
            'sales_total'  => $salesTotal,
            'sales_count'  => $salesCount,
            'income'       => $income,
            'expense'      => $expense,
            'profit'       => $income - $expense,
            'generated_at' => now()->toIso8601String(),
        ];

        // Cache hasil selama 24 jam
        $cacheKey = "tenant_report_{$this->tenantId}_{$this->period}";
        Cache::put($cacheKey, $summary, now()->addHours(24));

        // Notifikasi admin bahwa laporan sudah siap
        $admins = User::where('tenant_id', $this->tenantId)->where('role', 'admin')->pluck('id');
        foreach ($admins as $userId) {
            ErpNotification::create([
                'tenant_id' => $this->tenantId,
                'user_id'   => $userId,
                'type'      => 'report_ready',
                'title'     => '📊 Laporan Bulanan Siap',
                'body'      => "Laporan ringkasan bulan **{$this->period}** sudah siap. Penjualan: Rp " . number_format($salesTotal, 0, ',', '.') . ", Profit: Rp " . number_format($income - $expense, 0, ',', '.'),
                'data'      => $summary,
            ]);
        }

        Log::info("GenerateTenantReport: tenant={$this->tenantId} period={$this->period}");
    }
}
