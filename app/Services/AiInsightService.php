<?php

namespace App\Services;

use App\Models\ErpNotification;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AiInsightService — analisis proaktif bisnis per tenant.
 *
 * Menghasilkan insight, anomali, dan rekomendasi tanpa perlu user bertanya.
 * Dipanggil oleh: GenerateAiInsights job (terjadwal) & DashboardController (on-demand).
 */
class AiInsightService
{
    // ─── Public API ───────────────────────────────────────────────

    /**
     * Jalankan semua analisis dan kembalikan array insight.
     * Setiap insight: ['type', 'severity', 'title', 'body', 'data', 'action']
     */
    public function analyze(int $tenantId): array
    {
        $insights = array_merge(
            $this->analyzeRevenueTrend($tenantId),
            $this->analyzeStockDepletion($tenantId),
            $this->analyzeExpenseAnomaly($tenantId),
            $this->analyzeReceivables($tenantId),
            $this->analyzeSalesVelocity($tenantId),
            $this->analyzeTopProducts($tenantId),
        );

        // Urutkan: critical → warning → info
        usort($insights, fn($a, $b) => $this->severityOrder($a['severity']) <=> $this->severityOrder($b['severity']));

        return $insights;
    }

    /**
     * Simpan insight ke erp_notifications (in-app) dan kembalikan array insight.
     * Hindari duplikat: cek apakah insight tipe yang sama sudah ada hari ini.
     */
    public function generateAndSave(int $tenantId): array
    {
        $insights = $this->analyze($tenantId);
        if (empty($insights)) return [];

        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        foreach ($insights as $insight) {
            // Skip duplikat hari ini
            $exists = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', 'ai_insight_' . $insight['type'])
                ->whereDate('created_at', today())
                ->exists();

            if ($exists) continue;

            foreach ($recipients as $userId) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id'   => $userId,
                    'type'      => 'ai_insight_' . $insight['type'],
                    'title'     => $insight['title'],
                    'body'      => $insight['body'],
                    'data'      => array_merge($insight['data'] ?? [], [
                        'severity' => $insight['severity'],
                        'action'   => $insight['action'] ?? null,
                    ]),
                ]);
            }
        }

        return $insights;
    }

    // ─── Analyzers ────────────────────────────────────────────────

    /**
     * Tren pendapatan: bandingkan 7 hari ini vs 7 hari lalu.
     */
    private function analyzeRevenueTrend(int $tenantId): array
    {
        $thisWeek = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [now()->subDays(6)->toDateString(), now()->toDateString()])
            ->sum('total');

        $lastWeek = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereBetween('date', [now()->subDays(13)->toDateString(), now()->subDays(7)->toDateString()])
            ->sum('total');

        if ($lastWeek <= 0 || $thisWeek <= 0) return [];

        $changePercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;

        if (abs($changePercent) < 10) return []; // tidak signifikan

        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        if ($changePercent <= -15) {
            return [[
                'type'     => 'revenue_drop',
                'severity' => 'critical',
                'title'    => '📉 Omzet Turun Signifikan',
                'body'     => sprintf(
                    'Omzet 7 hari ini %s, turun %.1f%% dibanding 7 hari lalu (%s). Perlu perhatian segera.',
                    $fmt($thisWeek), abs($changePercent), $fmt($lastWeek)
                ),
                'data'     => ['this_week' => $thisWeek, 'last_week' => $lastWeek, 'change_percent' => round($changePercent, 1)],
                'action'   => 'tampilkan tren penjualan 14 hari terakhir',
            ]];
        }

        if ($changePercent <= -10) {
            return [[
                'type'     => 'revenue_decline',
                'severity' => 'warning',
                'title'    => '⚠️ Omzet Menurun',
                'body'     => sprintf(
                    'Omzet 7 hari ini %s, turun %.1f%% vs minggu lalu (%s).',
                    $fmt($thisWeek), abs($changePercent), $fmt($lastWeek)
                ),
                'data'     => ['this_week' => $thisWeek, 'last_week' => $lastWeek, 'change_percent' => round($changePercent, 1)],
                'action'   => 'analisis penyebab penurunan omzet',
            ]];
        }

        if ($changePercent >= 20) {
            return [[
                'type'     => 'revenue_spike',
                'severity' => 'info',
                'title'    => '🚀 Omzet Naik Pesat',
                'body'     => sprintf(
                    'Omzet 7 hari ini %s, naik %.1f%% vs minggu lalu (%s). Pertahankan momentum!',
                    $fmt($thisWeek), $changePercent, $fmt($lastWeek)
                ),
                'data'     => ['this_week' => $thisWeek, 'last_week' => $lastWeek, 'change_percent' => round($changePercent, 1)],
                'action'   => 'tampilkan produk terlaris minggu ini',
            ]];
        }

        return [];
    }

    /**
     * Prediksi stok habis: estimasi berapa hari stok tersisa berdasarkan rata-rata penjualan.
     */
    private function analyzeStockDepletion(int $tenantId): array
    {
        $insights = [];

        // Ambil produk aktif dengan stok
        $stocks = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
            ->join('products', 'product_stocks.product_id', '=', 'products.id')
            ->select('product_stocks.*')
            ->where('product_stocks.quantity', '>', 0)
            ->get();

        foreach ($stocks as $stock) {
            // Rata-rata penjualan harian 14 hari terakhir
            $avgDaily = DB::table('sales_order_items')
                ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                ->where('sales_orders.tenant_id', $tenantId)
                ->where('sales_orders.status', '!=', 'cancelled')
                ->where('sales_order_items.product_id', $stock->product_id)
                ->whereBetween('sales_orders.date', [now()->subDays(14)->toDateString(), now()->toDateString()])
                ->sum('sales_order_items.quantity') / 14;

            if ($avgDaily <= 0) continue;

            $daysLeft = (int) floor($stock->quantity / $avgDaily);

            if ($daysLeft <= 3) {
                $insights[] = [
                    'type'     => 'stock_critical_' . $stock->product_id,
                    'severity' => 'critical',
                    'title'    => "🔴 Stok {$stock->product->name} Kritis",
                    'body'     => "Stok {$stock->product->name} di {$stock->warehouse->name} tinggal {$stock->quantity} {$stock->product->unit}. Berdasarkan rata-rata penjualan, stok akan habis dalam **{$daysLeft} hari**.",
                    'data'     => ['product_id' => $stock->product_id, 'days_left' => $daysLeft, 'quantity' => $stock->quantity, 'avg_daily' => round($avgDaily, 1)],
                    'action'   => "buat purchase order untuk {$stock->product->name}",
                ];
            } elseif ($daysLeft <= 7) {
                $insights[] = [
                    'type'     => 'stock_low_' . $stock->product_id,
                    'severity' => 'warning',
                    'title'    => "⚠️ Stok {$stock->product->name} Menipis",
                    'body'     => "Stok {$stock->product->name} tinggal {$stock->quantity} {$stock->product->unit}. Estimasi habis dalam **{$daysLeft} hari** berdasarkan rata-rata penjualan {$avgDaily} {$stock->product->unit}/hari.",
                    'data'     => ['product_id' => $stock->product_id, 'days_left' => $daysLeft, 'quantity' => $stock->quantity, 'avg_daily' => round($avgDaily, 1)],
                    'action'   => "tambah stok {$stock->product->name}",
                ];
            }
        }

        // Batasi max 3 insight stok agar tidak spam
        return array_slice($insights, 0, 3);
    }

    /**
     * Anomali pengeluaran: deteksi lonjakan pengeluaran tidak wajar.
     */
    private function analyzeExpenseAnomaly(int $tenantId): array
    {
        // Pengeluaran 7 hari ini
        $thisWeek = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('date', [now()->subDays(6)->toDateString(), now()->toDateString()])
            ->sum('amount');

        // Rata-rata pengeluaran 4 minggu sebelumnya (per minggu)
        $avgPrevious = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('date', [now()->subDays(34)->toDateString(), now()->subDays(7)->toDateString()])
            ->sum('amount') / 4;

        if ($avgPrevious <= 0 || $thisWeek <= 0) return [];

        $changePercent = (($thisWeek - $avgPrevious) / $avgPrevious) * 100;

        if ($changePercent < 30) return []; // tidak signifikan

        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        // Cari kategori pengeluaran terbesar minggu ini
        $topCategory = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('date', [now()->subDays(6)->toDateString(), now()->toDateString()])
            ->with('category')
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->first();

        $categoryNote = $topCategory?->category
            ? " Kategori terbesar: {$topCategory->category->name} ({$fmt($topCategory->total)})."
            : '';

        return [[
            'type'     => 'expense_anomaly',
            'severity' => $changePercent >= 50 ? 'critical' : 'warning',
            'title'    => '💸 Lonjakan Pengeluaran Terdeteksi',
            'body'     => sprintf(
                'Pengeluaran 7 hari ini %s, naik %.1f%% dari rata-rata mingguan (%s).%s',
                $fmt($thisWeek), $changePercent, $fmt($avgPrevious), $categoryNote
            ),
            'data'     => ['this_week' => $thisWeek, 'avg_previous' => $avgPrevious, 'change_percent' => round($changePercent, 1)],
            'action'   => 'tampilkan breakdown pengeluaran minggu ini',
        ]];
    }

    /**
     * Piutang jatuh tempo: deteksi piutang yang sudah lewat jatuh tempo.
     */
    private function analyzeReceivables(int $tenantId): array
    {
        $overdueCount = SalesOrder::where('tenant_id', $tenantId)
            ->where('payment_type', 'credit')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->count();

        $overdueAmount = SalesOrder::where('tenant_id', $tenantId)
            ->where('payment_type', 'credit')
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', today())
            ->sum('total');

        if ($overdueCount === 0) return [];

        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        return [[
            'type'     => 'overdue_receivables',
            'severity' => $overdueAmount > 5_000_000 ? 'critical' : 'warning',
            'title'    => "🔔 {$overdueCount} Piutang Jatuh Tempo",
            'body'     => "Ada {$overdueCount} tagihan senilai {$fmt($overdueAmount)} yang sudah melewati jatuh tempo. Segera lakukan follow-up ke pelanggan.",
            'data'     => ['count' => $overdueCount, 'amount' => $overdueAmount],
            'action'   => 'tampilkan daftar piutang yang sudah jatuh tempo',
        ]];
    }

    /**
     * Kecepatan penjualan: deteksi produk yang tiba-tiba tidak terjual.
     */
    private function analyzeSalesVelocity(int $tenantId): array
    {
        // Produk yang terjual minggu lalu tapi tidak terjual 3 hari ini
        $soldLastWeek = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.date', [now()->subDays(10)->toDateString(), now()->subDays(4)->toDateString()])
            ->pluck('sales_order_items.product_id')
            ->unique();

        if ($soldLastWeek->isEmpty()) return [];

        $soldRecently = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.date', [now()->subDays(3)->toDateString(), now()->toDateString()])
            ->pluck('sales_order_items.product_id')
            ->unique();

        $stalled = $soldLastWeek->diff($soldRecently);

        if ($stalled->count() < 3) return []; // tidak signifikan

        return [[
            'type'     => 'sales_stall',
            'severity' => 'info',
            'title'    => '📊 Penjualan Beberapa Produk Melambat',
            'body'     => "{$stalled->count()} produk yang aktif terjual minggu lalu tidak ada transaksi dalam 3 hari terakhir. Mungkin perlu promosi atau cek ketersediaan stok.",
            'data'     => ['stalled_count' => $stalled->count(), 'product_ids' => $stalled->values()->toArray()],
            'action'   => 'tampilkan produk yang tidak terjual 3 hari terakhir',
        ]];
    }

    /**
     * Produk terlaris: highlight produk dengan performa terbaik.
     */
    private function analyzeTopProducts(int $tenantId): array
    {
        $topProduct = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.date', [now()->subDays(6)->toDateString(), now()->toDateString()])
            ->selectRaw('products.name, SUM(sales_order_items.quantity) as total_qty, SUM(sales_order_items.total) as total_revenue')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_revenue')
            ->first();

        if (!$topProduct || $topProduct->total_revenue <= 0) return [];

        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        return [[
            'type'     => 'top_product',
            'severity' => 'info',
            'title'    => "⭐ Produk Terlaris: {$topProduct->name}",
            'body'     => "{$topProduct->name} menjadi produk terlaris minggu ini dengan {$topProduct->total_qty} unit terjual, menghasilkan {$fmt($topProduct->total_revenue)}.",
            'data'     => ['product_name' => $topProduct->name, 'qty' => $topProduct->total_qty, 'revenue' => $topProduct->total_revenue],
            'action'   => "tampilkan detail penjualan {$topProduct->name}",
        ]];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function severityOrder(string $severity): int
    {
        return match($severity) {
            'critical' => 0,
            'warning'  => 1,
            'info'     => 2,
            default    => 3,
        };
    }

    /**
     * Buat teks digest ringkas untuk email/WA.
     */
    public function buildDigestText(int $tenantId, string $period = 'daily'): string
    {
        $insights = $this->analyze($tenantId);

        $fmt = fn($n) => 'Rp ' . number_format($n, 0, ',', '.');

        // Statistik utama
        $todayRevenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('date', today())
            ->sum('total');

        $todayExpense = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereDate('date', today())
            ->sum('amount');

        $todayOrders = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereDate('date', today())
            ->count();

        $periodLabel = $period === 'weekly' ? 'Mingguan' : 'Harian';
        $lines = [
            "📊 **Digest {$periodLabel} Qalcuity ERP**",
            "📅 " . now()->translatedFormat('l, d F Y'),
            "",
            "**Ringkasan Hari Ini:**",
            "• Omzet: {$fmt($todayRevenue)} ({$todayOrders} transaksi)",
            "• Pengeluaran: {$fmt($todayExpense)}",
            "• Profit: {$fmt($todayRevenue - $todayExpense)}",
            "",
        ];

        if (!empty($insights)) {
            $lines[] = "**Insight AI:**";
            foreach (array_slice($insights, 0, 5) as $insight) {
                $icon = match($insight['severity']) {
                    'critical' => '🔴',
                    'warning'  => '🟡',
                    default    => '🟢',
                };
                $lines[] = "{$icon} {$insight['title']}";
                $lines[] = "   {$insight['body']}";
                $lines[] = "";
            }
        }

        $lines[] = "---";
        $lines[] = "Buka dashboard untuk detail lengkap: " . url('/dashboard');

        return implode("\n", $lines);
    }
}
