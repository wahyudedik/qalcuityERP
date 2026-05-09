<?php

namespace App\Services;

use App\Enums\AiUseCase;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SalesOrderItem;

/**
 * SalesAiService — AI contextual untuk fitur Invoice & Sales Order.
 *
 * 1. suggestPrice()         — suggest harga berdasarkan histori transaksi customer
 * 2. predictLatePayment()   — prediksi kemungkinan invoice telat dibayar
 * 3. draftItemDescription() — auto-draft deskripsi item dari nama produk
 *
 * Use Cases:
 * - draftItemDescription() uses AiUseCase::CRUD_AI
 * - predictLatePayment() and suggestPrice() use AiUseCase::FORECASTING
 */
class SalesAiService
{
    // ─── 1. Price Suggestion ──────────────────────────────────────

    /**
     * Suggest harga untuk produk berdasarkan histori transaksi customer.
     *
     * Use Case: AiUseCase::FORECASTING
     * When AI provider is integrated, pass: AiUseCase::FORECASTING->value
     *
     * Return:
     * [
     *   'suggested_price'  => float,
     *   'confidence'       => 'high'|'medium'|'low',
     *   'basis'            => string,   // penjelasan dasar saran
     *   'history_count'    => int,
     *   'avg_price'        => float,
     *   'min_price'        => float,
     *   'max_price'        => float,
     *   'last_price'       => float|null,
     *   'default_price'    => float,
     * ]
     *
     * Requirements: 8.4
     */
    public function suggestPrice(int $tenantId, int $customerId, int $productId, float $qty = 1): array
    {
        $product = Product::find($productId);
        $defaultPrice = $product ? (float) $product->price_sell : 0;

        // Ambil histori harga customer untuk produk ini (12 bulan terakhir)
        $history = SalesOrderItem::query()
            ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
            ->where('sales_orders.tenant_id', $tenantId)
            ->where('sales_orders.customer_id', $customerId)
            ->where('sales_order_items.product_id', $productId)
            ->whereNotIn('sales_orders.status', ['cancelled'])
            ->where('sales_orders.date', '>=', now()->subMonths(12)->toDateString())
            ->orderByDesc('sales_orders.date')
            ->select([
                'sales_order_items.price',
                'sales_order_items.quantity',
                'sales_orders.date',
            ])
            ->limit(20)
            ->get();

        if ($history->isEmpty()) {
            // Cek histori semua customer untuk produk ini (fallback)
            $allHistory = SalesOrderItem::query()
                ->join('sales_orders', 'sales_order_items.sales_order_id', '=', 'sales_orders.id')
                ->where('sales_orders.tenant_id', $tenantId)
                ->where('sales_order_items.product_id', $productId)
                ->whereNotIn('sales_orders.status', ['cancelled'])
                ->where('sales_orders.date', '>=', now()->subMonths(3)->toDateString())
                ->select('sales_order_items.price')
                ->limit(10)
                ->pluck('price');

            if ($allHistory->isEmpty()) {
                return [
                    'suggested_price' => $defaultPrice,
                    'confidence' => 'low',
                    'basis' => 'Harga default produk (belum ada histori transaksi)',
                    'history_count' => 0,
                    'avg_price' => $defaultPrice,
                    'min_price' => $defaultPrice,
                    'max_price' => $defaultPrice,
                    'last_price' => null,
                    'default_price' => $defaultPrice,
                ];
            }

            $avg = round($allHistory->avg());

            return [
                'suggested_price' => $avg,
                'confidence' => 'low',
                'basis' => 'Rata-rata harga ke semua customer (belum ada histori khusus customer ini)',
                'history_count' => $allHistory->count(),
                'avg_price' => $avg,
                'min_price' => round($allHistory->min()),
                'max_price' => round($allHistory->max()),
                'last_price' => null,
                'default_price' => $defaultPrice,
            ];
        }

        $prices = $history->pluck('price')->map(fn ($p) => (float) $p);
        $avg = round($prices->avg());
        $lastPrice = (float) $history->first()->price;
        $count = $history->count();

        // Weighted average: transaksi terbaru lebih berpengaruh
        $weighted = $this->weightedAverage($history->toArray());

        // Confidence berdasarkan jumlah histori
        $confidence = match (true) {
            $count >= 5 => 'high',
            $count >= 2 => 'medium',
            default => 'low',
        };

        // Basis penjelasan
        $customer = Customer::find($customerId);
        $customerName = $customer?->name ?? 'customer ini';
        $basis = match ($confidence) {
            'high' => "Berdasarkan {$count} transaksi terakhir dengan {$customerName}. Harga terakhir: Rp ".number_format($lastPrice, 0, ',', '.'),
            'medium' => "Berdasarkan {$count} transaksi dengan {$customerName}",
            default => "Berdasarkan 1 transaksi sebelumnya dengan {$customerName}",
        };

        return [
            'suggested_price' => $weighted,
            'confidence' => $confidence,
            'basis' => $basis,
            'history_count' => $count,
            'avg_price' => $avg,
            'min_price' => round($prices->min()),
            'max_price' => round($prices->max()),
            'last_price' => $lastPrice,
            'default_price' => $defaultPrice,
        ];
    }

    // ─── 2. Late Payment Prediction ───────────────────────────────

    /**
     * Prediksi kemungkinan invoice telat dibayar berdasarkan pola customer.
     *
     * Use Case: AiUseCase::FORECASTING
     * When AI provider is integrated, pass: AiUseCase::FORECASTING->value
     *
     * Return:
     * [
     *   'risk'          => 'high'|'medium'|'low',
     *   'probability'   => float,   // 0-100
     *   'reason'        => string,
     *   'avg_days_late' => float,
     *   'late_count'    => int,
     *   'total_invoices'=> int,
     *   'tips'          => string[],
     * ]
     *
     * Requirements: 8.4
     */
    public function predictLatePayment(int $tenantId, int $customerId): array
    {
        // Ambil histori invoice customer yang sudah selesai (paid)
        $paidInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->where('status', 'paid')
            ->whereNotNull('due_date')
            ->with('payments')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $totalPaid = $paidInvoices->count();

        // Invoice yang masih outstanding
        $outstanding = Invoice::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->count();

        // Invoice overdue saat ini
        $currentOverdue = Invoice::where('tenant_id', $tenantId)
            ->where('customer_id', $customerId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('due_date', '<', today())
            ->count();

        if ($totalPaid === 0 && $outstanding === 0) {
            return [
                'risk' => 'low',
                'probability' => 10,
                'reason' => 'Belum ada histori invoice untuk customer ini',
                'avg_days_late' => 0,
                'late_count' => 0,
                'total_invoices' => 0,
                'tips' => ['Pertimbangkan meminta DP untuk customer baru'],
            ];
        }

        // Hitung keterlambatan dari histori paid invoices
        $lateDays = [];
        $lateCount = 0;

        foreach ($paidInvoices as $inv) {
            // Ambil tanggal pembayaran terakhir
            $lastPayment = $inv->payments()->orderByDesc('payment_date')->first();
            if (! $lastPayment) {
                continue;
            }

            $daysLate = $inv->due_date->diffInDays($lastPayment->payment_date, false);
            if ($daysLate > 0) {
                $lateDays[] = $daysLate;
                $lateCount++;
            }
        }

        $lateRate = $totalPaid > 0 ? ($lateCount / $totalPaid) * 100 : 0;
        $avgDaysLate = ! empty($lateDays) ? round(array_sum($lateDays) / count($lateDays), 1) : 0;

        // Faktor tambahan: ada invoice overdue sekarang
        $overdueBonus = $currentOverdue > 0 ? 20 : 0;

        // Hitung probability
        $probability = min(95, round($lateRate * 0.7 + $overdueBonus + ($avgDaysLate > 30 ? 15 : 0)));

        // Risk level
        $risk = match (true) {
            $probability >= 60 => 'high',
            $probability >= 30 => 'medium',
            default => 'low',
        };

        // Reason
        $customer = Customer::find($customerId);
        $name = $customer?->name ?? 'Customer';

        if ($lateCount === 0 && $currentOverdue === 0) {
            $reason = "{$name} memiliki rekam jejak pembayaran yang baik ({$totalPaid} invoice tepat waktu)";
        } elseif ($currentOverdue > 0) {
            $reason = "{$name} saat ini memiliki {$currentOverdue} invoice overdue";
        } else {
            $reason = "{$name} terlambat membayar {$lateCount} dari {$totalPaid} invoice (rata-rata {$avgDaysLate} hari terlambat)";
        }

        // Tips
        $tips = $this->buildPaymentTips($risk, $avgDaysLate, $currentOverdue, $outstanding);

        return [
            'risk' => $risk,
            'probability' => $probability,
            'reason' => $reason,
            'avg_days_late' => $avgDaysLate,
            'late_count' => $lateCount,
            'total_invoices' => $totalPaid,
            'tips' => $tips,
        ];
    }

    // ─── 3. Auto-Draft Item Description ──────────────────────────

    /**
     * Generate deskripsi item dari nama produk + konteks.
     * Menggunakan template berbasis kategori produk (tanpa API call).
     *
     * Use Case: AiUseCase::CRUD_AI
     * When AI provider is integrated, pass: AiUseCase::CRUD_AI->value
     *
     * Requirements: 8.4
     */
    public function draftItemDescription(int $tenantId, int $productId): array
    {
        $product = Product::find($productId);
        if (! $product) {
            return ['description' => '', 'source' => 'none'];
        }

        // Jika produk sudah punya deskripsi, gunakan itu
        if (! empty($product->description)) {
            return [
                'description' => $product->description,
                'source' => 'product',
            ];
        }

        // Generate dari nama + kategori + unit
        $desc = $this->generateDescription($product);

        return [
            'description' => $desc,
            'source' => 'generated',
            'product_name' => $product->name,
            'category' => $product->category,
            'unit' => $product->unit,
            'sku' => $product->sku,
        ];
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function weightedAverage(array $history): float
    {
        if (empty($history)) {
            return 0;
        }

        $totalWeight = 0;
        $weightedSum = 0;
        $n = count($history);

        foreach ($history as $i => $item) {
            // Transaksi terbaru (index 0) dapat bobot tertinggi
            $weight = $n - $i;
            $weightedSum += (float) $item['price'] * $weight;
            $totalWeight += $weight;
        }

        return $totalWeight > 0 ? round($weightedSum / $totalWeight) : 0;
    }

    private function buildPaymentTips(string $risk, float $avgDaysLate, int $currentOverdue, int $outstanding): array
    {
        $tips = [];

        if ($risk === 'high') {
            $tips[] = 'Pertimbangkan meminta uang muka (DP) sebelum memproses order';
            $tips[] = 'Set jatuh tempo lebih pendek dari biasanya';
            if ($currentOverdue > 0) {
                $tips[] = 'Selesaikan invoice overdue yang ada sebelum membuat invoice baru';
            }
        } elseif ($risk === 'medium') {
            $tips[] = 'Kirim reminder pembayaran 3 hari sebelum jatuh tempo';
            if ($avgDaysLate > 14) {
                $tips[] = 'Pertimbangkan menambah 7-14 hari buffer ke jatuh tempo';
            }
        } else {
            $tips[] = 'Customer ini memiliki rekam jejak pembayaran yang baik';
        }

        return $tips;
    }

    private function generateDescription(Product $product): string
    {
        $name = $product->name;
        $unit = $product->unit ?? 'unit';
        $category = strtolower($product->category ?? '');
        $sku = $product->sku ? " (SKU: {$product->sku})" : '';

        // Template berdasarkan kategori
        $templates = [
            'elektronik' => "{$name} — perangkat elektronik berkualitas. Satuan: {$unit}{$sku}.",
            'makanan' => "{$name} — produk makanan/minuman. Satuan: {$unit}{$sku}.",
            'minuman' => "{$name} — produk minuman. Satuan: {$unit}{$sku}.",
            'pakaian' => "{$name} — produk fashion/pakaian. Satuan: {$unit}{$sku}.",
            'kesehatan' => "{$name} — produk kesehatan & kebersihan. Satuan: {$unit}{$sku}.",
            'otomotif' => "{$name} — suku cadang/aksesori otomotif. Satuan: {$unit}{$sku}.",
            'furniture' => "{$name} — produk furnitur & dekorasi. Satuan: {$unit}{$sku}.",
            'alat' => "{$name} — peralatan & perlengkapan. Satuan: {$unit}{$sku}.",
            'bahan' => "{$name} — bahan baku/material. Satuan: {$unit}{$sku}.",
            'jasa' => "Layanan {$name}. Satuan: {$unit}{$sku}.",
            'service' => "Layanan {$name}. Satuan: {$unit}{$sku}.",
        ];

        foreach ($templates as $key => $template) {
            if (str_contains($category, $key)) {
                return $template;
            }
        }

        // Default template
        return "{$name}. Satuan: {$unit}{$sku}.";
    }
}
