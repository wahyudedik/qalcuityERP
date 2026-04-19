<?php

namespace App\Services\Agent;

use App\Models\Budget;
use App\Models\Employee;
use App\Models\InsightRead;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\ProactiveInsight;
use Illuminate\Support\Facades\DB;

/**
 * ProactiveInsightEngine menganalisis kondisi bisnis tenant secara terjadwal
 * dan menghasilkan ProactiveInsight jika ditemukan kondisi yang memerlukan perhatian.
 *
 * Requirements: 4.1, 4.2, 4.3, 4.4, 4.5
 */
class ProactiveInsightEngine
{
    /** Threshold default invoice belum dibayar (dalam rupiah) jika tidak dikonfigurasi tenant */
    private const DEFAULT_UNPAID_THRESHOLD = 1_000_000;

    /**
     * Analisis kondisi bisnis tenant dan generate insights.
     * Dipanggil oleh scheduled job setiap 6 jam.
     *
     * @return ProactiveInsight[]
     */
    public function analyze(int $tenantId): array
    {
        $generated = [];

        $conditions = [
            fn () => $this->checkLowStock($tenantId),
            fn () => $this->checkOverdueAr($tenantId),
            fn () => $this->checkBudgetExceeded($tenantId),
            fn () => $this->checkContractExpiry($tenantId),
            fn () => $this->checkUnpaidInvoices($tenantId),
        ];

        foreach ($conditions as $check) {
            try {
                $insights = $check();
                foreach ($insights as $insight) {
                    $generated[] = $insight;
                }
            } catch (\Throwable $e) {
                // Log error, skip kondisi ini, lanjutkan kondisi lain
                \Illuminate\Support\Facades\Log::warning('ProactiveInsightEngine: gagal mengecek kondisi', [
                    'tenant_id' => $tenantId,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        return $generated;
    }

    /**
     * Ambil insights yang belum dibaca untuk user tertentu.
     *
     * @return ProactiveInsight[]
     */
    public function getPendingInsights(int $tenantId, int $userId): array
    {
        // Ambil insight_id yang sudah dibaca/dismissed oleh user ini
        $readInsightIds = InsightRead::where('user_id', $userId)
            ->pluck('insight_id')
            ->toArray();

        return ProactiveInsight::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNull('suppressed_until')
                    ->orWhere('suppressed_until', '<', now());
            })
            ->when(!empty($readInsightIds), function ($query) use ($readInsightIds) {
                $query->whereNotIn('id', $readInsightIds);
            })
            ->orderByRaw("FIELD(urgency, 'critical', 'high', 'medium', 'low')")
            ->orderBy('created_at', 'desc')
            ->get()
            ->all();
    }

    /**
     * Tandai insight sebagai dismissed/handled.
     * Suppress insight serupa selama 24 jam.
     */
    public function dismiss(ProactiveInsight $insight, string $reason): void
    {
        // Set suppressed_until = now + 24 jam
        $insight->suppressed_until = now()->addHours(24);
        $insight->save();

        // Buat InsightRead record
        $userId = auth()->id();
        if ($userId) {
            InsightRead::firstOrCreate(
                [
                    'insight_id' => $insight->id,
                    'user_id'    => $userId,
                ],
                [
                    'status'  => $reason === 'handled' ? 'handled' : 'dismissed',
                    'read_at' => now(),
                ]
            );
        }
    }

    // -------------------------------------------------------------------------
    // Private: Pengecekan kondisi bisnis
    // -------------------------------------------------------------------------

    /**
     * Cek produk dengan stok di bawah reorder point (stock_min).
     *
     * @return ProactiveInsight[]
     */
    private function checkLowStock(int $tenantId): array
    {
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNotNull('stock_min')
            ->where('stock_min', '>', 0)
            ->get()
            ->filter(fn (Product $p) => $p->totalStock() < $p->stock_min)
            ->values();

        if ($lowStockProducts->isEmpty()) {
            return [];
        }

        $conditionData = $lowStockProducts->map(fn (Product $p) => [
            'product_id'   => $p->id,
            'product_name' => $p->name,
            'sku'          => $p->sku,
            'current_stock' => $p->totalStock(),
            'stock_min'    => $p->stock_min,
        ])->toArray();

        $hash = $this->makeHash('low_stock', $tenantId, $conditionData);

        if ($this->isDuplicate($tenantId, $hash)) {
            return [];
        }

        $count = $lowStockProducts->count();
        $urgency = $count >= 10 ? 'critical' : ($count >= 5 ? 'high' : ($count >= 2 ? 'medium' : 'low'));

        $productNames = $lowStockProducts->take(3)->pluck('name')->implode(', ');
        $remaining = $count - 3;
        $suffix = $count > 3 ? " dan {$remaining} produk lainnya" : '';

        $insight = ProactiveInsight::create([
            'tenant_id'      => $tenantId,
            'condition_type' => 'low_stock',
            'urgency'        => $urgency,
            'title'          => "Stok {$count} Produk di Bawah Batas Minimum",
            'description'    => "Terdapat {$count} produk dengan stok di bawah reorder point: {$productNames}{$suffix}.",
            'business_impact' => 'Stok yang habis dapat menyebabkan kehilangan penjualan, ketidakpuasan pelanggan, dan gangguan operasional.',
            'recommendations' => [
                'Segera buat Purchase Order untuk produk-produk yang stoknya kritis.',
                'Tinjau ulang reorder point untuk memastikan akurasi perencanaan stok.',
                'Pertimbangkan untuk meningkatkan safety stock pada produk dengan permintaan tinggi.',
            ],
            'condition_data'  => $conditionData,
            'condition_hash'  => $hash,
            'suppressed_until' => null,
        ]);

        return [$insight];
    }

    /**
     * Cek piutang yang melewati jatuh tempo lebih dari 7 hari.
     *
     * @return ProactiveInsight[]
     */
    private function checkOverdueAr(int $tenantId): array
    {
        $overdueDate = now()->subDays(7);

        $overdueInvoices = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', $overdueDate)
            ->get();

        if ($overdueInvoices->isEmpty()) {
            return [];
        }

        $totalOverdue = $overdueInvoices->sum('remaining_amount');
        $conditionData = [
            'count'         => $overdueInvoices->count(),
            'total_overdue' => $totalOverdue,
            'oldest_due_date' => $overdueInvoices->min('due_date'),
        ];

        $hash = $this->makeHash('overdue_ar', $tenantId, $conditionData);

        if ($this->isDuplicate($tenantId, $hash)) {
            return [];
        }

        $count = $overdueInvoices->count();
        $urgency = $totalOverdue >= 50_000_000 ? 'critical' : ($totalOverdue >= 10_000_000 ? 'high' : 'medium');
        $formattedTotal = 'Rp ' . number_format($totalOverdue, 0, ',', '.');

        $insight = ProactiveInsight::create([
            'tenant_id'      => $tenantId,
            'condition_type' => 'overdue_ar',
            'urgency'        => $urgency,
            'title'          => "Piutang Jatuh Tempo: {$count} Invoice Belum Dibayar",
            'description'    => "Terdapat {$count} invoice dengan total piutang {$formattedTotal} yang telah melewati jatuh tempo lebih dari 7 hari.",
            'business_impact' => 'Piutang yang tidak tertagih berdampak pada arus kas bisnis dan dapat meningkatkan risiko kredit macet.',
            'recommendations' => [
                'Kirimkan pengingat pembayaran kepada pelanggan yang memiliki invoice jatuh tempo.',
                'Hubungi pelanggan dengan piutang terbesar untuk negosiasi jadwal pembayaran.',
                'Tinjau kebijakan kredit dan batas kredit untuk pelanggan berisiko tinggi.',
            ],
            'condition_data'  => $conditionData,
            'condition_hash'  => $hash,
            'suppressed_until' => null,
        ]);

        return [$insight];
    }

    /**
     * Cek anggaran yang terpakai lebih dari 90%.
     *
     * @return ProactiveInsight[]
     */
    private function checkBudgetExceeded(int $tenantId): array
    {
        $budgets = Budget::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->where('amount', '>', 0)
            ->get()
            ->filter(fn (Budget $b) => $b->usage_percent >= 90)
            ->values();

        if ($budgets->isEmpty()) {
            return [];
        }

        $conditionData = $budgets->map(fn (Budget $b) => [
            'budget_id'     => $b->id,
            'name'          => $b->name,
            'department'    => $b->department,
            'period'        => $b->period,
            'amount'        => $b->amount,
            'realized'      => $b->realized,
            'usage_percent' => $b->usage_percent,
        ])->toArray();

        $hash = $this->makeHash('budget_exceeded', $tenantId, $conditionData);

        if ($this->isDuplicate($tenantId, $hash)) {
            return [];
        }

        $count = $budgets->count();
        $overBudget = $budgets->filter(fn (Budget $b) => $b->usage_percent >= 100)->count();
        $urgency = $overBudget > 0 ? 'critical' : ($count >= 3 ? 'high' : 'medium');

        $budgetNames = $budgets->take(3)->pluck('name')->implode(', ');
        $remaining = $count - 3;
        $suffix = $count > 3 ? " dan {$remaining} anggaran lainnya" : '';

        $insight = ProactiveInsight::create([
            'tenant_id'      => $tenantId,
            'condition_type' => 'budget_exceeded',
            'urgency'        => $urgency,
            'title'          => "{$count} Anggaran Mendekati atau Melebihi Batas",
            'description'    => "Anggaran berikut telah terpakai ≥90%: {$budgetNames}{$suffix}." . ($overBudget > 0 ? " {$overBudget} anggaran sudah melebihi batas." : ''),
            'business_impact' => 'Anggaran yang hampir habis dapat mengganggu operasional dan memerlukan persetujuan tambahan anggaran yang memakan waktu.',
            'recommendations' => [
                'Tinjau pengeluaran yang masih bisa ditunda hingga periode anggaran berikutnya.',
                'Ajukan revisi anggaran jika pengeluaran tambahan tidak dapat dihindari.',
                'Identifikasi area penghematan untuk mencegah anggaran terlampaui.',
            ],
            'condition_data'  => $conditionData,
            'condition_hash'  => $hash,
            'suppressed_until' => null,
        ]);

        return [$insight];
    }

    /**
     * Cek karyawan dengan kontrak yang berakhir dalam 30 hari.
     * Menggunakan resign_date sebagai tanggal akhir kontrak.
     *
     * @return ProactiveInsight[]
     */
    private function checkContractExpiry(int $tenantId): array
    {
        $expiringEmployees = Employee::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->whereNotNull('resign_date')
            ->whereBetween('resign_date', [now()->toDateString(), now()->addDays(30)->toDateString()])
            ->get();

        if ($expiringEmployees->isEmpty()) {
            return [];
        }

        $conditionData = $expiringEmployees->map(fn (Employee $e) => [
            'employee_id'   => $e->id,
            'name'          => $e->name,
            'position'      => $e->position,
            'department'    => $e->department,
            'resign_date'   => $e->resign_date?->toDateString(),
            'days_remaining' => (int) now()->diffInDays($e->resign_date, false),
        ])->toArray();

        $hash = $this->makeHash('contract_expiry', $tenantId, $conditionData);

        if ($this->isDuplicate($tenantId, $hash)) {
            return [];
        }

        $count = $expiringEmployees->count();
        $expiringSoon = $expiringEmployees->filter(
            fn (Employee $e) => $e->resign_date && $e->resign_date->lte(now()->addDays(7))
        )->count();

        $urgency = $expiringSoon > 0 ? 'high' : ($count >= 3 ? 'medium' : 'low');
        $employeeNames = $expiringEmployees->take(3)->pluck('name')->implode(', ');
        $remaining = $count - 3;
        $suffix = $count > 3 ? " dan {$remaining} karyawan lainnya" : '';

        $insight = ProactiveInsight::create([
            'tenant_id'      => $tenantId,
            'condition_type' => 'contract_expiry',
            'urgency'        => $urgency,
            'title'          => "Kontrak {$count} Karyawan Berakhir dalam 30 Hari",
            'description'    => "Kontrak karyawan berikut akan berakhir dalam 30 hari ke depan: {$employeeNames}{$suffix}.",
            'business_impact' => 'Kontrak yang berakhir tanpa tindak lanjut dapat menyebabkan kehilangan tenaga kerja terlatih dan gangguan operasional.',
            'recommendations' => [
                'Segera diskusikan perpanjangan kontrak dengan karyawan yang bersangkutan.',
                'Siapkan dokumen perpanjangan kontrak atau proses rekrutmen pengganti jika diperlukan.',
                'Lakukan evaluasi kinerja untuk menentukan kelayakan perpanjangan kontrak.',
            ],
            'condition_data'  => $conditionData,
            'condition_hash'  => $hash,
            'suppressed_until' => null,
        ]);

        return [$insight];
    }

    /**
     * Cek invoice yang belum dibayar melebihi threshold yang dikonfigurasi tenant.
     *
     * @return ProactiveInsight[]
     */
    private function checkUnpaidInvoices(int $tenantId): array
    {
        $threshold = $this->getUnpaidThreshold($tenantId);

        $unpaidInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('status', 'unpaid')
            ->where('remaining_amount', '>', $threshold)
            ->get();

        if ($unpaidInvoices->isEmpty()) {
            return [];
        }

        $totalUnpaid = $unpaidInvoices->sum('remaining_amount');
        $conditionData = [
            'count'        => $unpaidInvoices->count(),
            'total_unpaid' => $totalUnpaid,
            'threshold'    => $threshold,
        ];

        $hash = $this->makeHash('unpaid_invoice', $tenantId, $conditionData);

        if ($this->isDuplicate($tenantId, $hash)) {
            return [];
        }

        $count = $unpaidInvoices->count();
        $urgency = $totalUnpaid >= 100_000_000 ? 'critical' : ($totalUnpaid >= 25_000_000 ? 'high' : 'medium');
        $formattedTotal = 'Rp ' . number_format($totalUnpaid, 0, ',', '.');
        $formattedThreshold = 'Rp ' . number_format($threshold, 0, ',', '.');

        $insight = ProactiveInsight::create([
            'tenant_id'      => $tenantId,
            'condition_type' => 'unpaid_invoice',
            'urgency'        => $urgency,
            'title'          => "{$count} Invoice Belum Dibayar Melebihi Threshold",
            'description'    => "Terdapat {$count} invoice belum dibayar dengan nilai masing-masing di atas {$formattedThreshold}. Total: {$formattedTotal}.",
            'business_impact' => 'Invoice yang belum dibayar dalam jumlah besar berdampak langsung pada likuiditas dan kemampuan bisnis memenuhi kewajiban keuangan.',
            'recommendations' => [
                'Prioritaskan penagihan invoice dengan nilai terbesar terlebih dahulu.',
                'Pertimbangkan untuk menawarkan diskon pembayaran awal (early payment discount) kepada pelanggan.',
                'Evaluasi kebijakan kredit dan persyaratan pembayaran untuk pelanggan baru.',
            ],
            'condition_data'  => $conditionData,
            'condition_hash'  => $hash,
            'suppressed_until' => null,
        ]);

        return [$insight];
    }

    // -------------------------------------------------------------------------
    // Private: Helper methods
    // -------------------------------------------------------------------------

    /**
     * Buat condition_hash untuk dedup.
     */
    private function makeHash(string $conditionType, int $tenantId, array $conditionData): string
    {
        return md5($conditionType . ':' . $tenantId . ':' . json_encode($conditionData));
    }

    /**
     * Cek apakah insight dengan hash yang sama sudah ada dan masih dalam periode suppression.
     */
    private function isDuplicate(int $tenantId, string $hash): bool
    {
        return ProactiveInsight::where('tenant_id', $tenantId)
            ->where('condition_hash', $hash)
            ->where(function ($query) {
                $query->whereNull('suppressed_until')
                    ->orWhere('suppressed_until', '>', now());
            })
            ->exists();
    }

    /**
     * Ambil threshold invoice belum dibayar untuk tenant.
     * Fallback ke DEFAULT_UNPAID_THRESHOLD jika tidak dikonfigurasi.
     */
    private function getUnpaidThreshold(int $tenantId): float
    {
        // Coba ambil dari cache/settings jika ada
        $cacheKey = "tenant:{$tenantId}:unpaid_invoice_threshold";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($tenantId) {
            // Cek apakah ada konfigurasi di tabel settings
            try {
                $setting = DB::table('settings')
                    ->where('tenant_id', $tenantId)
                    ->where('key', 'unpaid_invoice_threshold')
                    ->value('value');

                return $setting ? (float) $setting : self::DEFAULT_UNPAID_THRESHOLD;
            } catch (\Throwable) {
                return self::DEFAULT_UNPAID_THRESHOLD;
            }
        });
    }
}
