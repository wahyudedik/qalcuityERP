<?php

namespace App\Services;

use App\Models\AnomalyAlert;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * AnomalyDetectionService — Task 51
 * Deteksi anomali otomatis: transaksi tidak wajar, pola fraud, stok tidak cocok, dll.
 */
class AnomalyDetectionService
{
    /**
     * Jalankan semua detektor dan simpan anomali baru ke DB.
     * Return jumlah anomali baru yang ditemukan.
     */
    public function detectAndSave(int $tenantId): int
    {
        $anomalies = array_merge(
            $this->detectUnusualTransactions($tenantId),
            $this->detectUnbalancedJournals($tenantId),
            $this->detectDuplicateTransactions($tenantId),
            $this->detectFraudPatterns($tenantId),
            $this->detectPriceAnomalies($tenantId),
            $this->detectStockMismatch($tenantId),
        );

        $saved = 0;
        foreach ($anomalies as $anomaly) {
            // Hindari duplikat: cek apakah anomali tipe+hash yang sama sudah ada hari ini
            $hash = md5($anomaly['type'].json_encode($anomaly['data'] ?? []));
            $exists = AnomalyAlert::where('tenant_id', $tenantId)
                ->where('type', $anomaly['type'])
                ->whereDate('created_at', today())
                ->where('status', 'open')
                ->exists();

            if ($exists) {
                continue;
            }

            AnomalyAlert::create(array_merge($anomaly, ['tenant_id' => $tenantId]));
            $saved++;
        }

        return $saved;
    }

    /**
     * Jalankan semua detektor dan return array anomali (tanpa simpan ke DB).
     * Dipakai oleh AiInsightService.
     */
    public function detect(int $tenantId): array
    {
        return array_merge(
            $this->detectUnusualTransactions($tenantId),
            $this->detectUnbalancedJournals($tenantId),
            $this->detectDuplicateTransactions($tenantId),
            $this->detectFraudPatterns($tenantId),
            $this->detectPriceAnomalies($tenantId),
            $this->detectStockMismatch($tenantId),
        );
    }

    // ─── Detectors ────────────────────────────────────────────────

    /**
     * Transaksi > 3x rata-rata 30 hari terakhir.
     */
    private function detectUnusualTransactions(int $tenantId): array
    {
        $avg = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereBetween('date', [now()->subDays(30)->toDateString(), now()->subDays(1)->toDateString()])
            ->avg('amount') ?? 0;

        if ($avg <= 0) {
            return [];
        }

        $threshold = $avg * 3;

        $unusual = Transaction::where('tenant_id', $tenantId)
            ->where('type', 'expense')
            ->whereDate('date', today())
            ->where('amount', '>', $threshold)
            ->with('category')
            ->get();

        if ($unusual->isEmpty()) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return $unusual->map(fn ($t) => [
            'type' => 'unusual_transaction',
            'severity' => 'warning',
            'title' => '⚠️ Transaksi Tidak Wajar Terdeteksi',
            'description' => sprintf(
                'Transaksi %s senilai %s melebihi 3x rata-rata harian (%s). Kategori: %s.',
                $t->reference ?? "#{$t->id}",
                $fmt($t->amount),
                $fmt($avg),
                $t->category?->name ?? 'Tidak ada kategori'
            ),
            'data' => ['transaction_id' => $t->id, 'amount' => $t->amount, 'avg' => $avg],
        ])->toArray();
    }

    /**
     * Jurnal draft lebih dari 7 hari (belum di-post).
     */
    private function detectUnbalancedJournals(int $tenantId): array
    {
        $count = DB::table('journal_entries')
            ->where('tenant_id', $tenantId)
            ->where('status', 'draft')
            ->where('date', '<', now()->subDays(7)->toDateString())
            ->count();

        if ($count === 0) {
            return [];
        }

        return [[
            'type' => 'unbalanced_journal',
            'severity' => 'warning',
            'title' => "📋 {$count} Jurnal Draft Belum Di-post",
            'description' => "{$count} jurnal masih berstatus draft lebih dari 7 hari. Segera review dan posting untuk menjaga akurasi laporan keuangan.",
            'data' => ['count' => $count],
        ]];
    }

    /**
     * Transaksi duplikat: nominal + pihak yang sama dalam 24 jam.
     */
    private function detectDuplicateTransactions(int $tenantId): array
    {
        $duplicates = DB::table('transactions as t1')
            ->join('transactions as t2', function ($join) {
                $join->on('t1.amount', '=', 't2.amount')
                    ->on('t1.id', '<', 't2.id')
                    ->whereRaw('ABS(TIMESTAMPDIFF(HOUR, t1.created_at, t2.created_at)) <= 24');
            })
            ->where('t1.tenant_id', $tenantId)
            ->where('t2.tenant_id', $tenantId)
            ->whereDate('t1.created_at', '>=', now()->subDays(1)->toDateString())
            ->select('t1.id as id1', 't2.id as id2', 't1.amount', 't1.description')
            ->limit(5)
            ->get();

        if ($duplicates->isEmpty()) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return [[
            'type' => 'duplicate_transaction',
            'severity' => 'warning',
            'title' => '🔁 Kemungkinan Transaksi Duplikat',
            'description' => sprintf(
                '%d pasang transaksi dengan nominal sama ditemukan dalam 24 jam terakhir. Contoh: %s senilai %s.',
                $duplicates->count(),
                $duplicates->first()->description ?? "Transaksi #{$duplicates->first()->id1}",
                $fmt($duplicates->first()->amount)
            ),
            'data' => ['pairs' => $duplicates->count(), 'sample' => $duplicates->first()],
        ]];
    }

    /**
     * Pola fraud: transaksi angka bulat besar di luar jam kerja (sebelum 07:00 atau setelah 22:00).
     */
    private function detectFraudPatterns(int $tenantId): array
    {
        $suspicious = Transaction::where('tenant_id', $tenantId)
            ->where('amount', '>=', 1_000_000)
            ->whereRaw('amount % 1000000 = 0') // angka bulat jutaan
            ->whereRaw('HOUR(created_at) NOT BETWEEN 7 AND 21') // di luar jam kerja
            ->whereDate('created_at', '>=', now()->subDays(7)->toDateString())
            ->count();

        if ($suspicious === 0) {
            return [];
        }

        return [[
            'type' => 'fraud_pattern',
            'severity' => 'critical',
            'title' => '🚨 Pola Transaksi Mencurigakan',
            'description' => "{$suspicious} transaksi dengan nominal bulat besar dilakukan di luar jam kerja (sebelum 07:00 atau setelah 22:00) dalam 7 hari terakhir. Segera verifikasi.",
            'data' => ['count' => $suspicious],
        ]];
    }

    /**
     * Harga jual di bawah HPP (cost_price).
     */
    private function detectPriceAnomalies(int $tenantId): array
    {
        $belowCost = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->join('products', 'sales_order_items.product_id', '=', 'products.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereRaw('sales_order_items.price < products.price_buy AND products.price_buy > 0')
            ->whereBetween('sales_orders.date', [now()->subDays(7)->toDateString(), now()->toDateString()])
            ->selectRaw('products.name, COUNT(*) as cnt, MIN(sales_order_items.price) as min_price, products.price_buy')
            ->groupBy('products.id', 'products.name', 'products.price_buy')
            ->limit(5)
            ->get();

        if ($belowCost->isEmpty()) {
            return [];
        }

        $fmt = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');

        return [[
            'type' => 'price_anomaly',
            'severity' => 'warning',
            'title' => "💸 {$belowCost->count()} Produk Dijual di Bawah HPP",
            'description' => sprintf(
                'Ditemukan penjualan di bawah harga pokok dalam 7 hari terakhir. Contoh: %s dijual %s (HPP: %s).',
                $belowCost->first()->name,
                $fmt($belowCost->first()->min_price),
                $fmt($belowCost->first()->price_buy)
            ),
            'data' => ['products' => $belowCost->toArray()],
        ]];
    }

    /**
     * Stok tidak cocok: pergerakan stok tidak sesuai dengan penjualan.
     */
    private function detectStockMismatch(int $tenantId): array
    {
        // Hitung total penjualan per produk 7 hari terakhir
        $sold = DB::table('sales_order_items')
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.status', '!=', 'cancelled')
            ->whereBetween('sales_orders.date', [now()->subDays(7)->toDateString(), now()->toDateString()])
            ->selectRaw('sales_order_items.product_id, SUM(sales_order_items.quantity) as sold_qty')
            ->groupBy('sales_order_items.product_id')
            ->pluck('sold_qty', 'product_id');

        if ($sold->isEmpty()) {
            return [];
        }

        // Hitung pergerakan stok keluar dari stock_movements
        $moved = DB::table('stock_movements')
            ->where('tenant_id', $tenantId)
            ->where('type', 'out')
            ->whereBetween('created_at', [now()->subDays(7)->startOfDay(), now()])
            ->whereIn('product_id', $sold->keys())
            ->selectRaw('product_id, SUM(quantity) as moved_qty')
            ->groupBy('product_id')
            ->pluck('moved_qty', 'product_id');

        $mismatches = [];
        foreach ($sold as $productId => $soldQty) {
            $movedQty = $moved[$productId] ?? 0;
            $diff = abs($soldQty - $movedQty);
            // Mismatch signifikan jika selisih > 10% dari qty terjual
            if ($soldQty > 0 && $diff / $soldQty > 0.1) {
                $product = Product::find($productId);
                if ($product) {
                    $mismatches[] = [
                        'product' => $product->name,
                        'sold' => $soldQty,
                        'moved' => $movedQty,
                        'diff' => $diff,
                    ];
                }
            }
        }

        if (empty($mismatches)) {
            return [];
        }

        return [[
            'type' => 'stock_mismatch',
            'severity' => 'warning',
            'title' => count($mismatches).' Produk: Stok Tidak Cocok dengan Penjualan',
            'description' => sprintf(
                'Pergerakan stok tidak sesuai dengan data penjualan 7 hari terakhir. Contoh: %s terjual %d unit tapi stok keluar hanya %d unit.',
                $mismatches[0]['product'],
                $mismatches[0]['sold'],
                $mismatches[0]['moved']
            ),
            'data' => ['mismatches' => array_slice($mismatches, 0, 5)],
        ]];
    }
}
