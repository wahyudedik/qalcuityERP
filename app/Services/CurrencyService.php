<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * CurrencyService - Currency conversion and rate management
 * 
 * BUG-FIN-003 FIX: Added stale rate detection and cache invalidation
 */
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
        $to = $this->getCurrency($toCode);

        if (!$from || !$to) {
            return $amount;
        }

        // BUG-FIN-003 FIX: Check for stale rates and log warning
        $this->checkAndLogStaleRate($from);
        $this->checkAndLogStaleRate($to);

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

    /**
     * BUG-FIN-003 FIX: Get stale currencies report
     * 
     * @param int $tenantId
     * @return array ['warning' => [...], 'critical' => [...]]
     */
    public function getStaleCurrenciesReport(int $tenantId): array
    {
        $currencies = Currency::where(fn($q) => $q->where('tenant_id', $tenantId)->orWhereNull('tenant_id'))
            ->where('is_active', true)
            ->where('is_base', false)
            ->get();

        $report = [
            'warning' => [],
            'critical' => [],
        ];

        foreach ($currencies as $currency) {
            $status = $currency->getRateStalenessStatus();
            $daysSinceUpdate = $currency->getDaysSinceLastUpdate();

            if ($status === 'stale_warning') {
                $report['warning'][] = [
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                    'rate_to_idr' => $currency->rate_to_idr,
                    'days_since_update' => $daysSinceUpdate,
                    'last_updated' => $currency->rate_updated_at?->format('Y-m-d H:i:s'),
                    'message' => "Kurs {$currency->code} sudah {$daysSinceUpdate} hari tidak diupdate",
                ];
            } elseif ($status === 'stale_critical') {
                $report['critical'][] = [
                    'currency_code' => $currency->code,
                    'currency_name' => $currency->name,
                    'rate_to_idr' => $currency->rate_to_idr,
                    'days_since_update' => $daysSinceUpdate ?? 'Never',
                    'last_updated' => $currency->rate_updated_at?->format('Y-m-d H:i:s') ?? 'Never',
                    'message' => "Kurs {$currency->code} KRITIS - {$daysSinceUpdate} hari tidak diupdate!",
                ];
            }
        }

        return $report;
    }

    /**
     * BUG-FIN-003 FIX: Bust cache for specific currency
     * 
     * Call this after rate update to ensure fresh data
     * 
     * @param string $currencyCode
     * @return void
     */
    public function bustCache(string $currencyCode): void
    {
        Cache::forget("currency_{$currencyCode}");
        Log::info("CurrencyService: Cache busted for {$currencyCode}");
    }

    /**
     * BUG-FIN-003 FIX: Bust all currency caches
     * 
     * Call this after bulk rate update
     * 
     * @param array $currencyCodes
     * @return void
     */
    public function bustAllCaches(array $currencyCodes): void
    {
        foreach ($currencyCodes as $code) {
            $this->bustCache($code);
        }
    }

    private function getCurrency(string $code): ?Currency
    {
        return Cache::remember(
            "currency_{$code}",
            300,
            fn() =>
            Currency::where('code', strtoupper($code))->first()
        );
    }

    /**
     * BUG-FIN-003 FIX: Check and log stale rate warnings
     * 
     * @param Currency $currency
     * @return void
     */
    private function checkAndLogStaleRate(Currency $currency): void
    {
        if ($currency->is_base) {
            return; // Base currency doesn't need checks
        }

        $status = $currency->getRateStalenessStatus();

        if ($status === 'stale_warning') {
            $days = $currency->getDaysSinceLastUpdate();
            Log::warning("CurrencyService: Stale rate detected", [
                'currency' => $currency->code,
                'days_since_update' => $days,
                'rate' => $currency->rate_to_idr,
                'severity' => 'warning',
            ]);
        } elseif ($status === 'stale_critical') {
            $days = $currency->getDaysSinceLastUpdate();
            Log::critical("CurrencyService: CRITICAL stale rate - conversion may be inaccurate!", [
                'currency' => $currency->code,
                'days_since_update' => $days ?? 'never',
                'rate' => $currency->rate_to_idr,
                'severity' => 'critical',
            ]);
        }
    }
}
