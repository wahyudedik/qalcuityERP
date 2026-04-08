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

    public int $tries = 3;
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

        if ($codes->isEmpty())
            return;

        // Gunakan exchangerate-api.com (free tier, no key needed for basic)
        // Fallback: frankfurter.app (open source, no key)
        $rates = $this->fetchRates($codes->toArray());

        if (empty($rates)) {
            Log::warning('UpdateCurrencyRates: gagal mengambil data kurs.');

            // BUG-FIN-003 FIX: Send notification if rate update fails
            $this->notifyRateUpdateFailure($codes);
            return;
        }

        $today = now()->toDateString();
        $updated = 0;
        $updatedCodes = [];

        // Update semua currency records yang cocok
        Currency::where('is_base', false)
            ->where('is_active', true)
            ->whereIn('code', array_keys($rates))
            ->get()
            ->each(function (Currency $currency) use ($rates, $today, &$updated, &$updatedCodes) {
                $newRate = $rates[$currency->code] ?? null;
                if (!$newRate || $newRate <= 0)
                    return;

                $oldRate = $currency->rate_to_idr;

                $currency->update([
                    'rate_to_idr' => $newRate,
                    'rate_updated_at' => now(),
                ]);

                $updatedCodes[] = $currency->code;

                // Simpan histori (satu record per hari per kode per tenant)
                CurrencyRateHistory::firstOrCreate(
                    [
                        'tenant_id' => $currency->tenant_id,
                        'currency_code' => $currency->code,
                        'date' => $today,
                    ],
                    ['rate_to_idr' => $newRate]
                );

                $updated++;

                // Log rate changes
                $changePercent = $oldRate > 0 ? (($newRate - $oldRate) / $oldRate) * 100 : 0;
                Log::info("UpdateCurrencyRates: {$currency->code} updated", [
                    'old_rate' => $oldRate,
                    'new_rate' => $newRate,
                    'change_percent' => round($changePercent, 2),
                ]);
            });

        // BUG-FIN-003 FIX: Bust cache for updated currencies
        if (!empty($updatedCodes)) {
            $currencyService = new \App\Services\CurrencyService();
            $currencyService->bustAllCaches($updatedCodes);

            Log::info("UpdateCurrencyRates: Cache busted for updated currencies", [
                'currencies' => $updatedCodes,
                'count' => count($updatedCodes),
            ]);
        }

        Log::info("UpdateCurrencyRates: updated={$updated} currencies date={$today}");

        // Notify success if currencies were updated
        if ($updated > 0) {
            $this->notifyRateUpdateSuccess($updated, $today);
        }
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
                    'to' => $symbols,
                ]);

            if (!$response->successful()) {
                Log::warning('Frankfurter API error: ' . $response->status());
                return [];
            }

            $data = $response->json('rates') ?? [];
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

    /**
     * BUG-FIN-003 FIX: Notify admin when rate update succeeds
     */
    private function notifyRateUpdateSuccess(int $updatedCount, string $date): void
    {
        // Get all admin users from all tenants
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $adminUsers = User::where('tenant_id', $tenant->id)
                ->where(function ($q) {
                    $q->where('role', 'admin')
                        ->orWhere('role', 'finance_manager')
                        ->orWhere('is_admin', true);
                })
                ->get();

            foreach ($adminUsers as $user) {
                ErpNotification::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'type' => 'currency_rate_updated',
                    'title' => '💱 Kurs Mata Uang Diperbarui',
                    'message' => "Kurs {$updatedCount} mata uang berhasil diperbarui untuk tanggal {$date}.",
                    'data' => [
                        'updated_count' => $updatedCount,
                        'date' => $date,
                    ],
                    'is_read' => false,
                ]);
            }
        }
    }

    /**
     * BUG-FIN-003 FIX: Notify admin when rate update fails
     */
    private function notifyRateUpdateFailure(array $currencyCodes): void
    {
        // Get all admin users from all tenants
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $adminUsers = User::where('tenant_id', $tenant->id)
                ->where(function ($q) {
                    $q->where('role', 'admin')
                        ->orWhere('role', 'finance_manager')
                        ->orWhere('is_admin', true);
                })
                ->get();

            foreach ($adminUsers as $user) {
                ErpNotification::create([
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                    'type' => 'currency_rate_update_failed',
                    'title' => '⚠️ Gagal Update Kurs Mata Uang',
                    'message' => "Gagal memperbarui kurs mata uang. Kurs saat ini mungkin sudah tidak akurat. " .
                        "Silakan update manual di Settings → Currency.",
                    'data' => [
                        'affected_currencies' => $currencyCodes,
                        'failed_at' => now()->format('Y-m-d H:i:s'),
                    ],
                    'is_read' => false,
                ]);
            }
        }
    }
}
