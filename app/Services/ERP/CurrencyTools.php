<?php

namespace App\Services\ERP;

use App\Models\Currency;
use App\Models\CurrencyRateHistory;

class CurrencyTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name' => 'set_currency_rate',
                'description' => 'Set atau update kurs mata uang asing ke IDR.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'currency_code' => ['type' => 'string', 'description' => 'Kode mata uang: USD, EUR, SGD, MYR, JPY, dll'],
                        'rate_to_idr' => ['type' => 'number', 'description' => '1 unit mata uang = berapa IDR'],
                        'currency_name' => ['type' => 'string', 'description' => 'Nama mata uang (opsional, untuk pendaftaran baru)'],
                        'symbol' => ['type' => 'string', 'description' => 'Simbol mata uang (opsional)'],
                    ],
                    'required' => ['currency_code', 'rate_to_idr'],
                ],
            ],
            [
                'name' => 'convert_currency',
                'description' => 'Konversi jumlah dari satu mata uang ke mata uang lain.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'amount' => ['type' => 'number', 'description' => 'Jumlah yang akan dikonversi'],
                        'from_currency' => ['type' => 'string', 'description' => 'Mata uang asal (misal: USD)'],
                        'to_currency' => ['type' => 'string', 'description' => 'Mata uang tujuan (misal: IDR)'],
                    ],
                    'required' => ['amount', 'from_currency', 'to_currency'],
                ],
            ],
            [
                'name' => 'list_currencies',
                'description' => 'Tampilkan semua mata uang yang terdaftar beserta kurs terkini.',
                'parameters' => ['type' => 'object', 'properties' => []],
            ],
        ];
    }

    public function setCurrencyRate(array $args): array
    {
        $code = strtoupper($args['currency_code']);

        $currency = Currency::updateOrCreate(
            ['tenant_id' => $this->tenantId, 'code' => $code],
            [
                'name' => $args['currency_name'] ?? $code,
                'symbol' => $args['symbol'] ?? $code,
                'rate_to_idr' => $args['rate_to_idr'],
                'is_base' => false,
                'is_active' => true,
                'rate_updated_at' => now(),
            ]
        );

        // Simpan ke history
        CurrencyRateHistory::create([
            'tenant_id' => $this->tenantId,
            'currency_code' => $code,
            'rate_to_idr' => $args['rate_to_idr'],
            'date' => today()->toDateString(),
        ]);

        return [
            'status' => 'success',
            'message' => "Kurs **{$code}** diperbarui: 1 {$code} = Rp ".number_format($args['rate_to_idr'], 2, ',', '.'),
        ];
    }

    public function convertCurrency(array $args): array
    {
        $from = strtoupper($args['from_currency']);
        $to = strtoupper($args['to_currency']);

        // Dapatkan rate ke IDR
        $fromRate = $from === 'IDR' ? 1.0 : $this->getRate($from);
        $toRate = $to === 'IDR' ? 1.0 : $this->getRate($to);

        if ($fromRate === null) {
            return ['status' => 'error', 'message' => "Kurs {$from} belum diset. Gunakan set_currency_rate terlebih dahulu."];
        }
        if ($toRate === null) {
            return ['status' => 'error', 'message' => "Kurs {$to} belum diset. Gunakan set_currency_rate terlebih dahulu."];
        }

        $idrAmount = $args['amount'] * $fromRate;
        $resultAmount = $idrAmount / $toRate;

        return [
            'status' => 'success',
            'from' => number_format($args['amount'], 2)." {$from}",
            'to' => number_format($resultAmount, 2)." {$to}",
            'rate' => "1 {$from} = ".number_format($fromRate / $toRate, 4)." {$to}",
            'message' => number_format($args['amount'], 2)." {$from} = **".number_format($resultAmount, 2)." {$to}**",
        ];
    }

    public function listCurrencies(array $args): array
    {
        // Pastikan IDR ada
        $idr = Currency::firstOrCreate(
            ['tenant_id' => $this->tenantId, 'code' => 'IDR'],
            ['name' => 'Rupiah Indonesia', 'symbol' => 'Rp', 'rate_to_idr' => 1, 'is_base' => true, 'is_active' => true]
        );

        $currencies = Currency::where('tenant_id', $this->tenantId)->where('is_active', true)->get();

        return [
            'status' => 'success',
            'data' => $currencies->map(fn ($c) => [
                'kode' => $c->code,
                'nama' => $c->name,
                'simbol' => $c->symbol,
                'kurs_ke_idr' => 'Rp '.number_format($c->rate_to_idr, 2, ',', '.'),
                'diperbarui' => $c->rate_updated_at?->format('d M Y H:i') ?? '-',
                'base' => $c->is_base ? 'Ya' : 'Tidak',
            ])->toArray(),
        ];
    }

    private function getRate(string $code): ?float
    {
        $currency = Currency::where('tenant_id', $this->tenantId)->where('code', $code)->first();

        return $currency?->rate_to_idr;
    }
}
