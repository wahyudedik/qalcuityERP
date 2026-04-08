<?php

namespace App\Services;

use App\Models\TaxRate;

/**
 * TaxService - Perhitungan pajak untuk invoice dan transaksi
 * 
 * BUG-SALES-003 FIX: Menggunakan pembulatan yang tepat untuk accounting
 * - Round HALF_UP (bukan PHP default HALF_EVEN)
 * - Consistent rounding di semua perhitungan
 * - Avoid floating point errors
 */
class TaxService
{
    /**
     * Hitung PPh withholding dari gross amount.
     * Untuk PPh 23: rate 2% dari bruto
     * Untuk PPh 21: rate sesuai konfigurasi
     * Untuk PPh 4 ayat 2: rate sesuai konfigurasi
     * 
     * BUG-SALES-003 FIX: Menggunakan pembulatan yang tepat
     */
    public function calculatePph(float $grossAmount, string $taxType, int $tenantId): array
    {
        $taxRate = TaxRate::where('tenant_id', $tenantId)
            ->where('tax_type', $taxType)
            ->where('is_active', true)
            ->where('is_withholding', true)
            ->first();

        if (!$taxRate) {
            return ['amount' => 0, 'rate' => 0, 'tax_type' => $taxType];
        }

        // BUG-SALES-003 FIX: Round to 2 decimals dengan HALF_UP
        $amount = $this->roundAccounting($grossAmount * ($taxRate->rate / 100));

        return [
            'amount' => $amount,
            'rate' => $taxRate->rate,
            'tax_type' => $taxType,
            'label' => $taxRate->getTypeLabel(),
        ];
    }

    /**
     * Hitung PPN dari subtotal.
     * 
     * BUG-SALES-003 FIX: Menggunakan pembulatan yang tepat
     */
    public function calculatePpn(float $subtotal, int $tenantId): array
    {
        $taxRate = TaxRate::where('tenant_id', $tenantId)
            ->where('tax_type', 'ppn')
            ->where('is_active', true)
            ->first();

        if (!$taxRate) {
            return ['amount' => 0, 'rate' => 0];
        }

        // BUG-SALES-003 FIX: Round to 2 decimals dengan HALF_UP
        return [
            'amount' => $this->roundAccounting($subtotal * ($taxRate->rate / 100)),
            'rate' => $taxRate->rate,
        ];
    }

    /**
     * Hitung tax amount dari subtotal berdasarkan tax_rate_id.
     * 
     * BUG-SALES-003 FIX: Menggunakan pembulatan yang tepat
     */
    public function calculate(float $subtotal, int $taxRateId): float
    {
        $taxRate = TaxRate::find($taxRateId);
        if (!$taxRate || !$taxRate->is_active) {
            return 0.0;
        }

        // BUG-SALES-003 FIX: Round to 2 decimals dengan HALF_UP
        return $this->roundAccounting($subtotal * ($taxRate->rate / 100));
    }

    /**
     * Hitung tax dari subtotal + rate langsung (tanpa lookup DB).
     * 
     * BUG-SALES-003 FIX: Menggunakan pembulatan yang tepat
     */
    public function calculateByRate(float $subtotal, float $rate): float
    {
        // BUG-SALES-003 FIX: Round to 2 decimals dengan HALF_UP
        return $this->roundAccounting($subtotal * ($rate / 100));
    }

    /**
     * BUG-SALES-003 FIX: Accounting-compliant rounding
     * 
     * Menggunakan ROUND_HALF_UP (bukan PHP default ROUND_HALF_EVEN)
     * untuk menghindari rounding errors di perhitungan accounting.
     * 
     * Contoh masalah dengan round() biasa:
     * - 2.5 → 2 (HALF_EVEN - banker's rounding)
     * - 2.5 → 3 (HALF_UP - accounting standard)
     * 
     * Untuk accounting, kita HARUS menggunakan HALF_UP agar:
     * - Consistent dengan perhitungan manual
     * - Tidak ada selisih 1-2 rupiah di laporan keuangan
     * - Match dengan invoice/receipt yang dicetak
     * 
     * @param float $value Nilai yang akan dibulatkan
     * @param int $precision Jumlah desimal (default: 2)
     * @return float Nilai yang sudah dibulatkan
     */
    public function roundAccounting(float $value, int $precision = 2): float
    {
        // Convert to string dengan precision lebih tinggi
        $multiplier = pow(10, $precision);

        // Tambahkan 0.5 untuk rounding UP, lalu floor
        // Ini adalah implementasi HALF_UP
        return floor($value * $multiplier + 0.5) / $multiplier;
    }

    /**
     * Hitung total invoice dengan presisi accounting
     * 
     * BUG-SALES-003 FIX: Pastikan total = subtotal + tax (no rounding errors)
     * 
     * @param float $subtotal
     * @param float $taxAmount
     * @param float $discount
     * @return float Total yang sudah dibulatkan dengan benar
     */
    public function calculateTotal(float $subtotal, float $taxAmount = 0, float $discount = 0): float
    {
        // Hitung total
        $total = $subtotal - $discount + $taxAmount;

        // Round final total untuk consistency
        return $this->roundAccounting($total);
    }

    /**
     * Ambil tax rate aktif default untuk tenant (pertama yang aktif).
     */
    public function getDefault(int $tenantId): ?TaxRate
    {
        return TaxRate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * Daftar semua tax rate aktif untuk tenant.
     */
    public function activeRates(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return TaxRate::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
