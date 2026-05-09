<?php

namespace App\Services\ERP;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Transaction;

class ForecastTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'get_forecast',
                'description' => 'Prediksi dan forecast bisnis berdasarkan data historis. Gunakan untuk: '
                    .'"prediksi omzet bulan depan", "kapan stok kopi habis?", '
                    .'"forecast penjualan 30 hari ke depan", "estimasi kebutuhan restock", '
                    .'"tren pertumbuhan bisnis", "proyeksi pendapatan".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'forecast_type' => [
                            'type' => 'string',
                            'description' => 'Jenis forecast: revenue (omzet), stock_depletion (kapan stok habis), restock_need (kebutuhan restock)',
                        ],
                        'product_name' => ['type' => 'string', 'description' => 'Nama produk (untuk stock_depletion dan restock_need)'],
                        'days_ahead' => ['type' => 'integer', 'description' => 'Berapa hari ke depan untuk prediksi (default: 30)'],
                        'base_days' => ['type' => 'integer', 'description' => 'Berapa hari historis sebagai basis (default: 30)'],
                    ],
                    'required' => ['forecast_type'],
                ],
            ],
            [
                'name' => 'compare_periods',
                'description' => 'Bandingkan performa bisnis antar dua periode. Gunakan untuk: '
                    .'"bandingkan penjualan bulan ini vs bulan lalu", '
                    .'"perbandingan omzet minggu ini vs minggu lalu", '
                    .'"growth penjualan per produk bulan ini vs bulan lalu", '
                    .'"keuangan bulan ini vs bulan lalu".',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'compare_type' => [
                            'type' => 'string',
                            'description' => 'Jenis perbandingan: sales (penjualan), finance (keuangan), products (per produk)',
                        ],
                        'period_a' => ['type' => 'string', 'description' => 'Periode pertama: this_month, this_week, last_month, last_week (default: this_month)'],
                        'period_b' => ['type' => 'string', 'description' => 'Periode kedua: last_month, last_week, two_months_ago (default: last_month)'],
                    ],
                    'required' => ['compare_type'],
                ],
            ],
        ];
    }

    public function getForecast(array $args): array
    {
        return match ($args['forecast_type'] ?? '') {
            'revenue' => $this->forecastRevenue($args),
            'stock_depletion' => $this->forecastStockDepletion($args),
            'restock_need' => $this->forecastRestockNeed($args),
            default => ['status' => 'error', 'message' => 'Tipe forecast tidak dikenali.'],
        };
    }

    public function comparePeriods(array $args): array
    {
        $type = $args['compare_type'] ?? 'sales';
        $periodA = $args['period_a'] ?? 'this_month';
        $periodB = $args['period_b'] ?? 'last_month';

        [$startA, $endA] = $this->resolvePeriod($periodA);
        [$startB, $endB] = $this->resolvePeriod($periodB);

        return match ($type) {
            'sales' => $this->compareSales($startA, $endA, $startB, $endB, $periodA, $periodB),
            'finance' => $this->compareFinance($startA, $endA, $startB, $endB, $periodA, $periodB),
            'products' => $this->compareProducts($startA, $endA, $startB, $endB, $periodA, $periodB),
            default => ['status' => 'error', 'message' => 'Tipe perbandingan tidak dikenali.'],
        };
    }

    // ─── Forecast helpers ─────────────────────────────────────────

    private function forecastRevenue(array $args): array
    {
        $baseDays = (int) ($args['base_days'] ?? 30);
        $aheadDays = (int) ($args['days_ahead'] ?? 30);

        $history = SalesOrder::where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subDays($baseDays))
            ->selectRaw('DATE(date) as day, SUM(total) as revenue')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        if ($history->count() < 3) {
            return ['status' => 'error', 'message' => 'Data historis terlalu sedikit untuk membuat prediksi (minimal 3 hari).'];
        }

        $revenues = $history->pluck('revenue')->map(fn ($v) => (float) $v)->toArray();
        $avgDaily = array_sum($revenues) / count($revenues);
        $trend = $this->calcTrend($revenues);
        $projected = ($avgDaily + $trend * $aheadDays / 2) * $aheadDays;

        // Weekly breakdown
        $weeks = [];
        for ($w = 1; $w <= ceil($aheadDays / 7); $w++) {
            $weekRevenue = ($avgDaily + $trend * ($w - 0.5) * 7) * min(7, $aheadDays - ($w - 1) * 7);
            $weeks[] = [
                'minggu' => "Minggu ke-{$w}",
                'proyeksi' => 'Rp '.number_format(max(0, $weekRevenue), 0, ',', '.'),
            ];
        }

        return [
            'status' => 'success',
            'data' => [
                'basis' => "{$baseDays} hari terakhir",
                'rata_rata_harian' => 'Rp '.number_format($avgDaily, 0, ',', '.'),
                'tren_harian' => ($trend >= 0 ? '+' : '').'Rp '.number_format($trend, 0, ',', '.').'/hari',
                'proyeksi_'.$aheadDays.'_hari' => 'Rp '.number_format(max(0, $projected), 0, ',', '.'),
                'proyeksi_per_minggu' => $weeks,
                'catatan' => 'Proyeksi berdasarkan tren linear dari data historis. Aktual bisa berbeda.',
            ],
        ];
    }

    private function forecastStockDepletion(array $args): array
    {
        $productName = $args['product_name'] ?? '';
        $baseDays = (int) ($args['base_days'] ?? 30);

        $product = Product::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$productName}%")
            ->first();

        if (! $product) {
            return ['status' => 'error', 'message' => "Produk '{$productName}' tidak ditemukan."];
        }

        $currentStock = ProductStock::where('product_id', $product->id)->sum('quantity');

        $soldQty = SalesOrderItem::whereHas('salesOrder', fn ($q) => $q->where('tenant_id', $this->tenantId)
            ->whereNotIn('status', ['cancelled'])
            ->where('date', '>=', now()->subDays($baseDays))
        )->where('product_id', $product->id)->sum('quantity');

        $avgDailySales = $baseDays > 0 ? $soldQty / $baseDays : 0;

        if ($avgDailySales <= 0) {
            return ['status' => 'success', 'data' => [
                'produk' => $product->name,
                'stok_saat_ini' => $currentStock.' '.$product->unit,
                'rata_penjualan' => '0/hari',
                'estimasi_habis' => 'Tidak bisa diprediksi (tidak ada penjualan)',
            ]];
        }

        $daysLeft = $currentStock / $avgDailySales;
        $depletionDate = now()->addDays((int) $daysLeft);

        return ['status' => 'success', 'data' => [
            'produk' => $product->name,
            'stok_saat_ini' => $currentStock.' '.$product->unit,
            'rata_penjualan' => round($avgDailySales, 1).' '.$product->unit.'/hari',
            'estimasi_habis' => round($daysLeft, 0).' hari lagi ('.$depletionDate->format('d M Y').')',
            'rekomendasi' => $daysLeft <= 7 ? '⚠️ Segera restock!' : ($daysLeft <= 14 ? '📦 Pertimbangkan restock minggu ini' : '✅ Stok masih aman'),
        ]];
    }

    private function forecastRestockNeed(array $args): array
    {
        $baseDays = (int) ($args['base_days'] ?? 30);
        $aheadDays = (int) ($args['days_ahead'] ?? 30);

        $products = Product::where('tenant_id', $this->tenantId)
            ->where('is_active', true)
            ->withSum('stocks as current_stock', 'quantity')
            ->get();

        $needs = [];
        foreach ($products as $product) {
            $soldQty = SalesOrderItem::whereHas('salesOrder', fn ($q) => $q->where('tenant_id', $this->tenantId)
                ->whereNotIn('status', ['cancelled'])
                ->where('date', '>=', now()->subDays($baseDays))
            )->where('product_id', $product->id)->sum('quantity');

            $avgDaily = $baseDays > 0 ? $soldQty / $baseDays : 0;
            if ($avgDaily <= 0) {
                continue;
            }

            $needed = $avgDaily * $aheadDays;
            $current = $product->current_stock ?? 0;
            $toOrder = max(0, $needed - $current);

            if ($toOrder > 0) {
                $needs[] = [
                    'produk' => $product->name,
                    'stok_saat_ini' => $current.' '.$product->unit,
                    'kebutuhan' => round($needed, 0).' '.$product->unit,
                    'perlu_order' => round($toOrder, 0).' '.$product->unit,
                    'estimasi_biaya' => 'Rp '.number_format($toOrder * $product->price_buy, 0, ',', '.'),
                ];
            }
        }

        if (empty($needs)) {
            return ['status' => 'success', 'message' => "Stok semua produk cukup untuk {$aheadDays} hari ke depan.", 'data' => []];
        }

        usort($needs, fn ($a, $b) => $b['perlu_order'] <=> $a['perlu_order']);

        return ['status' => 'success', 'data' => [
            'periode_proyeksi' => "{$aheadDays} hari ke depan",
            'produk_perlu_restock' => count($needs),
            'detail' => array_slice($needs, 0, 15),
        ]];
    }

    // ─── Compare helpers ──────────────────────────────────────────

    private function compareSales($startA, $endA, $startB, $endB, $labelA, $labelB): array
    {
        $qA = SalesOrder::where('tenant_id', $this->tenantId)->whereNotIn('status', ['cancelled'])->whereBetween('date', [$startA, $endA]);
        $qB = SalesOrder::where('tenant_id', $this->tenantId)->whereNotIn('status', ['cancelled'])->whereBetween('date', [$startB, $endB]);

        $revA = $qA->sum('total');
        $ordA = $qA->count();
        $revB = $qB->sum('total');
        $ordB = $qB->count();

        $growth = $revB > 0 ? round((($revA - $revB) / $revB) * 100, 1) : null;

        return ['status' => 'success', 'data' => [
            'periode_a' => $this->periodLabel($labelA),
            'periode_b' => $this->periodLabel($labelB),
            'omzet_a' => 'Rp '.number_format($revA, 0, ',', '.'),
            'omzet_b' => 'Rp '.number_format($revB, 0, ',', '.'),
            'order_a' => $ordA.' order',
            'order_b' => $ordB.' order',
            'pertumbuhan_omzet' => $growth !== null ? ($growth >= 0 ? "+{$growth}%" : "{$growth}%") : 'N/A',
            'selisih_omzet' => 'Rp '.number_format(abs($revA - $revB), 0, ',', '.').($revA >= $revB ? ' lebih tinggi' : ' lebih rendah'),
        ]];
    }

    private function compareFinance($startA, $endA, $startB, $endB, $labelA, $labelB): array
    {
        $incA = Transaction::where('tenant_id', $this->tenantId)->where('type', 'income')->whereBetween('date', [$startA, $endA])->sum('amount');
        $expA = Transaction::where('tenant_id', $this->tenantId)->where('type', 'expense')->whereBetween('date', [$startA, $endA])->sum('amount');
        $incB = Transaction::where('tenant_id', $this->tenantId)->where('type', 'income')->whereBetween('date', [$startB, $endB])->sum('amount');
        $expB = Transaction::where('tenant_id', $this->tenantId)->where('type', 'expense')->whereBetween('date', [$startB, $endB])->sum('amount');

        return ['status' => 'success', 'data' => [
            'periode_a' => $this->periodLabel($labelA),
            'periode_b' => $this->periodLabel($labelB),
            'pemasukan_a' => 'Rp '.number_format($incA, 0, ',', '.'),
            'pemasukan_b' => 'Rp '.number_format($incB, 0, ',', '.'),
            'pengeluaran_a' => 'Rp '.number_format($expA, 0, ',', '.'),
            'pengeluaran_b' => 'Rp '.number_format($expB, 0, ',', '.'),
            'laba_a' => 'Rp '.number_format($incA - $expA, 0, ',', '.'),
            'laba_b' => 'Rp '.number_format($incB - $expB, 0, ',', '.'),
            'growth_pemasukan' => $incB > 0 ? round((($incA - $incB) / $incB) * 100, 1).'%' : 'N/A',
        ]];
    }

    private function compareProducts($startA, $endA, $startB, $endB, $labelA, $labelB): array
    {
        $getProducts = fn ($start, $end) => SalesOrderItem::whereHas('salesOrder', fn ($q) => $q->where('tenant_id', $this->tenantId)->whereNotIn('status', ['cancelled'])->whereBetween('date', [$start, $end])
        )->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->selectRaw('products.name, SUM(sales_order_items.quantity) as qty, SUM(sales_order_items.total) as revenue')
            ->groupBy('products.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get()
            ->keyBy('name');

        $prodA = $getProducts($startA, $endA);
        $prodB = $getProducts($startB, $endB);
        $allNames = $prodA->keys()->merge($prodB->keys())->unique();

        $comparison = $allNames->map(fn ($name) => [
            'produk' => $name,
            'omzet_a' => 'Rp '.number_format($prodA->get($name)?->revenue ?? 0, 0, ',', '.'),
            'omzet_b' => 'Rp '.number_format($prodB->get($name)?->revenue ?? 0, 0, ',', '.'),
            'qty_a' => $prodA->get($name)?->qty ?? 0,
            'qty_b' => $prodB->get($name)?->qty ?? 0,
            'growth' => ($prodB->get($name)?->revenue ?? 0) > 0
                ? round((($prodA->get($name)?->revenue ?? 0) - ($prodB->get($name)?->revenue ?? 0)) / ($prodB->get($name)?->revenue) * 100, 1).'%'
                : 'Baru',
        ])->sortByDesc(fn ($r) => (float) str_replace(['Rp ', '.', ','], '', $r['omzet_a']))->values()->toArray();

        return ['status' => 'success', 'data' => [
            'periode_a' => $this->periodLabel($labelA),
            'periode_b' => $this->periodLabel($labelB),
            'produk' => $comparison,
        ]];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function resolvePeriod(string $period): array
    {
        return match ($period) {
            'this_week' => [now()->startOfWeek(), now()->endOfWeek()],
            'last_week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'two_months_ago' => [now()->subMonths(2)->startOfMonth(), now()->subMonths(2)->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    private function periodLabel(string $period): string
    {
        return match ($period) {
            'this_week' => 'Minggu Ini',
            'last_week' => 'Minggu Lalu',
            'this_month' => 'Bulan Ini ('.now()->format('M Y').')',
            'last_month' => 'Bulan Lalu ('.now()->subMonth()->format('M Y').')',
            'two_months_ago' => now()->subMonths(2)->format('M Y'),
            'this_year' => 'Tahun Ini',
            default => $period,
        };
    }

    private function calcTrend(array $values): float
    {
        $n = count($values);
        if ($n < 2) {
            return 0;
        }
        $xMean = ($n - 1) / 2;
        $yMean = array_sum($values) / $n;
        $num = 0;
        $den = 0;
        foreach ($values as $i => $y) {
            $num += ($i - $xMean) * ($y - $yMean);
            $den += ($i - $xMean) ** 2;
        }

        return $den != 0 ? $num / $den : 0;
    }
}
