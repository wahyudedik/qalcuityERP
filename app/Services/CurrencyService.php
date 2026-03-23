<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;

class CurrencyService
{
    /**
     * Konversi amount dari satu currency ke currency lain.
     * Semua konversi melalui IDR sebagai base.
     */
    public function convert(float $amount, string $fromCode, string $toCode): float
    {
        if ($fromCode === $toCode) {
            return $amount;
        }

        $from = $this->getCurrency($fromCode);
        $to   = $this->getCurrency($toCode);

        if (! $from || ! $to) {
            return $amount;
        }

        // amount → IDR → target currency
        $idr = $amount * $from->rate_to_idr;
        return $to->rate_to_idr > 0 ? $idr / $to->rate_to_idr : $idr;
    }

    /**
     * Konversi ke IDR.
     */
    public function toIdr(float $amount, string $fromCode): float
    {
        return $this->convert($amount, $fromCode, 'IDR');
    }

    /**
     * Ambil rate currency terhadap IDR.
     */
    public function getRate(string $code): float
    {
        $currency = $this->getCurrency($code);
        return $currency?->rate_to_idr ?? 1.0;
    }

    /**
     * Daftar currency aktif untuk tenant.
     */
    public function activeCurrencies(int $tenantId): \Illuminate\Database\Eloquent\Collection
    {
        return Currency::where(fn($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->where('is_active', true)
            ->orderBy('code')
            ->get();
    }

    private function getCurrency(string $code): ?Currency
    {
        return Cache::remember("currency_{$code}", 300, fn() =>
            Currency::where('code', strtoupper($code))->first()
        );
    }
}
