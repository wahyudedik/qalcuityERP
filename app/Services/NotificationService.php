<?php

namespace App\Services;

use App\Models\AssetMaintenance;
use App\Models\Budget;
use App\Models\ErpNotification;
use App\Models\Invoice;
use App\Models\ProductBatch;
use App\Models\ProductStock;
use App\Models\Employee;
use App\Models\EmployeeReport;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\AssetMaintenanceDueNotification;
use App\Notifications\BudgetExceededNotification;
use App\Notifications\InvoiceOverdueNotification;
use App\Notifications\LowStockEmailNotification;
use App\Notifications\TrialExpiryNotification;

class NotificationService
{
    /**
     * Check if a specific user has the notification type enabled for the given channel.
     * Returns true (enabled) by default when no preference record exists.
     * Only call this when there is a specific target user — tenant-wide notifications skip this.
     */
    private function shouldNotify(int $userId, string $type, string $channel = 'in_app'): bool
    {
        return \App\Models\NotificationPreference::isEnabled($userId, $type, $channel);
    }

    /**
     * Cek stok di bawah minimum dan buat notifikasi.
     */
    public function checkLowStock(int $tenantId): int
    {
        $lowStocks = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->whereColumn('quantity', '<=', 'products.stock_min')
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*')
            ->get();

        if ($lowStocks->isEmpty()) return 0;

        // Kirim ke semua admin & manager tenant
        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        $count = 0;
        $emailItems = [];

        foreach ($lowStocks as $stock) {
            // Hindari duplikat notifikasi hari ini
            $exists = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', 'low_stock')
                ->where('data->product_id', $stock->product_id)
                ->where('data->warehouse_id', $stock->warehouse_id)
                ->whereDate('created_at', today())
                ->exists();

            if ($exists) continue;

            foreach ($recipients as $userId) {
                if ($this->shouldNotify($userId, 'low_stock', 'in_app')) {
                    ErpNotification::create([
                        'tenant_id' => $tenantId,
                        'user_id'   => $userId,
                        'type'      => 'low_stock',
                        'module'    => 'inventory',
                        'title'     => '⚠️ Stok Menipis',
                        'body'      => "Stok **{$stock->product->name}** di gudang **{$stock->warehouse->name}** tinggal {$stock->quantity} {$stock->product->unit} (min: {$stock->product->stock_min}).",
                        'data'      => [
                            'product_id'   => $stock->product_id,
                            'warehouse_id' => $stock->warehouse_id,
                            'quantity'     => $stock->quantity,
                            'stock_min'    => $stock->product->stock_min,
                        ],
                    ]);
                }
            }

            $emailItems[] = [
                'product' => $stock->product->name,
                'qty'     => $stock->quantity,
                'unit'    => $stock->product->unit ?? 'pcs',
                'min'     => $stock->product->stock_min,
            ];
            $count++;
        }

        // Kirim 1 email ringkasan per hari ke admin/manager
        if (!empty($emailItems)) {
            $adminUsers = User::whereIn('id', $recipients)->get();
            foreach ($adminUsers as $admin) {
                if ($this->shouldNotify($admin->id, 'low_stock', 'email')) {
                    $admin->notify(new LowStockEmailNotification($emailItems));
                }
            }
        }

        return $count;
    }

    /**
     * Cek laporan karyawan yang belum masuk dan buat notifikasi.
     */
    public function checkMissingReports(int $tenantId, string $type = 'weekly'): int
    {
        $periodStart = $type === 'weekly'
            ? now()->startOfWeek()->toDateString()
            : now()->startOfMonth()->toDateString();

        $submitted = EmployeeReport::where('tenant_id', $tenantId)
            ->where('type', $type)
            ->where('period_start', $periodStart)
            ->whereIn('status', ['submitted', 'reviewed'])
            ->pluck('employee_id');

        $missing = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNotIn('id', $submitted)
            ->get();

        if ($missing->isEmpty()) return 0;

        // Kirim ke admin
        $admins = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        $exists = ErpNotification::where('tenant_id', $tenantId)
            ->where('type', 'missing_report')
            ->where('data->period_start', $periodStart)
            ->where('data->report_type', $type)
            ->whereDate('created_at', today())
            ->exists();

        if ($exists) return 0;

        $names = $missing->pluck('name')->take(5)->implode(', ');
        $more  = $missing->count() > 5 ? ' dan ' . ($missing->count() - 5) . ' lainnya' : '';

        foreach ($admins as $userId) {
            if ($this->shouldNotify($userId, 'missing_report', 'in_app')) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $userId,
                    'type'      => 'missing_report',
                    'module'    => 'hrm',
                    'title'     => '📋 Laporan Belum Masuk',
                    'body'      => "{$missing->count()} karyawan belum mengumpulkan laporan {$type}: {$names}{$more}.",
                    'data'      => [
                        'report_type'  => $type,
                        'period_start' => $periodStart,
                        'count'        => $missing->count(),
                        'employees'    => $missing->pluck('name')->toArray(),
                    ],
                ]);
            }
        }

        return $missing->count();
    }

    /**
     * Jalankan semua pengecekan untuk satu tenant.
     */
    public function runChecksForTenant(int $tenantId): array
    {
        return [
            'low_stock'              => $this->checkLowStock($tenantId),
            'missing_reports'        => $this->checkMissingReports($tenantId, 'weekly'),
            'invoice_overdue'        => $this->checkInvoiceOverdue($tenantId),
            'asset_maintenance_due'  => $this->checkAssetMaintenanceDue($tenantId),
            'budget_exceeded'        => $this->checkBudgetExceeded($tenantId),
            'product_expiry'         => $this->checkProductExpiry($tenantId),
        ];
    }

    // ─── Invoice Overdue ──────────────────────────────────────────────────────

    public function checkInvoiceOverdue(int $tenantId): int
    {
        $overdueInvoices = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->with('customer')
            ->get();

        if ($overdueInvoices->isEmpty()) return 0;

        // Satu notifikasi batch per hari
        $alreadySent = ErpNotification::where('tenant_id', $tenantId)
            ->where('type', 'invoice_overdue_summary')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySent) return 0;

        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        if ($recipients->isEmpty()) return 0;

        $tenant      = Tenant::find($tenantId);
        $totalAmount = $overdueInvoices->sum('remaining_amount');
        $count       = $overdueInvoices->count();

        $invoiceData = $overdueInvoices->map(fn($inv) => [
            'number'      => $inv->number,
            'customer'    => $inv->customer?->name ?? '-',
            'amount'      => (float) $inv->remaining_amount,
            'days_overdue' => $inv->daysOverdue(),
        ])->toArray();

        foreach ($recipients as $user) {
            if ($this->shouldNotify($user->id, 'invoice_overdue', 'in_app')) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $user->id,
                    'type'      => 'invoice_overdue_summary',
                    'module'    => 'finance',
                    'title'     => "⚠️ {$count} Invoice Jatuh Tempo",
                    'body'      => "{$count} invoice senilai Rp " . number_format($totalAmount, 0, ',', '.') . " belum dibayar.",
                    'data'      => ['count' => $count, 'total_amount' => $totalAmount],
                ]);
            }

            if ($this->shouldNotify($user->id, 'invoice_overdue', 'email')) {
                $user->notify(new InvoiceOverdueNotification($invoiceData, $tenant?->name ?? 'ERP'));
            }
        }

        return $count;
    }

    // ─── Asset Maintenance Due ────────────────────────────────────────────────

    public function checkAssetMaintenanceDue(int $tenantId): int
    {
        // Maintenance yang terlambat atau jatuh tempo dalam 7 hari ke depan
        $maintenances = AssetMaintenance::where('tenant_id', $tenantId)
            ->whereIn('status', ['scheduled', 'pending'])
            ->where('scheduled_date', '<=', now()->addDays(7))
            ->with('asset')
            ->get();

        if ($maintenances->isEmpty()) return 0;

        $alreadySent = ErpNotification::where('tenant_id', $tenantId)
            ->where('type', 'asset_maintenance_due')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySent) return 0;

        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        if ($recipients->isEmpty()) return 0;

        $tenant = Tenant::find($tenantId);
        $count  = $maintenances->count();

        $items = $maintenances->map(fn($m) => [
            'asset_name'     => $m->asset?->name ?? '-',
            'type'           => $m->type,
            'scheduled_date' => $m->scheduled_date->format('d M Y'),
            'days_until'     => (int) now()->startOfDay()->diffInDays($m->scheduled_date, false),
        ])->toArray();

        $overdueCount = count(array_filter($items, fn($i) => $i['days_until'] < 0));
        $title = $overdueCount > 0
            ? "🔧 {$overdueCount} Pemeliharaan Aset Terlambat"
            : "🔧 {$count} Jadwal Pemeliharaan Aset Mendatang";

        foreach ($recipients as $user) {
            if ($this->shouldNotify($user->id, 'asset_maintenance_due', 'in_app')) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $user->id,
                    'type'      => 'asset_maintenance_due',
                    'module'    => 'hrm',
                    'title'     => $title,
                    'body'      => "{$count} jadwal pemeliharaan aset perlu perhatian.",
                    'data'      => ['count' => $count, 'overdue' => $overdueCount],
                ]);
            }

            if ($this->shouldNotify($user->id, 'asset_maintenance_due', 'email')) {
                $user->notify(new AssetMaintenanceDueNotification($items, $tenant?->name ?? 'ERP'));
            }
        }

        return $count;
    }

    // ─── Budget Exceeded ──────────────────────────────────────────────────────

    public function checkBudgetExceeded(int $tenantId): int
    {
        $currentPeriod = now()->format('Y-m');

        // Budget yang sudah ≥ 80% terpakai di periode ini
        $budgets = Budget::where('tenant_id', $tenantId)
            ->where('period', $currentPeriod)
            ->where('amount', '>', 0)
            ->whereRaw('(realized / amount * 100) >= 80')
            ->get();

        if ($budgets->isEmpty()) return 0;

        $alreadySent = ErpNotification::where('tenant_id', $tenantId)
            ->where('type', 'budget_alert')
            ->whereDate('created_at', today())
            ->exists();

        if ($alreadySent) return 0;

        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->get();

        if ($recipients->isEmpty()) return 0;

        $tenant   = Tenant::find($tenantId);
        $exceeded = $budgets->filter(fn($b) => $b->usage_percent >= 100);
        $warning  = $budgets->filter(fn($b) => $b->usage_percent >= 80 && $b->usage_percent < 100);

        $title = $exceeded->isNotEmpty()
            ? "💰 {$exceeded->count()} Anggaran Terlampaui"
            : "💰 {$warning->count()} Anggaran Hampir Habis";

        $budgetData = $budgets->map(fn($b) => [
            'name'          => $b->name,
            'department'    => $b->department ?? '-',
            'amount'        => $b->amount,
            'realized'      => $b->realized,
            'usage_percent' => $b->usage_percent,
            'period'        => $b->period,
        ])->toArray();

        foreach ($recipients as $user) {
            if ($this->shouldNotify($user->id, 'budget_alert', 'in_app')) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $user->id,
                    'type'      => 'budget_alert',
                    'module'    => 'finance',
                    'title'     => $title,
                    'body'      => $exceeded->isNotEmpty()
                        ? "{$exceeded->count()} anggaran telah terlampaui. Segera tinjau pengeluaran."
                        : "{$warning->count()} anggaran sudah di atas 80%. Perhatikan pengeluaran.",
                    'data'      => [
                        'exceeded_count' => $exceeded->count(),
                        'warning_count'  => $warning->count(),
                        'period'         => $currentPeriod,
                    ],
                ]);
            }

            if ($this->shouldNotify($user->id, 'budget_alert', 'email')) {
                $user->notify(new BudgetExceededNotification($budgetData, $tenant?->name ?? 'ERP'));
            }
        }

        return $budgets->count();
    }

    /**
     * Cek trial yang akan berakhir dan kirim notifikasi (7 hari & 1 hari sebelum).
     */
    public function checkTrialExpiry(): int
    {
        $count = 0;

        Tenant::where('plan', 'trial')
            ->where('is_active', true)
            ->whereNotNull('trial_ends_at')
            ->get()
            ->each(function (Tenant $tenant) use (&$count) {
                $daysLeft = (int) now()->diffInDays($tenant->trial_ends_at, false);

                if (!in_array($daysLeft, [7, 3, 1])) return;

                // Cek sudah kirim hari ini
                $alreadySent = ErpNotification::where('tenant_id', $tenant->id)
                    ->where('type', 'trial_expiry')
                    ->where('data->days_left', $daysLeft)
                    ->whereDate('created_at', today())
                    ->exists();

                if ($alreadySent) return;

                $admins = User::where('tenant_id', $tenant->id)
                    ->whereIn('role', ['admin'])
                    ->get();

                foreach ($admins as $admin) {
                    if ($this->shouldNotify($admin->id, 'trial_expiry', 'email')) {
                        $admin->notify(new TrialExpiryNotification($tenant, $daysLeft));
                    }

                    if ($this->shouldNotify($admin->id, 'trial_expiry', 'in_app')) {
                        ErpNotification::create([
                            'tenant_id' => $tenant->id,
                            'user_id'   => $admin->id,
                            'type'      => 'trial_expiry',
                            'module'    => 'system',
                            'title'     => "⏰ Trial berakhir dalam {$daysLeft} hari",
                            'body'      => "Trial gratis Anda akan berakhir dalam {$daysLeft} hari. Upgrade sekarang untuk tetap menggunakan Qalcuity ERP.",
                            'data'      => ['days_left' => $daysLeft],
                        ]);
                    }
                }

                $count++;
            });

        return $count;
    }

    /**
     * Cek batch produk yang akan/sudah expired dan buat notifikasi.
     * Hanya untuk produk dengan has_expiry = true.
     * Alert dikirim sesuai expiry_alert_days per produk (default 2 hari).
     */
    public function checkProductExpiry(int $tenantId): int
    {
        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        if ($recipients->isEmpty()) return 0;

        $count = 0;

        // 1. Batch yang AKAN expired (dalam window alert per produk)
        $expiringSoon = ProductBatch::with(['product', 'warehouse'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '>=', today())
            ->whereHas('product', fn($q) => $q->where('has_expiry', true)->where('is_active', true))
            ->get()
            ->filter(fn($batch) => $batch->daysUntilExpiry() <= ($batch->product->expiry_alert_days ?? 2));

        foreach ($expiringSoon as $batch) {
            $days     = $batch->daysUntilExpiry();
            $notifKey = "expiry_soon_{$batch->id}_" . today()->format('Ymd');

            $exists = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', $notifKey)
                ->whereDate('created_at', today())
                ->exists();

            if ($exists) continue;

            $label = $days === 0 ? 'hari ini' : "dalam {$days} hari";

            foreach ($recipients as $userId) {
                if ($this->shouldNotify($userId, 'product_expiry', 'in_app')) {
                    ErpNotification::create([
                        'tenant_id' => $tenantId,
                        'user_id'   => $userId,
                        'type'      => $notifKey,
                        'module'    => 'inventory',
                        'title'     => '⏰ Produk Akan Expired',
                        'body'      => "Batch **{$batch->batch_number}** produk **{$batch->product->name}** " .
                            "({$batch->quantity} {$batch->product->unit}) di gudang **{$batch->warehouse->name}** " .
                            "akan expired {$label} ({$batch->expiry_date->format('d/m/Y')}).",
                        'data'      => [
                            'batch_id'    => $batch->id,
                            'product_id'  => $batch->product_id,
                            'days_left'   => $days,
                            'expiry_date' => $batch->expiry_date->toDateString(),
                            'quantity'    => $batch->quantity,
                        ],
                    ]);
                }
            }
            $count++;
        }

        // 2. Batch yang SUDAH expired tapi status masih active (perlu tindakan)
        $alreadyExpired = ProductBatch::with(['product', 'warehouse'])
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', today())
            ->whereHas('product', fn($q) => $q->where('has_expiry', true)->where('is_active', true))
            ->get();

        // Auto-update status ke expired
        foreach ($alreadyExpired as $batch) {
            $batch->update(['status' => 'expired']);

            $notifKey = "expiry_expired_{$batch->id}";
            $exists   = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', $notifKey)
                ->exists();

            if ($exists) continue;

            foreach ($recipients as $userId) {
                if ($this->shouldNotify($userId, 'product_expiry', 'in_app')) {
                    ErpNotification::create([
                        'tenant_id' => $tenantId,
                        'user_id'   => $userId,
                        'type'      => $notifKey,
                        'module'    => 'inventory',
                        'title'     => '🔴 Produk Expired',
                        'body'      => "Batch **{$batch->batch_number}** produk **{$batch->product->name}** " .
                            "({$batch->quantity} {$batch->product->unit}) di gudang **{$batch->warehouse->name}** " .
                            "sudah EXPIRED sejak {$batch->expiry_date->format('d/m/Y')}. Segera lakukan tindakan.",
                        'data'      => [
                            'batch_id'    => $batch->id,
                            'product_id'  => $batch->product_id,
                            'expiry_date' => $batch->expiry_date->toDateString(),
                            'quantity'    => $batch->quantity,
                        ],
                    ]);
                }
            }
            $count++;
        }

        return $count;
    }

    /**
     * Jalankan semua pengecekan untuk semua tenant aktif — dispatch sebagai jobs.
     */
    public function runAllChecks(): array
    {
        $results = [];

        Tenant::where('is_active', true)->each(function (Tenant $tenant) use (&$results) {
            \App\Jobs\SendErpNotificationBatch::dispatch($tenant->id, 'all');
            $results[$tenant->slug] = ['queued' => true];
        });

        return $results;
    }
}
