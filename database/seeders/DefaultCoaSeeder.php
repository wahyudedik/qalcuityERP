<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

/**
 * Seed Chart of Accounts default Indonesia untuk satu tenant.
 * Dipanggil saat onboarding atau via artisan.
 */
class DefaultCoaSeeder extends Seeder
{
    public static function seedForTenant(int $tenantId): void
    {
        $accounts = self::defaultAccounts();

        $parentMap = []; // code => id

        foreach ($accounts as $acc) {
            $parentId = isset($acc['parent_code']) ? ($parentMap[$acc['parent_code']] ?? null) : null;

            $record = ChartOfAccount::firstOrCreate(
                ['tenant_id' => $tenantId, 'code' => $acc['code']],
                [
                    'parent_id'      => $parentId,
                    'name'           => $acc['name'],
                    'type'           => $acc['type'],
                    'normal_balance' => $acc['normal_balance'],
                    'level'          => $acc['level'],
                    'is_header'      => $acc['is_header'] ?? false,
                    'is_active'      => true,
                ]
            );

            $parentMap[$acc['code']] = $record->id;
        }
    }

    public function run(): void
    {
        // Seed untuk semua tenant yang ada
        \App\Models\Tenant::all()->each(fn($t) => self::seedForTenant($t->id));
    }

    private static function defaultAccounts(): array
    {
        return [
            // ── ASET ──────────────────────────────────────────────────────
            ['code' => '1000', 'name' => 'ASET',                        'type' => 'asset',     'normal_balance' => 'debit',  'level' => 1, 'is_header' => true],
            ['code' => '1100', 'name' => 'Aset Lancar',                 'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '1000'],
            ['code' => '1101', 'name' => 'Kas',                         'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1102', 'name' => 'Bank',                        'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1103', 'name' => 'Piutang Usaha',               'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1104', 'name' => 'Piutang Lain-lain',           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1105', 'name' => 'Persediaan Barang',           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1106', 'name' => 'Biaya Dibayar di Muka',       'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1107', 'name' => 'PPN Masukan',                 'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1100'],
            ['code' => '1200', 'name' => 'Aset Tidak Lancar',           'type' => 'asset',     'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '1000'],
            ['code' => '1201', 'name' => 'Aset Tetap',                  'type' => 'asset',     'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '1200'],
            ['code' => '1202', 'name' => 'Akumulasi Penyusutan',        'type' => 'asset',     'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '1200'],

            // ── KEWAJIBAN ─────────────────────────────────────────────────
            ['code' => '2000', 'name' => 'KEWAJIBAN',                   'type' => 'liability', 'normal_balance' => 'credit', 'level' => 1, 'is_header' => true],
            ['code' => '2100', 'name' => 'Kewajiban Lancar',            'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '2000'],
            ['code' => '2101', 'name' => 'Hutang Usaha',                'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2102', 'name' => 'Hutang Lain-lain',            'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2103', 'name' => 'PPN Keluaran',                'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2104', 'name' => 'PPh 21 Terutang',             'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2105', 'name' => 'PPh 23 Terutang',             'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2106', 'name' => 'Pendapatan Diterima di Muka', 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2107', 'name' => 'Uang Muka Pelanggan',         'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2200', 'name' => 'Kewajiban Jangka Panjang',    'type' => 'liability', 'normal_balance' => 'credit', 'level' => 2, 'is_header' => true,  'parent_code' => '2000'],
            ['code' => '2201', 'name' => 'Hutang Bank Jangka Panjang',  'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2200'],

            // ── EKUITAS ───────────────────────────────────────────────────
            ['code' => '3000', 'name' => 'EKUITAS',                     'type' => 'equity',    'normal_balance' => 'credit', 'level' => 1, 'is_header' => true],
            ['code' => '3101', 'name' => 'Modal Disetor',               'type' => 'equity',    'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '3000'],
            ['code' => '3102', 'name' => 'Laba Ditahan',                'type' => 'equity',    'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '3000'],
            ['code' => '3103', 'name' => 'Laba/Rugi Tahun Berjalan',    'type' => 'equity',    'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '3000'],

            // ── PENDAPATAN ────────────────────────────────────────────────
            ['code' => '4000', 'name' => 'PENDAPATAN',                  'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 1, 'is_header' => true],
            ['code' => '4101', 'name' => 'Pendapatan Penjualan',        'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '4000'],
            ['code' => '4102', 'name' => 'Pendapatan Jasa',             'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '4000'],
            ['code' => '4103', 'name' => 'Pendapatan Lain-lain',        'type' => 'revenue',   'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '4000'],
            ['code' => '4104', 'name' => 'Diskon Penjualan',            'type' => 'revenue',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '4000'],

            // ── BEBAN ─────────────────────────────────────────────────────
            ['code' => '5000', 'name' => 'BEBAN',                       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 1, 'is_header' => true],
            ['code' => '5100', 'name' => 'Harga Pokok Penjualan',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '5000'],
            ['code' => '5101', 'name' => 'HPP Barang',                  'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5100'],
            ['code' => '5200', 'name' => 'Beban Operasional',           'type' => 'expense',   'normal_balance' => 'debit',  'level' => 2, 'is_header' => true,  'parent_code' => '5000'],
            ['code' => '5201', 'name' => 'Beban Gaji',                  'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5202', 'name' => 'Beban Sewa',                  'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5203', 'name' => 'Beban Listrik & Air',         'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5204', 'name' => 'Beban Penyusutan',            'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5205', 'name' => 'Beban Pemasaran',             'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5206', 'name' => 'Beban Administrasi',          'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5207', 'name' => 'Beban Pajak',                 'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5208', 'name' => 'Beban Lain-lain',             'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],
            ['code' => '5209', 'name' => 'Beban BPJS Perusahaan',       'type' => 'expense',   'normal_balance' => 'debit',  'level' => 3, 'parent_code' => '5200'],

            // ── Akun Payroll (Hutang Gaji & Kewajiban) ────────────────────
            ['code' => '2108', 'name' => 'Hutang Gaji',                 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
            ['code' => '2109', 'name' => 'Hutang BPJS',                 'type' => 'liability', 'normal_balance' => 'credit', 'level' => 3, 'parent_code' => '2100'],
        ];
    }
}
