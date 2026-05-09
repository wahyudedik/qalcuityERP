<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ChartOfAccount;
use App\Models\Customer;
use App\Models\ErpNotification;
use App\Models\Invoice;
use App\Models\JournalEntryLine;
use App\Models\Payable;
use App\Models\PayrollRun;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * AiInsightService — analisis proaktif bisnis per tenant.
 *
 * Menghasilkan insight, anomali, dan rekomendasi tanpa perlu user bertanya.
 * Dipanggil oleh: GenerateAiInsights job (terjadwal) & DashboardController (on-demand).
 */
class AiInsightService
{
    public function __construct(protected ?AnomalyDetectionService $anomalyService = null)
    {
        $this->anomalyService ??= app(AnomalyDetectionService::class);
    }

    // ─── Public API ───────────────────────────────────────────────

    /**
     * Jalankan semua analisis dan kembalikan array insight.
     * Setiap insight: ['type', 'severity', 'title', 'body', 'data', 'action']
     */
    public function analyze(int $tenantId): array
    {
        // Konversi anomali ke format insight
        $anomalyInsights = collect($this->anomalyService->detect($tenantId))
            ->map(fn ($a) => [
                'type' => $a['type'],
                'severity' => $a['severity'],
                'title' => $a['title'],
                'body' => $a['description'],
                'data' => $a['data'] ?? [],
                'action' => 'lihat detail anomali',
            ])->toArray();

        $insights = array_merge(
            // ── Existing analyzers ──
            $this->analyzeRevenueTrend($tenantId),
            $this->analyzeMonthlyRevenue($tenantId),   // ← bulan vs bulan + "kemungkinan karena"
            $this->analyzeStockDepletion($tenantId),
            $this->analyzeExpenseAnomaly($tenantId),
            $this->analyzeReceivables($tenantId),
            $this->analyzeCreditLimits($tenantId),      // ← BUG-SALES-004: Credit limit monitoring
            $this->analyzeCurrencyStaleness($tenantId), // ← BUG-FIN-003: Currency rate monitoring
            $this->analyzeSalesVelocity($tenantId),
            $this->analyzeTopProducts($tenantId),
            // ── New analyzers ──
            $this->analyzeCashFlowPrediction($tenantId),
            $this->analyzeBudgetVariance($tenantId),
            $this->analyzePayrollCost($tenantId),
            $this->analyzeGlInsights($tenantId),
            // ── Anomaly Detection (Task 51) ──
            $anomalyInsights,
        );

        // Urutkan: critical → warning → info
        usort($insights, fn ($a, $b) => $this->severityOrder($a['severity']) <=> $this->severityOrder($b['severity']));

        return $insights;
    }

    /**
     * Simpan insight ke erp_notifications (in-app) dan kembalikan array insight.
     * Hindari duplikat: cek apakah insight tipe yang sama sudah ada hari ini.
     */
    public function generateAndSave(int $tenantId): array
    {
        $insights = $this->analyze($tenantId);
        if (empty($insights)) {
            return [];
        }

        $recipients = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['admin', 'manager'])
            ->pluck('id');

        foreach ($insights as $insight) {
            // Skip duplikat hari ini
            $exists = ErpNotification::where('tenant_id', $tenantId)
                ->where('type', 'ai_insight_'.$insight['type'])
                ->whereDate('created_at', today())
                ->exists();

            if ($exists) {
                continue;
            }

            foreach ($recipients as $userId) {
                ErpNotification::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'type' => 'ai_insight_'.$insight['type'],
                    'title' => $insight['title'],
                    'body' => $insight['body'],
                    'data' => array_merge($insight['data'] ?? [], [
                        'severity' => $insight['severity'],
                        'action' => $insight['action'] ?? null,
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

        if ($lastWeek <= 0 || $thisWeek <= 0) {
            return [];
        }

        $changePercent = (($thisWeek - $lastWeek) / $lastWeek) * 100;

        if (abs($changePercent) < 10) {
            return [];
        } // tidak signifikan

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        if ($changePercent <= -15) {
            return [
                [
                    'type' => 'revenue_drop',
                    'severity' => 'critical',
                    'title' => '📉 Omzet Turun Signifikan',
                    'body' => sprintf(
                        'Omzet 7 hari ini %s, turun %.1f%% dibanding 7 hari lalu (%s). Perlu perhatian segera.',
                        $fmt($thisWeek),
                        abs($changePercent),
                        $fmt($lastWeek)
                    ),
                    'data' => ['this_week' => $thisWeek, 'last_week' => $lastWeek, 'change_percent' => round($changePercent, 1)],
                    'action' => 'tampilkan tren penjualan 14 hari terakhir',
                ],
            ];
        }

        if ($changePercent <= -10) {
            return [
                [
                    'type' => 'revenue_decline',
                    'severity' => 'warning',
                    'title' => '⚠️ Omzet Menurun',
                    'body' => sprintf(
                        'Omzet 7 hari ini %s, turun %.1f%% vs minggu lalu (%s).',
                        $fmt($thisWeek),
                        abs($changePercent),
                        $fmt($lastWeek)
                    ),
                    'data' => ['this_week' => $thisWeek, 'last_week' => $lastWeek, 'change_percent' => round($changePercent, 1)],
                    'action' => 'analisis penyebab penurunan omzet',
                ],
            ];
        }

        if ($changePercent >= 20) {
            return [
                [
                    'type' => 'revenue_spike',
                    'severity' => 'info',
                    'title' => '🚀 Omzet Naik Pesat',
                    'body' => sprintf(
                        'Omzet 7 hari ini %s, naik %.1f%% vs minggu lalu (%s). Pertahankan momentum!',
                        $fmt($thisWeek),
                        $changePercent,
                        $fmt($lastWeek)
                    ),
                    'data' => ['this_week' => $thisWeek, 'last_week' => $lastWeek, 'change_percent' => round($changePercent, 1)],
                    'action' => 'tampilkan produk terlaris minggu ini',
                ],
            ];
        }

        return [];
    }

    /**
     * Analisis revenue bulan ini vs bulan lalu dengan penjelasan "kemungkinan karena...".
     * Lebih kontekstual dari analyzeRevenueTrend karena menyertakan faktor penyebab.
     */
    private function analyzeMonthlyRevenue(int $tenantId): array
    {
        $thisMonth = now();
        $lastMonth = now()->subMonth();

        $thisRevenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', $thisMonth->month)
            ->whereYear('date', $thisMonth->year)
            ->sum('total');

        $lastRevenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', $lastMonth->month)
            ->whereYear('date', $lastMonth->year)
            ->sum('total');

        if ($lastRevenue <= 0 || $thisRevenue <= 0) {
            return [];
        }

        $changePercent = (($thisRevenue - $lastRevenue) / $lastRevenue) * 100;

        // Hanya proses jika perubahan signifikan (>= 8%)
        if (abs($changePercent) < 8) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        // ── Kumpulkan faktor penyebab ──────────────────────────────
        $causes = [];

        // 1. Jumlah order turun/naik
        $thisOrders = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', $thisMonth->month)->whereYear('date', $thisMonth->year)
            ->count();
        $lastOrders = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', $lastMonth->month)->whereYear('date', $lastMonth->year)
            ->count();

        if ($lastOrders > 0) {
            $orderChange = (($thisOrders - $lastOrders) / $lastOrders) * 100;
            if (abs($orderChange) >= 10) {
                $causes[] = $orderChange < 0
                    ? sprintf('jumlah order turun %.0f%% (%d → %d order)', abs($orderChange), $lastOrders, $thisOrders)
                    : sprintf('jumlah order naik %.0f%% (%d → %d order)', $orderChange, $lastOrders, $thisOrders);
            }
        }

        // 2. Pengeluaran naik signifikan
        $thisExpense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereMonth('date', $thisMonth->month)->whereYear('date', $thisMonth->year)->sum('amount');
        $lastExpense = Transaction::where('tenant_id', $tenantId)->where('type', 'expense')
            ->whereMonth('date', $lastMonth->month)->whereYear('date', $lastMonth->year)->sum('amount');

        if ($lastExpense > 0 && $thisExpense > 0) {
            $expenseChange = (($thisExpense - $lastExpense) / $lastExpense) * 100;
            if ($expenseChange >= 15) {
                $causes[] = sprintf('pengeluaran naik %.0f%% (%s)', $expenseChange, $fmt($thisExpense));
            }
        }

        // 3. Produk terlaris bulan ini vs bulan lalu
        $topThisMonth = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->whereNotIn('sales_orders.status', ['cancelled'])
            ->whereMonth('sales_orders.date', $thisMonth->month)
            ->whereYear('sales_orders.date', $thisMonth->year)
            ->selectRaw('products.name, SUM(sales_order_items.total) as total')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->first();

        $topLastMonth = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->whereNotIn('sales_orders.status', ['cancelled'])
            ->whereMonth('sales_orders.date', $lastMonth->month)
            ->whereYear('sales_orders.date', $lastMonth->year)
            ->selectRaw('products.name, SUM(sales_order_items.total) as total')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total')
            ->first();

        if ($topThisMonth && $topLastMonth && $topThisMonth->name !== $topLastMonth->name) {
            $causes[] = "produk terlaris bergeser dari \"{$topLastMonth->name}\" ke \"{$topThisMonth->name}\"";
        }

        // 4. Pelanggan baru vs bulan lalu
        $newCustomersThis = Customer::where('tenant_id', $tenantId)
            ->whereMonth('created_at', $thisMonth->month)->whereYear('created_at', $thisMonth->year)->count();
        $newCustomersLast = Customer::where('tenant_id', $tenantId)
            ->whereMonth('created_at', $lastMonth->month)->whereYear('created_at', $lastMonth->year)->count();

        if ($newCustomersLast > 0) {
            $custChange = (($newCustomersThis - $newCustomersLast) / $newCustomersLast) * 100;
            if ($custChange <= -20) {
                $causes[] = sprintf('akuisisi pelanggan baru turun %.0f%% (%d → %d)', abs($custChange), $newCustomersLast, $newCustomersThis);
            } elseif ($custChange >= 20) {
                $causes[] = sprintf('pelanggan baru naik %.0f%% (%d → %d)', $custChange, $newCustomersLast, $newCustomersThis);
            }
        }

        // ── Susun insight ──────────────────────────────────────────
        $causeText = ! empty($causes)
            ? ' Kemungkinan karena: '.implode(', ', $causes).'.'
            : '';

        $monthName = $thisMonth->translatedFormat('F Y');
        $lastMonthName = $lastMonth->translatedFormat('F Y');

        if ($changePercent <= -15) {
            return [
                [
                    'type' => 'monthly_revenue_drop',
                    'severity' => 'critical',
                    'title' => "📉 Revenue {$monthName} Turun {$this->fmtPct($changePercent)}",
                    'body' => sprintf(
                        'Revenue bulan ini %s, turun %.1f%% dibanding %s (%s).%s',
                        $fmt($thisRevenue),
                        abs($changePercent),
                        $lastMonthName,
                        $fmt($lastRevenue),
                        $causeText
                    ),
                    'data' => [
                        'this_month' => $thisRevenue,
                        'last_month' => $lastRevenue,
                        'change_percent' => round($changePercent, 1),
                        'this_orders' => $thisOrders,
                        'last_orders' => $lastOrders,
                        'causes' => $causes,
                    ],
                    'action' => 'analisis penyebab penurunan revenue bulan ini',
                ],
            ];
        }

        if ($changePercent <= -8) {
            return [
                [
                    'type' => 'monthly_revenue_decline',
                    'severity' => 'warning',
                    'title' => "⚠️ Revenue {$monthName} Menurun {$this->fmtPct($changePercent)}",
                    'body' => sprintf(
                        'Revenue bulan ini %s, turun %.1f%% vs %s (%s).%s',
                        $fmt($thisRevenue),
                        abs($changePercent),
                        $lastMonthName,
                        $fmt($lastRevenue),
                        $causeText
                    ),
                    'data' => [
                        'this_month' => $thisRevenue,
                        'last_month' => $lastRevenue,
                        'change_percent' => round($changePercent, 1),
                        'causes' => $causes,
                    ],
                    'action' => 'tampilkan perbandingan penjualan bulan ini vs bulan lalu',
                ],
            ];
        }

        if ($changePercent >= 15) {
            return [
                [
                    'type' => 'monthly_revenue_growth',
                    'severity' => 'info',
                    'title' => "🚀 Revenue {$monthName} Naik {$this->fmtPct($changePercent)}",
                    'body' => sprintf(
                        'Revenue bulan ini %s, naik %.1f%% vs %s (%s).%s Pertahankan momentum!',
                        $fmt($thisRevenue),
                        $changePercent,
                        $lastMonthName,
                        $fmt($lastRevenue),
                        $causeText
                    ),
                    'data' => [
                        'this_month' => $thisRevenue,
                        'last_month' => $lastRevenue,
                        'change_percent' => round($changePercent, 1),
                        'causes' => $causes,
                    ],
                    'action' => 'tampilkan produk terlaris bulan ini',
                ],
            ];
        }

        return [];
    }

    private function fmtPct(float $pct): string
    {
        return number_format(abs($pct), 1).'%';
    }

    /**
     * Prediksi stok habis: estimasi berapa hari stok tersisa berdasarkan rata-rata penjualan.
     */
    private function analyzeStockDepletion(int $tenantId): array
    {
        $insights = [];

        // Ambil produk aktif dengan stok
        $stocks = ProductStock::with(['product', 'warehouse'])
            ->whereHas('product', fn ($q) => $q->where('tenant_id', $tenantId)->where('is_active', true))
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

            if ($avgDaily <= 0) {
                continue;
            }

            $daysLeft = (int) floor($stock->quantity / $avgDaily);

            if ($daysLeft <= 3) {
                $insights[] = [
                    'type' => 'stock_critical_'.$stock->product_id,
                    'severity' => 'critical',
                    'title' => "🔴 Stok {$stock->product->name} Kritis",
                    'body' => "Stok {$stock->product->name} di {$stock->warehouse->name} tinggal {$stock->quantity} {$stock->product->unit}. Berdasarkan rata-rata penjualan, stok akan habis dalam **{$daysLeft} hari**.",
                    'data' => ['product_id' => $stock->product_id, 'days_left' => $daysLeft, 'quantity' => $stock->quantity, 'avg_daily' => round($avgDaily, 1)],
                    'action' => "buat purchase order untuk {$stock->product->name}",
                ];
            } elseif ($daysLeft <= 7) {
                $insights[] = [
                    'type' => 'stock_low_'.$stock->product_id,
                    'severity' => 'warning',
                    'title' => "⚠️ Stok {$stock->product->name} Menipis",
                    'body' => "Stok {$stock->product->name} tinggal {$stock->quantity} {$stock->product->unit}. Estimasi habis dalam **{$daysLeft} hari** berdasarkan rata-rata penjualan {$avgDaily} {$stock->product->unit}/hari.",
                    'data' => ['product_id' => $stock->product_id, 'days_left' => $daysLeft, 'quantity' => $stock->quantity, 'avg_daily' => round($avgDaily, 1)],
                    'action' => "tambah stok {$stock->product->name}",
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

        if ($avgPrevious <= 0 || $thisWeek <= 0) {
            return [];
        }

        $changePercent = (($thisWeek - $avgPrevious) / $avgPrevious) * 100;

        if ($changePercent < 30) {
            return [];
        } // tidak signifikan

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

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

        return [
            [
                'type' => 'expense_anomaly',
                'severity' => $changePercent >= 50 ? 'critical' : 'warning',
                'title' => '💸 Lonjakan Pengeluaran Terdeteksi',
                'body' => sprintf(
                    'Pengeluaran 7 hari ini %s, naik %.1f%% dari rata-rata mingguan (%s).%s',
                    $fmt($thisWeek),
                    $changePercent,
                    $fmt($avgPrevious),
                    $categoryNote
                ),
                'data' => ['this_week' => $thisWeek, 'avg_previous' => $avgPrevious, 'change_percent' => round($changePercent, 1)],
                'action' => 'tampilkan breakdown pengeluaran minggu ini',
            ],
        ];
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

        if ($overdueCount === 0) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return [
            [
                'type' => 'overdue_receivables',
                'severity' => $overdueAmount > 5_000_000 ? 'critical' : 'warning',
                'title' => "🔔 {$overdueCount} Piutang Jatuh Tempo",
                'body' => "Ada {$overdueCount} tagihan senilai {$fmt($overdueAmount)} yang sudah melewati jatuh tempo. Segera lakukan follow-up ke pelanggan.",
                'data' => ['count' => $overdueCount, 'amount' => $overdueAmount],
                'action' => 'tampilkan daftar piutang yang sudah jatuh tempo',
            ],
        ];
    }

    /**
     * BUG-SALES-004 FIX: Credit limit monitoring - deteksi customer yang mendekati/melebihi limit
     */
    private function analyzeCreditLimits(int $tenantId): array
    {
        // Get all customers with credit limit set
        $customers = Customer::where('tenant_id', $tenantId)
            ->whereNotNull('credit_limit')
            ->where('credit_limit', '>', 0)
            ->get();

        if ($customers->isEmpty()) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
        $alerts = [];
        $criticalCount = 0;
        $warningCount = 0;
        $totalAtRisk = 0;

        foreach ($customers as $customer) {
            $outstanding = $customer->outstandingBalance();
            $creditLimit = (float) $customer->credit_limit;
            $usagePercent = $creditLimit > 0 ? ($outstanding / $creditLimit) * 100 : 0;

            // Critical: Already exceeded or >95% used
            if ($usagePercent >= 95) {
                $criticalCount++;
                $totalAtRisk += $outstanding;
                $alerts[] = [
                    'type' => 'credit_limit_critical',
                    'severity' => 'critical',
                    'title' => "🚨 {$customer->name} - Limit Kredit Terlampaui",
                    'body' => "Customer {$customer->name} telah menggunakan {$fmt($outstanding)} dari {$fmt($creditLimit)} (".round($usagePercent, 1).'%). '.
                        "Sisa kredit: {$fmt($customer->availableCredit())}. ".
                        'Sales Order baru akan ditolak otomatis.',
                    'data' => [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'credit_limit' => $creditLimit,
                        'outstanding' => $outstanding,
                        'usage_percent' => round($usagePercent, 1),
                        'available' => $customer->availableCredit(),
                    ],
                    'action' => 'review credit limit atau hubungi customer untuk pembayaran',
                ];
            }
            // Warning: 80-94% used
            elseif ($usagePercent >= 80) {
                $warningCount++;
                $totalAtRisk += $outstanding;
                $alerts[] = [
                    'type' => 'credit_limit_warning',
                    'severity' => 'warning',
                    'title' => "⚠️ {$customer->name} - Mendekati Limit Kredit",
                    'body' => "Customer {$customer->name} telah menggunakan {$fmt($outstanding)} dari {$fmt($creditLimit)} (".round($usagePercent, 1).'%). '.
                        "Sisa kredit: {$fmt($customer->availableCredit())}. ".
                        'Segera follow-up untuk pembayaran sebelum limit terlampaui.',
                    'data' => [
                        'customer_id' => $customer->id,
                        'customer_name' => $customer->name,
                        'credit_limit' => $creditLimit,
                        'outstanding' => $outstanding,
                        'usage_percent' => round($usagePercent, 1),
                        'available' => $customer->availableCredit(),
                    ],
                    'action' => 'follow-up pembayaran atau pertimbangkan peningkatan limit',
                ];
            }
        }

        if (empty($alerts)) {
            return [];
        }

        // Add summary insight
        $summary = [
            'type' => 'credit_limit_summary',
            'severity' => $criticalCount > 0 ? 'critical' : 'warning',
            'title' => "📊 Credit Limit Monitor: {$criticalCount} Critical, {$warningCount} Warning",
            'body' => "Total {$criticalCount} customer melebihi batas kredit dan {$warningCount} customer mendekati limit. ".
                "Total exposure: {$fmt($totalAtRisk)}. ".
                'Sales Order untuk customer yang melebihi limit akan ditolak otomatis.',
            'data' => [
                'critical_count' => $criticalCount,
                'warning_count' => $warningCount,
                'total_at_risk' => $totalAtRisk,
            ],
            'action' => 'review semua customer dengan credit limit issues',
        ];

        array_unshift($alerts, $summary);

        return $alerts;
    }

    /**
     * BUG-FIN-003 FIX: Currency rate staleness monitoring
     *
     * Detect stale exchange rates that could cause inaccurate multi-currency conversions
     */
    private function analyzeCurrencyStaleness(int $tenantId): array
    {
        $currencyService = new CurrencyService;
        $report = $currencyService->getStaleCurrenciesReport($tenantId);

        $fmt = fn ($n) => is_numeric($n) ? 'Rp '.number_format($n, 0, ',', '.') : $n;

        $alerts = [];
        $criticalCount = count($report['critical']);
        $warningCount = count($report['warning']);

        // Critical alerts
        foreach ($report['critical'] as $currency) {
            $alerts[] = [
                'type' => 'currency_rate_critical',
                'severity' => 'critical',
                'title' => "🚨 Kurs {$currency['currency_code']} KRITIS - Tidak Update {$currency['days_since_update']} Hari",
                'body' => "Kurs {$currency['currency_name']} ({$currency['currency_code']}) sudah {$currency['days_since_update']} hari tidak diperbarui. ".
                    "Rate saat ini: {$fmt($currency['rate_to_idr'])}. ".
                    'Konversi mata uang TIDAK AKURAT dan dapat menyebabkan kesalahan laporan keuangan!',
                'data' => $currency,
                'action' => 'update kurs manual di Settings → Currency atau jalankan "Update Currency Rates"',
            ];
        }

        // Warning alerts
        foreach ($report['warning'] as $currency) {
            $alerts[] = [
                'type' => 'currency_rate_warning',
                'severity' => 'warning',
                'title' => "⚠️ Kurs {$currency['currency_code']} Lama - {$currency['days_since_update']} Hari",
                'body' => "Kurs {$currency['currency_name']} ({$currency['currency_code']}) sudah {$currency['days_since_update']} hari tidak diperbarui. ".
                    "Rate saat ini: {$fmt($currency['rate_to_idr'])}. ".
                    'Segera update untuk menjaga akurasi konversi mata uang.',
                'data' => $currency,
                'action' => 'update kurs untuk menjaga akurasi',
            ];
        }

        // Summary alert
        if ($criticalCount > 0 || $warningCount > 0) {
            $summary = [
                'type' => 'currency_staleness_summary',
                'severity' => $criticalCount > 0 ? 'critical' : 'warning',
                'title' => "💱 Currency Rate Monitor: {$criticalCount} Critical, {$warningCount} Warning",
                'body' => "Total {$criticalCount} mata uang dengan kurs KRITIS (tidak update >30 hari) dan ".
                    "{$warningCount} mata uang dengan kurs lama (tidak update >7 hari). ".
                    'Konversi multi-currency mungkin TIDAK AKURAT. '.
                    'Update otomatis dijadwalkan setiap hari jam 06:00.',
                'data' => [
                    'critical_count' => $criticalCount,
                    'warning_count' => $warningCount,
                    'total_affected' => $criticalCount + $warningCount,
                    'next_scheduled_update' => 'Daily at 06:00',
                ],
                'action' => 'review semua currency rates dan update jika diperlukan',
            ];

            array_unshift($alerts, $summary);
        }

        return $alerts;
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

        if ($soldLastWeek->isEmpty()) {
            return [];
        }

        $soldRecently = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.date', [now()->subDays(3)->toDateString(), now()->toDateString()])
            ->pluck('sales_order_items.product_id')
            ->unique();

        $stalled = $soldLastWeek->diff($soldRecently);

        if ($stalled->count() < 3) {
            return [];
        } // tidak signifikan

        return [
            [
                'type' => 'sales_stall',
                'severity' => 'info',
                'title' => '📊 Penjualan Beberapa Produk Melambat',
                'body' => "{$stalled->count()} produk yang aktif terjual minggu lalu tidak ada transaksi dalam 3 hari terakhir. Mungkin perlu promosi atau cek ketersediaan stok.",
                'data' => ['stalled_count' => $stalled->count(), 'product_ids' => $stalled->values()->toArray()],
                'action' => 'tampilkan produk yang tidak terjual 3 hari terakhir',
            ],
        ];
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

        if (! $topProduct || $topProduct->total_revenue <= 0) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return [
            [
                'type' => 'top_product',
                'severity' => 'info',
                'title' => "⭐ Produk Terlaris: {$topProduct->name}",
                'body' => "{$topProduct->name} menjadi produk terlaris minggu ini dengan {$topProduct->total_qty} unit terjual, menghasilkan {$fmt($topProduct->total_revenue)}.",
                'data' => ['product_name' => $topProduct->name, 'qty' => $topProduct->total_qty, 'revenue' => $topProduct->total_revenue],
                'action' => "tampilkan detail penjualan {$topProduct->name}",
            ],
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function severityOrder(string $severity): int
    {
        return match ($severity) {
            'critical' => 0,
            'warning' => 1,
            'info' => 2,
            default => 3,
        };
    }

    /**
     * Buat teks digest ringkas untuk email/WA.
     */
    public function buildDigestText(int $tenantId, string $period = 'daily'): string
    {
        $insights = $this->analyze($tenantId);

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

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
            '📅 '.now()->translatedFormat('l, d F Y'),
            '',
            '**Ringkasan Hari Ini:**',
            "• Omzet: {$fmt($todayRevenue)} ({$todayOrders} transaksi)",
            "• Pengeluaran: {$fmt($todayExpense)}",
            "• Profit: {$fmt($todayRevenue - $todayExpense)}",
            '',
        ];

        if (! empty($insights)) {
            $lines[] = '**Insight AI:**';
            foreach (array_slice($insights, 0, 5) as $insight) {
                $icon = match ($insight['severity']) {
                    'critical' => '🔴',
                    'warning' => '🟡',
                    default => '🟢',
                };
                $lines[] = "{$icon} {$insight['title']}";
                $lines[] = "   {$insight['body']}";
                $lines[] = '';
            }
        }

        $lines[] = '---';
        $lines[] = 'Buka dashboard untuk detail lengkap: '.url('/dashboard');

        return implode("\n", $lines);
    }

    // ─── New Analyzers ────────────────────────────────────────────

    /**
     * Prediksi arus kas 30 hari ke depan.
     * Bandingkan: AR yang akan masuk (invoice due) vs AP yang harus dibayar (payable due).
     * Jika proyeksi kas negatif → critical alert.
     */
    private function analyzeCashFlowPrediction(int $tenantId): array
    {
        $fmt = fn ($n) => 'Rp '.number_format(abs($n), 0, ',', '.');

        // AR yang jatuh tempo dalam 30 hari ke depan (potensi kas masuk)
        $arIncoming = Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [today(), today()->addDays(30)])
            ->sum('remaining_amount');

        // AP yang jatuh tempo dalam 30 hari ke depan (kas keluar wajib)
        $apOutgoing = Payable::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereBetween('due_date', [today(), today()->addDays(30)])
            ->sum('remaining_amount');

        // Saldo kas saat ini dari GL (akun 1101 + 1102)
        $cashAccounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', ['1101', '1102'])
            ->pluck('id');

        $currentCash = 0;
        if ($cashAccounts->isNotEmpty()) {
            $debit = (float) JournalEntryLine::whereIn('account_id', $cashAccounts)
                ->whereHas('journalEntry', fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'posted'))
                ->sum('debit');
            $credit = (float) JournalEntryLine::whereIn('account_id', $cashAccounts)
                ->whereHas('journalEntry', fn ($q) => $q->where('tenant_id', $tenantId)->where('status', 'posted'))
                ->sum('credit');
            $currentCash = $debit - $credit;
        }

        // Jika tidak ada data sama sekali, skip
        if ($arIncoming <= 0 && $apOutgoing <= 0 && $currentCash <= 0) {
            return [];
        }

        $projectedCash = $currentCash + $arIncoming - $apOutgoing;
        $netFlow = $arIncoming - $apOutgoing;

        // Hanya alert jika ada risiko
        if ($projectedCash >= 0 && $netFlow >= 0) {
            // Kas aman, tapi beri info jika ada AR/AP signifikan
            if ($arIncoming > 0 || $apOutgoing > 0) {
                return [
                    [
                        'type' => 'cashflow_healthy',
                        'severity' => 'info',
                        'title' => '💧 Proyeksi Arus Kas 30 Hari: Sehat',
                        'body' => sprintf(
                            'Proyeksi kas akhir bulan: %s. AR masuk: %s | AP keluar: %s.',
                            $fmt($projectedCash),
                            $fmt($arIncoming),
                            $fmt($apOutgoing)
                        ),
                        'data' => compact('currentCash', 'arIncoming', 'apOutgoing', 'projectedCash'),
                        'action' => 'tampilkan proyeksi arus kas 30 hari',
                    ],
                ];
            }

            return [];
        }

        if ($projectedCash < 0) {
            return [
                [
                    'type' => 'cashflow_deficit',
                    'severity' => 'critical',
                    'title' => '🚨 Proyeksi Defisit Kas 30 Hari',
                    'body' => sprintf(
                        'Proyeksi kas 30 hari ke depan: %s (DEFISIT). Kas saat ini: %s | AR masuk: %s | AP jatuh tempo: %s. Segera tindak lanjuti piutang atau tunda pembayaran.',
                        $fmt($projectedCash),
                        $fmt($currentCash),
                        $fmt($arIncoming),
                        $fmt($apOutgoing)
                    ),
                    'data' => compact('currentCash', 'arIncoming', 'apOutgoing', 'projectedCash'),
                    'action' => 'tampilkan daftar piutang dan hutang jatuh tempo 30 hari',
                ],
            ];
        }

        // Net flow negatif tapi kas masih positif → warning
        return [
            [
                'type' => 'cashflow_warning',
                'severity' => 'warning',
                'title' => '⚠️ Arus Kas Bersih Negatif 30 Hari',
                'body' => sprintf(
                    'AP jatuh tempo (%s) melebihi AR yang akan masuk (%s). Selisih: %s. Kas saat ini masih cukup (%s).',
                    $fmt($apOutgoing),
                    $fmt($arIncoming),
                    $fmt(abs($netFlow)),
                    $fmt($currentCash)
                ),
                'data' => compact('currentCash', 'arIncoming', 'apOutgoing', 'projectedCash'),
                'action' => 'tampilkan proyeksi arus kas',
            ],
        ];
    }

    /**
     * Budget variance alert: deteksi anggaran yang sudah terpakai > 80% atau over budget.
     * Hanya untuk periode bulan berjalan.
     */
    private function analyzeBudgetVariance(int $tenantId): array
    {
        $period = now()->format('Y-m');
        $budgets = Budget::where('tenant_id', $tenantId)
            ->where('period', $period)
            ->where('status', 'active')
            ->where('amount', '>', 0)
            ->get();

        if ($budgets->isEmpty()) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        $overBudget = $budgets->filter(fn ($b) => $b->realized > $b->amount);
        $nearLimit = $budgets->filter(fn ($b) => $b->realized <= $b->amount && ($b->realized / $b->amount) >= 0.8);

        $insights = [];

        // Over budget items
        if ($overBudget->isNotEmpty()) {
            $totalOver = $overBudget->sum(fn ($b) => $b->realized - $b->amount);
            $worstItem = $overBudget->sortByDesc(fn ($b) => $b->realized - $b->amount)->first();
            $worstPct = round($worstItem->realized / $worstItem->amount * 100, 1);

            $insights[] = [
                'type' => 'budget_over',
                'severity' => 'critical',
                'title' => "🔴 {$overBudget->count()} Anggaran Over Budget",
                'body' => sprintf(
                    '%d item anggaran melebihi batas bulan ini, total kelebihan %s. Terparah: "%s" (%s%% dari anggaran %s).',
                    $overBudget->count(),
                    $fmt($totalOver),
                    $worstItem->name,
                    $worstPct,
                    $fmt($worstItem->amount)
                ),
                'data' => [
                    'over_count' => $overBudget->count(),
                    'total_over' => $totalOver,
                    'worst_item' => $worstItem->name,
                    'worst_pct' => $worstPct,
                ],
                'action' => 'tampilkan anggaran yang over budget bulan ini',
            ];
        }

        // Near limit (80-100%)
        if ($nearLimit->isNotEmpty()) {
            $names = $nearLimit->take(3)->pluck('name')->implode(', ');
            $insights[] = [
                'type' => 'budget_near_limit',
                'severity' => 'warning',
                'title' => "⚠️ {$nearLimit->count()} Anggaran Hampir Habis",
                'body' => sprintf(
                    '%d anggaran sudah terpakai ≥80%%: %s%s. Pertimbangkan revisi anggaran atau penghematan.',
                    $nearLimit->count(),
                    $names,
                    $nearLimit->count() > 3 ? ', dan lainnya' : ''
                ),
                'data' => ['near_count' => $nearLimit->count(), 'items' => $nearLimit->pluck('name')->toArray()],
                'action' => 'tampilkan detail anggaran bulan ini',
            ];
        }

        // Ringkasan utilisasi keseluruhan
        $totalBudget = $budgets->sum('amount');
        $totalRealized = $budgets->sum('realized');
        $usagePct = round($totalRealized / $totalBudget * 100, 1);

        if ($usagePct >= 90 && $overBudget->isEmpty()) {
            $insights[] = [
                'type' => 'budget_high_usage',
                'severity' => 'warning',
                'title' => "📊 Utilisasi Anggaran Bulan Ini: {$usagePct}%",
                'body' => sprintf(
                    'Total realisasi %s dari anggaran %s (%s%%). Sisa anggaran hanya %s.',
                    $fmt($totalRealized),
                    $fmt($totalBudget),
                    $usagePct,
                    $fmt($totalBudget - $totalRealized)
                ),
                'data' => compact('totalBudget', 'totalRealized', 'usagePct'),
                'action' => 'tampilkan laporan budget vs aktual',
            ];
        }

        return $insights;
    }

    /**
     * Analisis biaya payroll: bandingkan payroll bulan ini vs bulan lalu,
     * dan hitung rasio payroll terhadap pendapatan.
     */
    private function analyzePayrollCost(int $tenantId): array
    {
        $thisPeriod = now()->format('Y-m');
        $lastPeriod = now()->subMonth()->format('Y-m');

        $thisPayroll = PayrollRun::where('tenant_id', $tenantId)
            ->where('period', $thisPeriod)
            ->where('status', 'paid')
            ->sum('total_net');

        $lastPayroll = PayrollRun::where('tenant_id', $tenantId)
            ->where('period', $lastPeriod)
            ->where('status', 'paid')
            ->sum('total_net');

        if ($thisPayroll <= 0) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        $insights = [];

        // Kenaikan payroll signifikan (>15%)
        if ($lastPayroll > 0) {
            $changePct = (($thisPayroll - $lastPayroll) / $lastPayroll) * 100;

            if ($changePct >= 15) {
                $insights[] = [
                    'type' => 'payroll_spike',
                    'severity' => 'warning',
                    'title' => '💼 Biaya Payroll Naik Signifikan',
                    'body' => sprintf(
                        'Payroll bulan ini %s, naik %.1f%% dari bulan lalu (%s). Periksa apakah ada penambahan karyawan atau lembur berlebih.',
                        $fmt($thisPayroll),
                        $changePct,
                        $fmt($lastPayroll)
                    ),
                    'data' => ['this_payroll' => $thisPayroll, 'last_payroll' => $lastPayroll, 'change_pct' => round($changePct, 1)],
                    'action' => 'tampilkan detail payroll bulan ini',
                ];
            }
        }

        // Rasio payroll terhadap pendapatan bulan ini
        $thisRevenue = SalesOrder::where('tenant_id', $tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('total');

        if ($thisRevenue > 0) {
            $payrollRatio = round(($thisPayroll / $thisRevenue) * 100, 1);

            // Rasio > 40% adalah warning, > 60% critical
            if ($payrollRatio >= 60) {
                $insights[] = [
                    'type' => 'payroll_ratio_critical',
                    'severity' => 'critical',
                    'title' => "🚨 Rasio Payroll/Pendapatan Sangat Tinggi: {$payrollRatio}%",
                    'body' => sprintf(
                        'Biaya payroll %s = %.1f%% dari pendapatan %s bulan ini. Rasio ideal < 40%%. Bisnis berisiko merugi.',
                        $fmt($thisPayroll),
                        $payrollRatio,
                        $fmt($thisRevenue)
                    ),
                    'data' => ['payroll' => $thisPayroll, 'revenue' => $thisRevenue, 'ratio' => $payrollRatio],
                    'action' => 'analisis efisiensi biaya karyawan',
                ];
            } elseif ($payrollRatio >= 40) {
                $insights[] = [
                    'type' => 'payroll_ratio_high',
                    'severity' => 'warning',
                    'title' => "⚠️ Rasio Payroll/Pendapatan Tinggi: {$payrollRatio}%",
                    'body' => sprintf(
                        'Biaya payroll %s = %.1f%% dari pendapatan %s. Pertimbangkan optimasi biaya SDM.',
                        $fmt($thisPayroll),
                        $payrollRatio,
                        $fmt($thisRevenue)
                    ),
                    'data' => ['payroll' => $thisPayroll, 'revenue' => $thisRevenue, 'ratio' => $payrollRatio],
                    'action' => 'tampilkan laporan payroll vs pendapatan',
                ];
            } else {
                // Rasio sehat — info saja
                $insights[] = [
                    'type' => 'payroll_ratio_healthy',
                    'severity' => 'info',
                    'title' => "✅ Rasio Payroll Sehat: {$payrollRatio}%",
                    'body' => sprintf(
                        'Biaya payroll %s = %.1f%% dari pendapatan %s. Efisiensi SDM dalam batas wajar.',
                        $fmt($thisPayroll),
                        $payrollRatio,
                        $fmt($thisRevenue)
                    ),
                    'data' => ['payroll' => $thisPayroll, 'revenue' => $thisRevenue, 'ratio' => $payrollRatio],
                    'action' => 'tampilkan detail payroll',
                ];
            }
        }

        return $insights;
    }

    /**
     * GL-based insights: analisis dari journal entries yang sudah diposting.
     * - Deteksi akun dengan saldo tidak wajar (aset negatif, ekuitas negatif)
     * - Deteksi lonjakan beban dari GL
     * - Alert jika tidak ada jurnal diposting dalam 7 hari (GL tidak aktif)
     */
    private function analyzeGlInsights(int $tenantId): array
    {
        $insights = [];
        $fmt = fn ($n) => 'Rp '.number_format(abs($n), 0, ',', '.');

        // ── 1. Cek apakah GL aktif (ada jurnal diposting 7 hari terakhir) ──
        $recentJournals = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->count();

        $totalJournals = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->where('status', 'posted')
            ->count();

        if ($totalJournals > 0 && $recentJournals === 0) {
            $insights[] = [
                'type' => 'gl_inactive',
                'severity' => 'warning',
                'title' => '📒 GL Tidak Aktif 7 Hari',
                'body' => 'Tidak ada jurnal yang diposting dalam 7 hari terakhir. Pastikan transaksi dicatat dengan benar di General Ledger.',
                'data' => ['last_7_days' => 0, 'total_posted' => $totalJournals],
                'action' => 'buka daftar jurnal umum',
            ];
        }

        // ── 2. Deteksi akun kas/bank dengan saldo negatif ──
        $cashAccounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', ['1101', '1102'])
            ->where('is_active', true)
            ->get();

        foreach ($cashAccounts as $acc) {
            $balance = $acc->balance($tenantId);
            if ($balance < 0) {
                $insights[] = [
                    'type' => 'gl_negative_cash_'.$acc->code,
                    'severity' => 'critical',
                    'title' => "🔴 Saldo {$acc->name} Negatif",
                    'body' => "Akun {$acc->code} - {$acc->name} memiliki saldo negatif: ({$fmt($balance)}). Kemungkinan ada jurnal yang salah atau pembayaran melebihi saldo.",
                    'data' => ['account_code' => $acc->code, 'account_name' => $acc->name, 'balance' => $balance],
                    'action' => "periksa jurnal akun {$acc->name}",
                ];
            }
        }

        // ── 3. Deteksi lonjakan beban dari GL bulan ini vs bulan lalu ──
        $expenseAccounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->where('is_header', false)
            ->where('is_active', true)
            ->pluck('id');

        if ($expenseAccounts->isNotEmpty()) {
            $thisMonthExpense = (float) JournalEntryLine::whereIn('account_id', $expenseAccounts)
                ->whereHas(
                    'journalEntry',
                    fn ($q) => $q
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'posted')
                        ->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                )->sum('debit');

            $lastMonthExpense = (float) JournalEntryLine::whereIn('account_id', $expenseAccounts)
                ->whereHas(
                    'journalEntry',
                    fn ($q) => $q
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'posted')
                        ->whereMonth('date', now()->subMonth()->month)
                        ->whereYear('date', now()->subMonth()->year)
                )->sum('debit');

            if ($lastMonthExpense > 0 && $thisMonthExpense > 0) {
                $changePct = (($thisMonthExpense - $lastMonthExpense) / $lastMonthExpense) * 100;

                if ($changePct >= 30) {
                    $insights[] = [
                        'type' => 'gl_expense_spike',
                        'severity' => $changePct >= 50 ? 'critical' : 'warning',
                        'title' => '📈 Lonjakan Beban GL Bulan Ini',
                        'body' => sprintf(
                            'Total beban dari GL bulan ini %s, naik %.1f%% dari bulan lalu (%s). Periksa jurnal beban untuk memastikan tidak ada posting yang salah.',
                            $fmt($thisMonthExpense),
                            $changePct,
                            $fmt($lastMonthExpense)
                        ),
                        'data' => [
                            'this_month' => $thisMonthExpense,
                            'last_month' => $lastMonthExpense,
                            'change_pct' => round($changePct, 1),
                        ],
                        'action' => 'tampilkan laporan laba rugi dari GL',
                    ];
                }
            }
        }

        // ── 4. Deteksi piutang usaha dari GL yang sangat besar vs pendapatan ──
        $arAccount = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('code', '1103')
            ->where('is_active', true)
            ->first();

        if ($arAccount) {
            $arBalance = $arAccount->balance($tenantId);

            $monthlyRevenue = (float) JournalEntryLine::whereHas(
                'account',
                fn ($q) => $q->where('tenant_id', $tenantId)->where('type', 'revenue')
            )
                ->whereHas(
                    'journalEntry',
                    fn ($q) => $q
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'posted')
                        ->whereMonth('date', now()->month)
                        ->whereYear('date', now()->year)
                )->sum('credit');

            if ($monthlyRevenue > 0 && $arBalance > 0) {
                $arRatio = round(($arBalance / $monthlyRevenue) * 100, 1);

                if ($arRatio >= 200) {
                    $insights[] = [
                        'type' => 'gl_ar_high',
                        'severity' => 'warning',
                        'title' => "📋 Piutang Usaha Sangat Tinggi: {$arRatio}% dari Pendapatan",
                        'body' => sprintf(
                            'Saldo piutang usaha di GL: %s = %.1f%% dari pendapatan bulan ini (%s). Koleksi piutang perlu dipercepat.',
                            $fmt($arBalance),
                            $arRatio,
                            $fmt($monthlyRevenue)
                        ),
                        'data' => ['ar_balance' => $arBalance, 'monthly_revenue' => $monthlyRevenue, 'ratio' => $arRatio],
                        'action' => 'tampilkan aging piutang',
                    ];
                }
            }
        }

        return $insights;
    }
}
