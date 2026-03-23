<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Collection;

/**
 * InventoryAiService — AI contextual untuk fitur Inventory.
 *
 * 1. predictStockout()     — prediksi kapan stok akan habis berdasarkan tren penjualan
 * 2. suggestReorderQty()   — suggest jumlah reorder berdasarkan pola penjualan
 * 3. analyzeAllProducts()  — analisis batch semua produk untuk tampilan tabel
 */
class InventoryAiService
{
    private int $lookbackDays = 90; // analisis 90 hari terakhir

    // ─── 1. Stockout Prediction ───────────────────────────────────

    /**
     * Prediksi kapan stok produk akan habis.
     *
     * Strategi:
     * - Hitung rata-rata penjualan harian dari movement 'out' (90 hari terakhir)
     * - Proyeksikan: stok_saat_ini / avg_daily_out = hari tersisa
     * - Tambahkan safety stock recommendation
     *
     * Return: [
     *   'current_stock'    => int,
     *   'avg_daily_out'    => float,
     *   'days_remaining'   => int|null,   // null = tidak bisa diprediksi
     *   'stockout_date'    => string|null,
     *   'urgency'          => 'critical'|'warning'|'ok'|'unknown',
     *   'message'          => string,
     *   'trend'            => 'increasing'|'stable'|'decreasing',
     * ]
     */
    public function predictStockout(int $tenantId, int $productId): array
    {
        $product = Product::where('tenant_id', $tenantId)->findOrFail($productId);
        $currentStock = $product->productStocks()->sum('quantity');

        $from = now()->subDays($this->lookbackDays)->toDateString();

        // Ambil semua movement 'out' dalam periode
        $outMovements = StockMovement::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('type', 'out')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(quantity) as total')
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        if ($outMovements->isEmpty()) {
            return [
                'current_stock'  => $currentStock,
                'avg_daily_out'  => 0,
                'days_remaining' => null,
                'stockout_date'  => null,
                'urgency'        => 'unknown',
                'trend'          => 'unknown',
                'message'        => 'Belum ada data penjualan untuk diprediksi.',
            ];
        }

        // Rata-rata harian (dibagi hari aktual, bukan total hari)
        $totalOut    = $outMovements->sum('total');
        $activeDays  = $outMovements->count();
        $avgDailyOut = $totalOut / max(1, $activeDays);

        // Trend: bandingkan 30 hari pertama vs 30 hari terakhir
        $trend = $this->calculateTrend($outMovements);

        // Proyeksi hari tersisa
        if ($avgDailyOut <= 0) {
            return [
                'current_stock'  => $currentStock,
                'avg_daily_out'  => 0,
                'days_remaining' => null,
                'stockout_date'  => null,
                'urgency'        => 'ok',
                'trend'          => $trend,
                'message'        => 'Tidak ada penjualan aktif. Stok tidak terpakai.',
            ];
        }

        $daysRemaining = (int) floor($currentStock / $avgDailyOut);
        $stockoutDate  = now()->addDays($daysRemaining)->format('d M Y');

        // Urgency berdasarkan hari tersisa
        $urgency = match (true) {
            $daysRemaining <= 7  => 'critical',
            $daysRemaining <= 21 => 'warning',
            default              => 'ok',
        };

        $avgFmt = number_format($avgDailyOut, 1);
        $message = match ($urgency) {
            'critical' => "Stok diperkirakan habis dalam {$daysRemaining} hari ({$stockoutDate}). Segera lakukan reorder!",
            'warning'  => "Stok diperkirakan habis sekitar {$stockoutDate} ({$daysRemaining} hari lagi). Pertimbangkan reorder.",
            default    => "Stok cukup untuk {$daysRemaining} hari ke depan (rata-rata keluar {$avgFmt}/hari).",
        };

        return [
            'current_stock'  => $currentStock,
            'avg_daily_out'  => round($avgDailyOut, 2),
            'days_remaining' => $daysRemaining,
            'stockout_date'  => $stockoutDate,
            'urgency'        => $urgency,
            'trend'          => $trend,
            'message'        => $message,
        ];
    }

    /**
     * Hitung tren penjualan: bandingkan paruh pertama vs paruh kedua periode.
     */
    private function calculateTrend(Collection $movements): string
    {
        if ($movements->count() < 4) return 'stable';

        $half    = (int) ceil($movements->count() / 2);
        $first   = $movements->take($half)->avg('total');
        $second  = $movements->skip($half)->avg('total');

        if ($second > $first * 1.15) return 'increasing';
        if ($second < $first * 0.85) return 'decreasing';
        return 'stable';
    }

    // ─── 2. Reorder Quantity Suggestion ──────────────────────────

    /**
     * Suggest jumlah reorder berdasarkan pola penjualan.
     *
     * Formula:
     * - Lead time default: 7 hari (waktu tunggu supplier)
     * - Safety stock: 1.5x rata-rata penjualan selama lead time
     * - Reorder qty: kebutuhan 30 hari + safety stock - stok saat ini
     *
     * Return: [
     *   'reorder_qty'       => int,
     *   'safety_stock'      => int,
     *   'lead_time_days'    => int,
     *   'cover_days'        => int,   // berapa hari stok yang disarankan
     *   'avg_monthly_out'   => float,
     *   'confidence'        => 'high'|'medium'|'low',
     *   'basis'             => string,
     *   'economic_order'    => int,   // EOQ sederhana
     * ]
     */
    public function suggestReorderQty(int $tenantId, int $productId, int $leadTimeDays = 7): array
    {
        $product      = Product::where('tenant_id', $tenantId)->findOrFail($productId);
        $currentStock = $product->productStocks()->sum('quantity');
        $from         = now()->subDays($this->lookbackDays)->toDateString();

        $outMovements = StockMovement::where('tenant_id', $tenantId)
            ->where('product_id', $productId)
            ->where('type', 'out')
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as day, SUM(quantity) as total')
            ->groupBy('day')
            ->get();

        if ($outMovements->isEmpty()) {
            return [
                'reorder_qty'     => max(10, $product->stock_min * 3),
                'safety_stock'    => $product->stock_min,
                'lead_time_days'  => $leadTimeDays,
                'cover_days'      => 30,
                'avg_monthly_out' => 0,
                'confidence'      => 'low',
                'basis'           => 'Tidak ada histori penjualan. Menggunakan 3x stok minimum.',
                'economic_order'  => max(10, $product->stock_min * 3),
            ];
        }

        $totalOut      = $outMovements->sum('total');
        $activeDays    = $outMovements->count();
        $avgDailyOut   = $totalOut / max(1, $activeDays);
        $avgMonthlyOut = $avgDailyOut * 30;

        // Safety stock = 1.5x kebutuhan selama lead time
        $safetyStock = (int) ceil($avgDailyOut * $leadTimeDays * 1.5);

        // Reorder point = kebutuhan lead time + safety stock
        $reorderPoint = (int) ceil($avgDailyOut * $leadTimeDays) + $safetyStock;

        // Reorder qty = kebutuhan 30 hari + safety stock - stok saat ini
        $coverDays  = 30;
        $reorderQty = max(1, (int) ceil($avgDailyOut * $coverDays) + $safetyStock - $currentStock);

        // EOQ sederhana (Economic Order Quantity)
        // EOQ = sqrt(2 * D * S / H) — pakai estimasi sederhana
        $annualDemand = $avgDailyOut * 365;
        $orderingCost = 50000; // estimasi biaya pesan (Rp 50rb)
        $holdingCost  = max(1, (float) $product->price_buy * 0.20 / 12); // 20% harga beli per tahun / 12
        $eoq = $holdingCost > 0
            ? (int) ceil(sqrt((2 * $annualDemand * $orderingCost) / $holdingCost))
            : $reorderQty;

        $confidence = match (true) {
            $activeDays >= 30 => 'high',
            $activeDays >= 10 => 'medium',
            default           => 'low',
        };

        $avgFmt = number_format($avgMonthlyOut, 0);
        return [
            'reorder_qty'     => $reorderQty,
            'safety_stock'    => $safetyStock,
            'lead_time_days'  => $leadTimeDays,
            'cover_days'      => $coverDays,
            'avg_monthly_out' => round($avgMonthlyOut, 1),
            'confidence'      => $confidence,
            'basis'           => "Rata-rata penjualan {$avgFmt} unit/bulan dari {$activeDays} hari data ({$this->lookbackDays} hari terakhir)",
            'economic_order'  => $eoq,
        ];
    }

    // ─── 3. Batch Analysis for Table ─────────────────────────────

    /**
     * Analisis semua produk sekaligus untuk ditampilkan di tabel inventory.
     * Lebih efisien karena query movement dilakukan sekali.
     *
     * Return: array keyed by product_id => ['urgency', 'days_remaining', 'reorder_qty']
     */
    public function analyzeAllProducts(int $tenantId, Collection $products): array
    {
        if ($products->isEmpty()) return [];

        $productIds = $products->pluck('id')->toArray();
        $from       = now()->subDays($this->lookbackDays)->toDateString();

        // Satu query untuk semua movement out
        $movements = StockMovement::where('tenant_id', $tenantId)
            ->whereIn('product_id', $productIds)
            ->where('type', 'out')
            ->where('created_at', '>=', $from)
            ->selectRaw('product_id, DATE(created_at) as day, SUM(quantity) as total')
            ->groupBy('product_id', 'day')
            ->get()
            ->groupBy('product_id');

        // Stok per produk
        $stocks = \App\Models\ProductStock::whereIn('product_id', $productIds)
            ->selectRaw('product_id, SUM(quantity) as total')
            ->groupBy('product_id')
            ->pluck('total', 'product_id');

        $results = [];

        foreach ($products as $product) {
            $currentStock = (int) ($stocks[$product->id] ?? 0);
            $productMovs  = $movements->get($product->id, collect());

            if ($productMovs->isEmpty()) {
                $results[$product->id] = [
                    'urgency'       => 'unknown',
                    'days_remaining'=> null,
                    'reorder_qty'   => null,
                    'avg_daily_out' => 0,
                ];
                continue;
            }

            $totalOut    = $productMovs->sum('total');
            $activeDays  = $productMovs->count();
            $avgDailyOut = $totalOut / max(1, $activeDays);

            if ($avgDailyOut <= 0) {
                $results[$product->id] = [
                    'urgency'       => 'ok',
                    'days_remaining'=> null,
                    'reorder_qty'   => null,
                    'avg_daily_out' => 0,
                ];
                continue;
            }

            $daysRemaining = (int) floor($currentStock / $avgDailyOut);
            $safetyStock   = (int) ceil($avgDailyOut * 7 * 1.5);
            $reorderQty    = max(1, (int) ceil($avgDailyOut * 30) + $safetyStock - $currentStock);

            $urgency = match (true) {
                $daysRemaining <= 7  => 'critical',
                $daysRemaining <= 21 => 'warning',
                default              => 'ok',
            };

            $results[$product->id] = [
                'urgency'        => $urgency,
                'days_remaining' => $daysRemaining,
                'reorder_qty'    => max(0, $reorderQty),
                'avg_daily_out'  => round($avgDailyOut, 2),
            ];
        }

        return $results;
    }
}
