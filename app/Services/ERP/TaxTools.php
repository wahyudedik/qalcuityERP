<?php

namespace App\Services\ERP;

use App\Models\TaxRate;
use App\Models\TaxRecord;
use Illuminate\Support\Str;

class TaxTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'setup_tax_rates',
                'description' => 'Setup tarif pajak standar Indonesia (PPN 11%, PPh 23, PPh Final 0.5%).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'include_ppn'      => ['type' => 'boolean', 'description' => 'Aktifkan PPN 11% (default: true)'],
                        'include_pph23'    => ['type' => 'boolean', 'description' => 'Aktifkan PPh 23 (default: true)'],
                        'include_pph_final'=> ['type' => 'boolean', 'description' => 'Aktifkan PPh Final 0.5% UMKM (default: true)'],
                    ],
                ],
            ],
            [
                'name'        => 'record_tax',
                'description' => 'Catat transaksi pajak (PPN keluaran/masukan, PPh).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'type'             => ['type' => 'string', 'description' => 'ppn_out (PPN keluaran), ppn_in (PPN masukan), pph21, pph23, pph_final'],
                        'base_amount'      => ['type' => 'number', 'description' => 'Dasar pengenaan pajak (DPP)'],
                        'party_name'       => ['type' => 'string', 'description' => 'Nama customer/supplier'],
                        'npwp'             => ['type' => 'string', 'description' => 'NPWP pihak lawan (opsional)'],
                        'transaction_date' => ['type' => 'string', 'description' => 'Tanggal transaksi YYYY-MM-DD'],
                        'notes'            => ['type' => 'string', 'description' => 'Keterangan (opsional)'],
                    ],
                    'required' => ['type', 'base_amount'],
                ],
            ],
            [
                'name'        => 'get_tax_report',
                'description' => 'Laporan pajak per periode — PPN, PPh, total kewajiban pajak.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode YYYY-MM (default: bulan ini)'],
                        'type'   => ['type' => 'string', 'description' => 'Filter tipe pajak (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'calculate_ppn',
                'description' => 'Hitung PPN dari suatu transaksi.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'amount'       => ['type' => 'number', 'description' => 'Jumlah transaksi (Rp)'],
                        'include_ppn'  => ['type' => 'boolean', 'description' => 'true = amount sudah termasuk PPN, false = belum (default: false)'],
                        'rate'         => ['type' => 'number', 'description' => 'Tarif PPN % (default: 11)'],
                    ],
                    'required' => ['amount'],
                ],
            ],
        ];
    }

    public function setupTaxRates(array $args): array
    {
        $rates = [];

        if ($args['include_ppn'] ?? true) {
            $rates[] = ['name' => 'PPN 11%', 'code' => 'PPN', 'type' => 'ppn', 'rate' => 11];
        }
        if ($args['include_pph23'] ?? true) {
            $rates[] = ['name' => 'PPh 23 - Jasa (2%)', 'code' => 'PPH23_JASA', 'type' => 'pph23', 'rate' => 2];
            $rates[] = ['name' => 'PPh 23 - Dividen (15%)', 'code' => 'PPH23_DIV', 'type' => 'pph23', 'rate' => 15];
        }
        if ($args['include_pph_final'] ?? true) {
            $rates[] = ['name' => 'PPh Final UMKM 0.5%', 'code' => 'PPH_FINAL', 'type' => 'pph_final', 'rate' => 0.5];
        }

        $created = [];
        foreach ($rates as $rate) {
            $exists = TaxRate::where('tenant_id', $this->tenantId)->where('code', $rate['code'])->exists();
            if (!$exists) {
                TaxRate::create(['tenant_id' => $this->tenantId, ...$rate, 'is_active' => true]);
                $created[] = $rate['name'];
            }
        }

        return [
            'status'  => 'success',
            'message' => empty($created)
                ? 'Semua tarif pajak sudah terdaftar sebelumnya.'
                : 'Tarif pajak berhasil disetup: **' . implode('**, **', $created) . '**.',
        ];
    }

    public function recordTax(array $args): array
    {
        $taxRate = TaxRate::where('tenant_id', $this->tenantId)
            ->where('type', explode('_', $args['type'])[0] === 'ppn' ? 'ppn' : $args['type'])
            ->where('is_active', true)
            ->first();

        $rate      = $taxRate?->rate ?? $this->defaultRate($args['type']);
        $taxAmount = $args['base_amount'] * ($rate / 100);
        $period    = isset($args['transaction_date'])
            ? substr($args['transaction_date'], 0, 7)
            : now()->format('Y-m');

        $record = TaxRecord::create([
            'tenant_id'        => $this->tenantId,
            'tax_code'         => 'TAX-' . strtoupper(Str::random(8)),
            'type'             => $args['type'],
            'party_name'       => $args['party_name'] ?? null,
            'npwp'             => $args['npwp'] ?? null,
            'base_amount'      => $args['base_amount'],
            'tax_amount'       => $taxAmount,
            'rate'             => $rate,
            'transaction_date' => $args['transaction_date'] ?? today()->toDateString(),
            'period'           => $period,
            'status'           => 'recorded',
            'notes'            => $args['notes'] ?? null,
        ]);

        return [
            'status'  => 'success',
            'message' => "Pajak **{$args['type']}** dicatat. DPP: Rp " . number_format($args['base_amount'], 0, ',', '.')
                . " | Tarif: {$rate}% | Pajak: Rp " . number_format($taxAmount, 0, ',', '.'),
        ];
    }

    public function getTaxReport(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $query  = TaxRecord::where('tenant_id', $this->tenantId)->where('period', $period);

        if (!empty($args['type'])) $query->where('type', $args['type']);

        $records = $query->get();

        if ($records->isEmpty()) {
            return ['status' => 'success', 'message' => "Tidak ada catatan pajak untuk periode {$period}."];
        }

        $byType = $records->groupBy('type')->map(fn($g) => [
            'type'       => $g->first()->type,
            'jumlah_trx' => $g->count(),
            'total_dpp'  => 'Rp ' . number_format($g->sum('base_amount'), 0, ',', '.'),
            'total_pajak'=> 'Rp ' . number_format($g->sum('tax_amount'), 0, ',', '.'),
        ])->values();

        $ppnOut = $records->where('type', 'ppn_out')->sum('tax_amount');
        $ppnIn  = $records->where('type', 'ppn_in')->sum('tax_amount');
        $ppnNet = $ppnOut - $ppnIn;

        return [
            'status'          => 'success',
            'period'          => $period,
            'ppn_keluaran'    => 'Rp ' . number_format($ppnOut, 0, ',', '.'),
            'ppn_masukan'     => 'Rp ' . number_format($ppnIn, 0, ',', '.'),
            'ppn_kurang_bayar'=> 'Rp ' . number_format(max(0, $ppnNet), 0, ',', '.'),
            'total_pajak'     => 'Rp ' . number_format($records->sum('tax_amount'), 0, ',', '.'),
            'breakdown'       => $byType->toArray(),
        ];
    }

    public function calculatePpn(array $args): array
    {
        $rate       = $args['rate'] ?? 11;
        $amount     = $args['amount'];
        $includePpn = $args['include_ppn'] ?? false;

        if ($includePpn) {
            $dpp = $amount / (1 + $rate / 100);
            $ppn = $amount - $dpp;
        } else {
            $dpp = $amount;
            $ppn = $amount * ($rate / 100);
        }

        return [
            'status'  => 'success',
            'dpp'     => 'Rp ' . number_format($dpp, 0, ',', '.'),
            'ppn'     => 'Rp ' . number_format($ppn, 0, ',', '.'),
            'total'   => 'Rp ' . number_format($dpp + $ppn, 0, ',', '.'),
            'message' => "DPP: Rp " . number_format($dpp, 0, ',', '.') . " | PPN {$rate}%: Rp " . number_format($ppn, 0, ',', '.') . " | Total: Rp " . number_format($dpp + $ppn, 0, ',', '.'),
        ];
    }

    private function defaultRate(string $type): float
    {
        return match ($type) {
            'ppn_out', 'ppn_in' => 11,
            'pph23'             => 2,
            'pph_final'         => 0.5,
            'pph21'             => 5,
            default             => 0,
        };
    }
}
