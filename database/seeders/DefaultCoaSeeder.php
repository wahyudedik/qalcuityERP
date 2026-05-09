<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class DefaultCoaSeeder extends Seeder
{
    /**
     * Seed default Indonesian COA for a specific tenant.
     * Called from AccountingController::seedDefaultCoa()
     */
    public static function seedForTenant(int $tenantId): void
    {
        // Skip if tenant already has accounts
        if (ChartOfAccount::where('tenant_id', $tenantId)->exists()) {
            return;
        }

        $accounts = self::getDefaultAccounts();

        // First pass: create header accounts (no parent)
        $created = [];
        foreach ($accounts as $acc) {
            if ($acc['is_header'] && ! $acc['parent_code']) {
                $record = ChartOfAccount::create([
                    'tenant_id' => $tenantId,
                    'parent_id' => null,
                    'code' => $acc['code'],
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'normal_balance' => $acc['normal_balance'],
                    'level' => $acc['level'],
                    'is_header' => $acc['is_header'],
                    'is_active' => true,
                    'description' => $acc['description'] ?? null,
                ]);
                $created[$acc['code']] = $record->id;
            }
        }

        // Subsequent passes: create child accounts
        $maxPasses = 5;
        $remaining = array_filter($accounts, fn ($a) => ! isset($created[$a['code']]));

        for ($pass = 0; $pass < $maxPasses && count($remaining) > 0; $pass++) {
            foreach ($remaining as $key => $acc) {
                $parentId = $acc['parent_code'] ? ($created[$acc['parent_code']] ?? null) : null;

                if ($acc['parent_code'] && ! $parentId) {
                    continue; // parent not yet created
                }

                $record = ChartOfAccount::create([
                    'tenant_id' => $tenantId,
                    'parent_id' => $parentId,
                    'code' => $acc['code'],
                    'name' => $acc['name'],
                    'type' => $acc['type'],
                    'normal_balance' => $acc['normal_balance'],
                    'level' => $acc['level'],
                    'is_header' => $acc['is_header'],
                    'is_active' => true,
                    'description' => $acc['description'] ?? null,
                ]);
                $created[$acc['code']] = $record->id;
                unset($remaining[$key]);
            }
        }
    }

    /**
     * Standard Indonesian SME Chart of Accounts (PSAK-based)
     */
    private static function getDefaultAccounts(): array
    {
        return [
            // ── ASET (1xxx) ──────────────────────────────────────────────
            ['code' => '1000', 'name' => 'ASET',                          'type' => 'asset',     'normal_balance' => 'debit',  'level' => 1, 'is_header' => true,  'parent_code' => null],

            // Aset Lancar
            ['code' => '1100', 'name' => 'Aset Lancar',                   'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '1000'],
            ['code' => '1101', 'name' => 'Kas',                           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1102', 'name' => 'Kas Kecil',                     'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1103', 'name' => 'Bank BCA',                      'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1104', 'name' => 'Bank BRI',                      'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1105', 'name' => 'Bank Mandiri',                  'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1110', 'name' => 'Piutang Usaha',                 'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1111', 'name' => 'Cadangan Kerugian Piutang',     'type' => 'asset',     'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1112', 'name' => 'Piutang Lain-lain',             'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1120', 'name' => 'Persediaan Barang Dagang',      'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1121', 'name' => 'Persediaan Bahan Baku',         'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1122', 'name' => 'Persediaan Barang Dalam Proses', 'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1130', 'name' => 'Uang Muka Pembelian',           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1131', 'name' => 'Biaya Dibayar Dimuka',          'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],
            ['code' => '1132', 'name' => 'PPN Masukan',                   'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1100'],

            // Aset Tidak Lancar
            ['code' => '1200', 'name' => 'Aset Tidak Lancar',             'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '1000'],
            ['code' => '1201', 'name' => 'Tanah',                         'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1202', 'name' => 'Bangunan',                      'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1203', 'name' => 'Akumulasi Penyusutan Bangunan', 'type' => 'asset',     'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1204', 'name' => 'Kendaraan',                     'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1205', 'name' => 'Akumulasi Penyusutan Kendaraan', 'type' => 'asset',     'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1206', 'name' => 'Peralatan',                     'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1207', 'name' => 'Akumulasi Penyusutan Peralatan', 'type' => 'asset',     'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1208', 'name' => 'Inventaris Kantor',             'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1209', 'name' => 'Akumulasi Penyusutan Inventaris', 'type' => 'asset',    'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1210', 'name' => 'Aset Tak Berwujud',             'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '1200'],
            ['code' => '1211', 'name' => 'Akumulasi Amortisasi',          'type' => 'asset',     'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '1200'],

            // ── KEWAJIBAN (2xxx) ─────────────────────────────────────────
            ['code' => '2000', 'name' => 'KEWAJIBAN',                     'type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'parent_code' => null],

            // Kewajiban Lancar
            ['code' => '2100', 'name' => 'Kewajiban Lancar',              'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '2000'],
            ['code' => '2101', 'name' => 'Utang Usaha',                   'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2102', 'name' => 'Utang Lain-lain',               'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2103', 'name' => 'Utang Gaji',                    'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2104', 'name' => 'Utang Pajak PPh 21',            'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2105', 'name' => 'Utang Pajak PPh 23',            'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2106', 'name' => 'PPN Keluaran',                  'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2107', 'name' => 'Uang Muka Penjualan',           'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2108', 'name' => 'Pendapatan Diterima Dimuka',    'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2109', 'name' => 'Utang BPJS Kesehatan',          'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2110', 'name' => 'Utang BPJS Ketenagakerjaan',    'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],
            ['code' => '2111', 'name' => 'Utang Bank Jangka Pendek',      'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2100'],

            // Kewajiban Jangka Panjang
            ['code' => '2200', 'name' => 'Kewajiban Jangka Panjang',      'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '2000'],
            ['code' => '2201', 'name' => 'Utang Bank Jangka Panjang',     'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2200'],
            ['code' => '2202', 'name' => 'Utang Leasing',                 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '2200'],

            // ── EKUITAS (3xxx) ───────────────────────────────────────────
            ['code' => '3000', 'name' => 'EKUITAS',                       'type' => 'equity',    'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '3100', 'name' => 'Modal Disetor',                 'type' => 'equity',    'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '3000'],
            ['code' => '3101', 'name' => 'Modal Pemilik',                 'type' => 'equity',    'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '3100'],
            ['code' => '3102', 'name' => 'Prive / Penarikan Pemilik',     'type' => 'equity',    'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '3100'],
            ['code' => '3200', 'name' => 'Laba Ditahan',                  'type' => 'equity',    'normal_balance' => 'credit', 'level' => 2, 'is_header' => false, 'parent_code' => '3000'],
            ['code' => '3201', 'name' => 'Laba Tahun Berjalan',           'type' => 'equity',    'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '3200'],
            ['code' => '3202', 'name' => 'Laba Tahun Lalu',               'type' => 'equity',    'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '3200'],

            // ── PENDAPATAN (4xxx) ────────────────────────────────────────
            ['code' => '4000', 'name' => 'PENDAPATAN',                    'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 1, 'is_header' => true,  'parent_code' => null],
            ['code' => '4100', 'name' => 'Pendapatan Usaha',              'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '4000'],
            ['code' => '4101', 'name' => 'Penjualan',                     'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100'],
            ['code' => '4102', 'name' => 'Retur Penjualan',               'type' => 'revenue',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '4100'],
            ['code' => '4103', 'name' => 'Potongan Penjualan',            'type' => 'revenue',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '4100'],
            ['code' => '4104', 'name' => 'Pendapatan Jasa',               'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4100'],
            ['code' => '4200', 'name' => 'Pendapatan Lain-lain',          'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '4000'],
            ['code' => '4201', 'name' => 'Pendapatan Bunga',              'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4200'],
            ['code' => '4202', 'name' => 'Laba Penjualan Aset',           'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4200'],
            ['code' => '4203', 'name' => 'Pendapatan Lainnya',            'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '4200'],

            // ── BEBAN (5xxx) ─────────────────────────────────────────────
            ['code' => '5000', 'name' => 'BEBAN',                         'type' => 'expense',   'normal_balance' => 'debit',  'level' => 1, 'is_header' => true,  'parent_code' => null],

            // HPP
            ['code' => '5100', 'name' => 'Harga Pokok Penjualan',         'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '5000'],
            ['code' => '5101', 'name' => 'HPP Barang Dagang',             'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5100'],
            ['code' => '5102', 'name' => 'Pembelian',                     'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5100'],
            ['code' => '5103', 'name' => 'Retur Pembelian',               'type' => 'expense',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100'],
            ['code' => '5104', 'name' => 'Potongan Pembelian',            'type' => 'expense',   'normal_balance' => 'credit', 'level' => 3, 'is_header' => false, 'parent_code' => '5100'],
            ['code' => '5105', 'name' => 'Biaya Angkut Pembelian',        'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5100'],

            // Beban Operasional
            ['code' => '5200', 'name' => 'Beban Operasional',             'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '5000'],
            ['code' => '5201', 'name' => 'Beban Gaji',                    'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5202', 'name' => 'Beban Tunjangan',               'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5203', 'name' => 'Beban BPJS Kesehatan',          'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5204', 'name' => 'Beban BPJS Ketenagakerjaan',    'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5205', 'name' => 'Beban Sewa',                    'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5206', 'name' => 'Beban Listrik & Air',           'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5207', 'name' => 'Beban Telepon & Internet',      'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5208', 'name' => 'Beban Perlengkapan Kantor',     'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5209', 'name' => 'Beban Pemasaran & Iklan',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5210', 'name' => 'Beban Transportasi',            'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5211', 'name' => 'Beban Perjalanan Dinas',        'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5212', 'name' => 'Beban Pemeliharaan & Perbaikan', 'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5213', 'name' => 'Beban Penyusutan',              'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5214', 'name' => 'Beban Amortisasi',              'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5215', 'name' => 'Beban Asuransi',                'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],
            ['code' => '5216', 'name' => 'Beban Kebersihan & Keamanan',   'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5200'],

            // Beban Lain-lain
            ['code' => '5300', 'name' => 'Beban Lain-lain',               'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '5000'],
            ['code' => '5301', 'name' => 'Beban Bunga',                   'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5300'],
            ['code' => '5302', 'name' => 'Beban Administrasi Bank',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5300'],
            ['code' => '5303', 'name' => 'Beban Pajak',                   'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5300'],
            ['code' => '5304', 'name' => 'Kerugian Penjualan Aset',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5300'],
            ['code' => '5305', 'name' => 'Beban Lainnya',                 'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'is_header' => false, 'parent_code' => '5300'],
        ];
    }

    /**
     * Run as a standard Laravel seeder (for artisan db:seed)
     */
    public function run(): void
    {
        // No-op when run globally — use seedForTenant() instead
    }
}
