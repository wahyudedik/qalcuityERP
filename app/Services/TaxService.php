<?php

namespace App\Services;

use App\Models\TaxRate;

class TaxService
{
    /**
     * Hitung PPh withholding dari gross amount.
     * Untuk PPh 23: rate 2% dari bruto
     * Untuk PPh 21: rate sesuai konfigurasi
     * Untuk PPh 4 ayat 2: rate sesuai konfigurasi
     */
    public function calculatePph(float $grossAmount, string $taxType, int $tenantId): array
    {
        $taxRate = TaxRate::where('tenant_id', $tenantId)
            ->where('tax_type', $taxType)
            ->where('is_active', true)
            ->where('is_withholding', true)
            ->first();

        if (! $taxRate) {
            return ['amount' => 0, 'rate' => 0, 'tax_type' => $taxType];
        }

        $amount = round($grossAmount * ($taxRate->rate / 100), 2);

        return [
            'amount'   => $amount,
            'rate'     => $taxRate->rate,
            'tax_type' => $taxType,
            'label'    => $taxRate->getTypeLabel(),
        ];
    }

    /**
     * Hitung PPN dari subtotal.
     */
    public function calculatePpn(float $subtotal, int $tenantId): array
    {
        $taxRate = TaxRate::where('tenant_id', $tenantId)
            ->where('tax_type', 'ppn')
            ->where('is_active', true)
            ->first();

        if (! $taxRate) {
            return ['amount' => 0, 'rate' => 0];
        }

        return [
            'amount' => round($subtotal * ($taxRate->rate / 100), 2),
            'rate'   => $taxRate->rate,
        ];
    }

    /**
     * Hitung tax amount dari subtotal berdasarkan tax_rate_id.
     */
    public function calculate(float $subtotal, int $taxRateId): float
    {
        $taxRate = TaxRate::find($taxRateId);
        if (! $taxRate || ! $taxRate->is_active) {
            return 0.0;
        }

        return round($subtotal * ($taxRate->rate / 100), 2);
    }

    /**
     * Hitung tax dari subtotal + rate langsung (tanpa lookup DB).
     */
    public function calculateByRate(float $subtotal, float $rate): float
    {
        return round($subtotal * ($rate / 100), 2);
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
