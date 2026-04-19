<?php

namespace App\Services\Agent;

use App\DTOs\Agent\ErpContext;
use App\Models\AccountingPeriod;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AgentContextBuilder — Task 3
 *
 * Membangun ERP_Context dari data tenant saat ini untuk diinjeksikan
 * ke setiap sesi Agent. Menggunakan parallel queries untuk efisiensi
 * dan harus selesai dalam < 3 detik.
 *
 * Requirements: 2.1, 2.2, 2.4, 2.5
 */
class AgentContextBuilder
{
    /** Timeout per query dalam detik */
    private const QUERY_TIMEOUT_SECONDS = 2;

    /** Batas total waktu build dalam detik */
    private const BUILD_TIMEOUT_SECONDS = 3;

    /**
     * Bangun ERP_Context lengkap untuk tenant.
     * Harus selesai dalam < 3 detik.
     * Menggunakan parallel queries untuk efisiensi.
     *
     * @param  int    $tenantId
     * @param  array  $activeModules  Daftar modul aktif tenant
     * @return ErpContext
     */
    public function build(int $tenantId, array $activeModules): ErpContext
    {
        $startTime = microtime(true);

        $kpiSummary       = $this->buildKpiSummary($tenantId, $activeModules, $startTime);
        $accountingPeriod = $this->resolveAccountingPeriod($tenantId, $startTime);
        $industrySkills   = $this->resolveIndustrySkills($tenantId, $activeModules);

        return new ErpContext(
            tenantId: $tenantId,
            kpiSummary: $kpiSummary,
            activeModules: $activeModules,
            accountingPeriod: $accountingPeriod,
            industrySkills: $industrySkills,
            builtAt: Carbon::now(),
        );
    }

    /**
     * Refresh bagian tertentu dari ERP_Context tanpa rebuild penuh.
     * Digunakan untuk update incremental saat data berubah signifikan.
     *
     * @param  ErpContext $context  Context yang akan di-refresh
     * @param  string     $module   Nama modul yang datanya berubah
     * @return ErpContext           Context baru dengan data modul yang diperbarui
     */
    public function refresh(ErpContext $context, string $module): ErpContext
    {
        $tenantId     = $context->tenantId;
        $startTime    = microtime(true);
        $kpiSummary   = $context->kpiSummary;

        // Update hanya bagian KPI yang relevan dengan modul yang berubah
        $moduleKpiMap = [
            'inventory'  => fn() => ['critical_stock' => $this->queryCriticalStock($tenantId)],
            'accounting' => fn() => ['revenue' => $this->queryRevenue($tenantId), 'overdue_ar' => $this->queryOverdueAr($tenantId)],
            'hrm'        => fn() => ['active_employees' => $this->queryActiveEmployees($tenantId)],
            'sales'      => fn() => ['revenue' => $this->queryRevenue($tenantId)],
        ];

        $normalizedModule = strtolower($module);

        if (isset($moduleKpiMap[$normalizedModule])) {
            $elapsed = microtime(true) - $startTime;
            if ($elapsed < self::BUILD_TIMEOUT_SECONDS - 0.5) {
                try {
                    $updatedKpi = $moduleKpiMap[$normalizedModule]();
                    $kpiSummary = array_merge($kpiSummary, $updatedKpi);
                } catch (\Throwable $e) {
                    Log::warning("AgentContextBuilder: refresh gagal untuk modul {$module}", [
                        'tenant_id' => $tenantId,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }
        }

        $accountingPeriod = $context->accountingPeriod;
        if ($normalizedModule === 'accounting') {
            $accountingPeriod = $this->resolveAccountingPeriod($tenantId, $startTime) ?? $accountingPeriod;
        }

        return new ErpContext(
            tenantId: $tenantId,
            kpiSummary: $kpiSummary,
            activeModules: $context->activeModules,
            accountingPeriod: $accountingPeriod,
            industrySkills: $context->industrySkills,
            builtAt: Carbon::now(),
        );
    }

    // ─── Private: KPI Builders ────────────────────────────────────────────────

    /**
     * Bangun KPI summary menggunakan parallel queries.
     * Setiap query dibungkus try/catch agar partial context tetap bisa dikembalikan.
     */
    private function buildKpiSummary(int $tenantId, array $activeModules, float $startTime): array
    {
        $kpi = [
            'revenue'          => null,
            'critical_stock'   => null,
            'overdue_ar'       => null,
            'active_employees' => null,
            'unavailable'      => [],
        ];

        // Revenue bulan ini (modul sales/accounting)
        $kpi = $this->runWithTimeout(
            fn() => array_merge($kpi, ['revenue' => $this->queryRevenue($tenantId)]),
            $kpi,
            'revenue',
            $startTime
        );

        // Stok kritis (modul inventory)
        $kpi = $this->runWithTimeout(
            fn() => array_merge($kpi, ['critical_stock' => $this->queryCriticalStock($tenantId)]),
            $kpi,
            'critical_stock',
            $startTime
        );

        // Piutang jatuh tempo (modul accounting/sales)
        $kpi = $this->runWithTimeout(
            fn() => array_merge($kpi, ['overdue_ar' => $this->queryOverdueAr($tenantId)]),
            $kpi,
            'overdue_ar',
            $startTime
        );

        // Jumlah karyawan aktif (modul hrm)
        $kpi = $this->runWithTimeout(
            fn() => array_merge($kpi, ['active_employees' => $this->queryActiveEmployees($tenantId)]),
            $kpi,
            'active_employees',
            $startTime
        );

        return $kpi;
    }

    /**
     * Jalankan callable dengan timeout guard.
     * Jika timeout atau error, tandai field sebagai unavailable dan kembalikan partial context.
     */
    private function runWithTimeout(callable $fn, array $current, string $field, float $startTime): array
    {
        $elapsed = microtime(true) - $startTime;

        // Jika sudah mendekati batas waktu, skip query ini
        if ($elapsed >= self::BUILD_TIMEOUT_SECONDS - 0.3) {
            $current['unavailable'][] = $field;
            Log::warning("AgentContextBuilder: skip query '{$field}' karena mendekati timeout", [
                'elapsed_ms' => round($elapsed * 1000),
            ]);
            return $current;
        }

        try {
            return $fn();
        } catch (\Throwable $e) {
            $current['unavailable'][] = $field;
            Log::warning("AgentContextBuilder: query '{$field}' gagal", [
                'error' => $e->getMessage(),
            ]);
            return $current;
        }
    }

    /**
     * Query revenue bulan ini untuk tenant.
     * Scope ke tenant_id yang diberikan (tidak menggunakan global scope).
     */
    private function queryRevenue(int $tenantId): float
    {
        return (float) SalesOrder::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered', 'completed'])
            ->sum('total');
    }

    /**
     * Query jumlah produk dengan stok di bawah stock_min.
     * Scope ke tenant_id yang diberikan.
     */
    private function queryCriticalStock(int $tenantId): int
    {
        // Subquery: hitung total stok per produk, lalu bandingkan dengan stock_min
        $criticalProductIds = DB::table('products')
            ->select('products.id')
            ->leftJoin('product_stocks', 'product_stocks.product_id', '=', 'products.id')
            ->where('products.tenant_id', $tenantId)
            ->where('products.is_active', true)
            ->whereNotNull('products.stock_min')
            ->where('products.stock_min', '>', 0)
            ->whereNull('products.deleted_at')
            ->groupBy('products.id', 'products.stock_min')
            ->havingRaw('COALESCE(SUM(product_stocks.quantity), 0) < products.stock_min')
            ->pluck('id');

        return $criticalProductIds->count();
    }

    /**
     * Query total piutang yang sudah jatuh tempo (invoice unpaid/partial dengan due_date < hari ini).
     * Scope ke tenant_id yang diberikan.
     */
    private function queryOverdueAr(int $tenantId): float
    {
        return (float) Invoice::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', now()->startOfDay())
            ->sum('remaining_amount');
    }

    /**
     * Query jumlah karyawan aktif untuk tenant.
     * Scope ke tenant_id yang diberikan.
     */
    private function queryActiveEmployees(int $tenantId): int
    {
        return (int) Employee::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Resolve periode akuntansi yang sedang berjalan untuk tenant.
     */
    private function resolveAccountingPeriod(int $tenantId, float $startTime): ?string
    {
        $elapsed = microtime(true) - $startTime;
        if ($elapsed >= self::BUILD_TIMEOUT_SECONDS - 0.3) {
            return null;
        }

        try {
            $period = AccountingPeriod::withoutGlobalScopes()
                ->where('tenant_id', $tenantId)
                ->where('status', 'open')
                ->where('start_date', '<=', now()->toDateString())
                ->where('end_date', '>=', now()->toDateString())
                ->first(['name', 'start_date', 'end_date']);

            if (!$period) {
                return null;
            }

            return $period->name
                ?? ($period->start_date->format('M Y') . ' - ' . $period->end_date->format('M Y'));
        } catch (\Throwable $e) {
            Log::warning('AgentContextBuilder: gagal resolve accounting period', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Resolve industry skills berdasarkan modul aktif dan business_type tenant.
     */
    private function resolveIndustrySkills(int $tenantId, array $activeModules): array
    {
        $skills = [];

        // Core skills berdasarkan modul aktif
        $moduleSkillMap = [
            'accounting'  => 'Akuntansi & Keuangan',
            'inventory'   => 'Inventory & Gudang',
            'hrm'         => 'HRM & Payroll',
            'sales'       => 'Penjualan & CRM',
            'project'     => 'Project Management',
            'crm'         => 'Penjualan & CRM',
            'payroll'     => 'HRM & Payroll',
            'purchase'    => 'Pembelian & Pengadaan',
        ];

        foreach ($activeModules as $module) {
            $normalizedModule = strtolower($module);
            if (isset($moduleSkillMap[$normalizedModule])) {
                $skill = $moduleSkillMap[$normalizedModule];
                if (!in_array($skill, $skills, true)) {
                    $skills[] = $skill;
                }
            }
        }

        // Industry-specific skills berdasarkan business_type tenant
        try {
            $tenant = Tenant::withoutGlobalScopes()->find($tenantId, ['business_type', 'enabled_modules']);
            if ($tenant) {
                $industrySkillMap = [
                    'healthcare'   => 'Healthcare',
                    'manufacture'  => 'Manufaktur',
                    'hotel'        => 'Hospitality',
                    'construction' => 'Konstruksi',
                    'agriculture'  => 'Pertanian',
                    'livestock'    => 'Peternakan',
                ];

                if ($tenant->business_type && isset($industrySkillMap[$tenant->business_type])) {
                    $industrySkill = $industrySkillMap[$tenant->business_type];
                    if (!in_array($industrySkill, $skills, true)) {
                        $skills[] = $industrySkill;
                    }
                }

                // Tambahkan skill dari enabled_modules jika ada modul industri khusus
                $enabledModules = $tenant->enabled_modules ?? [];
                $industryModules = ['healthcare', 'manufacturing', 'telecom', 'hotel', 'construction'];
                foreach ($industryModules as $industryModule) {
                    if (in_array($industryModule, $enabledModules, true)) {
                        $label = ucfirst($industryModule);
                        if (!in_array($label, $skills, true)) {
                            $skills[] = $label;
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('AgentContextBuilder: gagal resolve industry skills', [
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
        }

        return $skills;
    }
}
