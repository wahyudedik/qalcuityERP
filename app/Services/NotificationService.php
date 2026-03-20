<?php

namespace App\Services;

use App\Models\ErpNotification;
use App\Models\ProductStock;
use App\Models\Employee;
use App\Models\EmployeeReport;
use App\Models\Tenant;
use App\Models\User;

class NotificationService
{
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
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $userId,
                    'type'      => 'low_stock',
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
            $count++;
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
            ErpNotification::create([
                'tenant_id' => $tenantId,
                'user_id'   => $userId,
                'type'      => 'missing_report',
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

        return $missing->count();
    }

    /**
     * Jalankan semua pengecekan untuk satu tenant.
     */
    public function runChecksForTenant(int $tenantId): array
    {
        return [
            'low_stock'       => $this->checkLowStock($tenantId),
            'missing_reports' => $this->checkMissingReports($tenantId, 'weekly'),
        ];
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
