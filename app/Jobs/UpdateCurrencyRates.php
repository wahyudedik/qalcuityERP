<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\CurrencyRateHistory;
use App\Models\ErpNotification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UpdateCurrencyRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $timeout = 60;

    public function handle(): void
    {
        // Ambil semua kode mata uang unik yang dipakai tenant
        $codes = Currency::where('is_base', false)
            ->where('is_active', true)
            ->distinct()
            ->pluck('code')
            ->filter(fn($c) => $c !== 'IDR')
            ->unique()
            ->values();

        if ($codes->isEmpty()) return;

        // Gunakan exchangerate-api.com (free tier, no key needed for basic)
        // Fallback: frankfurter.app (open source, no key)
        $rates = $this->fetchRates($codes->toArray());

        if (empty($rates)) {
            Log::warning('UpdateCurrencyRates: gagal mengambil data kurs.');
            return;
        }

        $today   = now()->toDateString();
        $updated = 0;

        // Update semua currency records yang cocok
        Currency::where('is_base', false)
            ->where('is_active', true)
            ->whereIn('code', array_keys($rates))
            ->get()
            ->each(function (Currency $currency) use ($rates, $today, &$updated) {
                $newRate = $rates[$currency->code] ?? null;
                if (!$newRate || $newRate <= 0) return;

                $currency->update([
                    'rate_to_idr'    => $newRate,
                    'rate_updated_at'=> now(),
                ]);

                // Simpan histori (satu record per hari per kode per tenant)
                CurrencyRateHistory::firstOrCreate(
                    [
                        'tenant_id'     => $currency->tenant_id,
                        'currency_code' => $currency->code,
                        'date'          => $today,
                    ],
                    ['rate_to_idr' => $newRate]
                );

                $updated++;
            });

        Log::info("UpdateCurrencyRates: updated={$updated} currencies date={$today}");
    }

    /**
     * Ambil kurs terhadap IDR dari frankfurter.app (gratis, tanpa API key).
     * Endpoint: https://api.frankfurter.app/latest?from=IDR&to=USD,EUR,...
     * Response: { "rates": { "USD": 0.000063, ... } } — ini IDR→foreign
     * Kita butuh foreign→IDR, jadi invert: rate_to_idr = 1 / rate
     */
    private function fetchRates(array $codes): array
    {
        try {
            $symbols = implode(',', $codes);

            $response = Http::timeout(15)
                ->get('https://api.frankfurter.app/latest', [
                    'from' => 'IDR',
                    'to'   => $symbols,
                ]);

            if (!$response->successful()) {
                Log::warning('Frankfurter API error: ' . $response->status());
                return [];
            }

            $data  = $response->json('rates') ?? [];
            $rates = [];

            foreach ($data as $code => $idrToForeign) {
                if ($idrToForeign > 0) {
                    // Invert: berapa IDR per 1 unit mata uang asing
                    $rates[$code] = round(1 / $idrToForeign, 4);
                }
            }

            return $rates;

        } catch (\Throwable $e) {
            Log::error('UpdateCurrencyRates fetch error: ' . $e->getMessage());
            return [];
        }
    }
}
