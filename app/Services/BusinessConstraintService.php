<?php

namespace App\Services;

use App\Models\BusinessConstraint;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * BusinessConstraintService — Task 45
 *
 * Validasi rule bisnis yang bisa dikonfigurasi per tenant.
 * Semua method check* melempar \RuntimeException jika constraint dilanggar.
 * Gunakan try/catch di controller untuk menangkap dan menampilkan pesan ke user.
 */
class BusinessConstraintService
{
    private array $cache = [];

    /** Ambil nilai constraint (dengan cache per request) */
    public function get(int $tenantId, string $key): mixed
    {
        $cacheKey = "{$tenantId}:{$key}";
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        $constraint = BusinessConstraint::where('tenant_id', $tenantId)
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        $value = $constraint ? $constraint->typedValue() : null;
        $this->cache[$cacheKey] = $value;
        return $value;
    }

    /**
     * Cek apakah harga jual di bawah HPP.
     * Lempar exception jika constraint aktif dan harga < cost.
     */
    public function checkSellPrice(int $tenantId, int $productId, float $price, float $qty = 1): void
    {
        if (! $this->get($tenantId, 'no_sell_below_cost')) return;

        $cost = Product::where('id', $productId)
            ->where('tenant_id', $tenantId)
            ->value('price_buy') ?? 0;

        if ($cost > 0 && $price < $cost) {
            $product = Product::find($productId);
            throw new \RuntimeException(
                "Harga jual Rp " . number_format($price, 0, ',', '.') .
                " untuk produk \"{$product->name}\" di bawah HPP (Rp " . number_format($cost, 0, ',', '.') . ")."
            );
        }
    }

    /**
     * Cek apakah diskon melebihi batas maksimal.
     */
    public function checkDiscount(int $tenantId, float $discountPct): void
    {
        $max = (float) ($this->get($tenantId, 'max_discount_pct') ?? 0);
        if ($max <= 0) return;

        if ($discountPct > $max) {
            throw new \RuntimeException(
                "Diskon {$discountPct}% melebihi batas maksimal yang diizinkan ({$max}%)."
            );
        }
    }

    /**
     * Cek apakah transaksi pengeluaran kas akan membuat saldo di bawah minimum.
     */
    public function checkCashBalance(int $tenantId, float $outgoingAmount): void
    {
        $minBalance = (float) ($this->get($tenantId, 'min_cash_balance') ?? 0);
        if ($minBalance <= 0) return;

        // Hitung saldo kas saat ini dari GL (akun 1101 + 1102)
        $cashBalance = $this->getCurrentCashBalance($tenantId);

        if (($cashBalance - $outgoingAmount) < $minBalance) {
            throw new \RuntimeException(
                "Transaksi ini akan membuat saldo kas menjadi Rp " .
                number_format($cashBalance - $outgoingAmount, 0, ',', '.') .
                ", di bawah minimum Rp " . number_format($minBalance, 0, ',', '.') . "."
            );
        }
    }

    /**
     * Cek apakah transaksi memerlukan konfirmasi (nominal di atas threshold).
     * Return true jika perlu konfirmasi, false jika tidak.
     */
    public function requiresConfirmation(int $tenantId, float $amount): bool
    {
        $threshold = (float) ($this->get($tenantId, 'confirm_above_amount') ?? 0);
        if ($threshold <= 0) return false;
        return $amount > $threshold;
    }

    /**
     * Cek apakah cost center wajib diisi.
     */
    public function checkCostCenterRequired(int $tenantId, ?int $costCenterId): void
    {
        if (! $this->get($tenantId, 'require_cost_center')) return;

        if (empty($costCenterId)) {
            throw new \RuntimeException('Cost Center wajib dipilih untuk setiap transaksi.');
        }
    }

    /**
     * Cek apakah stok negatif diizinkan.
     * Lempar exception jika tidak diizinkan dan stok akan negatif.
     */
    public function checkStockNegative(int $tenantId, string $productName, float $currentStock, float $requestedQty): void
    {
        if ($this->get($tenantId, 'allow_negative_stock')) return;

        if ($currentStock < $requestedQty) {
            throw new \RuntimeException(
                "Stok \"{$productName}\" tidak cukup. Tersedia: {$currentStock}, diminta: {$requestedQty}."
            );
        }
    }

    /**
     * Validasi semua constraint untuk transaksi penjualan sekaligus.
     * Lempar exception pertama yang ditemukan.
     */
    public function validateSaleTransaction(
        int    $tenantId,
        float  $total,
        float  $discountPct = 0,
        ?int   $costCenterId = null,
        array  $items = []  // [['product_id' => ..., 'price' => ..., 'qty' => ...], ...]
    ): void {
        // Cek diskon
        $this->checkDiscount($tenantId, $discountPct);

        // Cek cost center
        $this->checkCostCenterRequired($tenantId, $costCenterId);

        // Cek harga per item
        foreach ($items as $item) {
            if (!empty($item['product_id']) && !empty($item['price'])) {
                $this->checkSellPrice($tenantId, $item['product_id'], $item['price'], $item['qty'] ?? 1);
            }
        }
    }

    /**
     * Validasi constraint untuk transaksi pengeluaran kas.
     */
    public function validateCashOutflow(int $tenantId, float $amount, ?int $costCenterId = null): void
    {
        $this->checkCashBalance($tenantId, $amount);
        $this->checkCostCenterRequired($tenantId, $costCenterId);
    }

    /**
     * Validasi constraint untuk transaksi pembelian.
     * Cek cost center required dan min_cash_balance, tapi TIDAK cek sell-price.
     */
    public function validatePurchaseTransaction(int $tenantId, float $total, ?int $costCenterId): void
    {
        $this->checkCostCenterRequired($tenantId, $costCenterId);
        $this->checkCashBalance($tenantId, $total);
    }

    /**
     * Invalidate in-memory cache constraint untuk tenant tertentu.
     * Dipanggil setelah bulk update agar nilai terbaru langsung digunakan.
     */
    public function invalidateCache(int $tenantId): void
    {
        foreach (array_keys($this->cache) as $key) {
            if (str_starts_with($key, "{$tenantId}:")) {
                unset($this->cache[$key]);
            }
        }
    }

    /** Hitung saldo kas saat ini dari GL */
    private function getCurrentCashBalance(int $tenantId): float
    {
        $cashAccountIds = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', ['1101', '1102'])
            ->pluck('id');

        if ($cashAccountIds->isEmpty()) return 0;

        $debit  = JournalEntryLine::whereIn('account_id', $cashAccountIds)
            ->whereHas('journalEntry', fn($q) => $q->where('tenant_id', $tenantId)->where('status', 'posted'))
            ->sum('debit');

        $credit = JournalEntryLine::whereIn('account_id', $cashAccountIds)
            ->whereHas('journalEntry', fn($q) => $q->where('tenant_id', $tenantId)->where('status', 'posted'))
            ->sum('credit');

        return (float) $debit - (float) $credit;
    }
}
