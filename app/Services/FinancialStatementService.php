<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

/**
 * FinancialStatementService
 *
 * Menghasilkan 3 laporan keuangan formal dari GL (journal entries):
 *  1. Balance Sheet (Neraca)
 *  2. Income Statement / P&L (Laporan Laba Rugi)
 *  3. Cash Flow Statement (Laporan Arus Kas — indirect method)
 */
class FinancialStatementService
{
    // ─── Balance Sheet ────────────────────────────────────────────

    /**
     * Neraca per tanggal tertentu.
     * Saldo akumulatif dari awal hingga $asOf.
     *
     * Returns:
     *   assets        => [ current => [...], non_current => [...], total => float ]
     *   liabilities   => [ current => [...], long_term => [...], total => float ]
     *   equity        => [ items => [...], total => float ]
     *   net_income    => float  (laba/rugi periode berjalan dari P&L)
     *   total_l_e     => float  (total liabilities + equity, harus = total assets)
     *   is_balanced   => bool
     */
    public function balanceSheet(int $tenantId, string $asOf): array
    {
        $accounts = $this->getAccountBalances($tenantId, null, $asOf);

        $assets      = $accounts->where('type', 'asset');
        $liabilities = $accounts->where('type', 'liability');
        $equity      = $accounts->where('type', 'equity');

        // Pisahkan aset lancar vs tidak lancar berdasarkan kode (1100-1199 = lancar)
        $currentAssets    = $assets->filter(fn($a) => $a['code'] >= '1100' && $a['code'] < '1200');
        $nonCurrentAssets = $assets->filter(fn($a) => $a['code'] >= '1200');

        // Pisahkan kewajiban lancar vs jangka panjang (2100-2199 = lancar)
        $currentLiab  = $liabilities->filter(fn($a) => $a['code'] >= '2100' && $a['code'] < '2200');
        $longTermLiab = $liabilities->filter(fn($a) => $a['code'] >= '2200');

        $totalAssets      = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity      = $equity->sum('balance');

        // Laba/rugi berjalan dari P&L (revenue - expense)
        $netIncome = $this->calcNetIncome($tenantId, null, $asOf);

        $totalLiabEquity = $totalLiabilities + $totalEquity + $netIncome;

        return [
            'as_of'         => $asOf,
            'assets'        => [
                'current'     => $currentAssets->values(),
                'non_current' => $nonCurrentAssets->values(),
                'total'       => $totalAssets,
            ],
            'liabilities'   => [
                'current'   => $currentLiab->values(),
                'long_term' => $longTermLiab->values(),
                'total'     => $totalLiabilities,
            ],
            'equity'        => [
                'items' => $equity->values(),
                'total' => $totalEquity,
            ],
            'net_income'    => $netIncome,
            'total_l_e'     => $totalLiabEquity,
            'total_assets'  => $totalAssets,
            'is_balanced'   => abs($totalAssets - $totalLiabEquity) < 1,
        ];
    }

    // ─── Income Statement (P&L) ───────────────────────────────────

    /**
     * Laporan Laba Rugi untuk periode $from - $to.
     *
     * Returns:
     *   revenue       => [ items => [...], total => float ]
     *   cogs          => [ items => [...], total => float ]  (HPP)
     *   gross_profit  => float
     *   opex          => [ items => [...], total => float ]  (Beban Operasional)
     *   operating_income => float
     *   other         => [ items => [...], total => float ]  (Beban/Pendapatan Lain)
     *   net_income    => float
     */
    public function incomeStatement(int $tenantId, string $from, string $to): array
    {
        $accounts = $this->getAccountBalances($tenantId, $from, $to);

        $revenue  = $accounts->where('type', 'revenue');
        $expenses = $accounts->where('type', 'expense');

        // HPP: kode 5100-5199
        $cogs = $expenses->filter(fn($a) => $a['code'] >= '5100' && $a['code'] < '5200');
        // Beban Operasional: kode 5200-5299
        $opex = $expenses->filter(fn($a) => $a['code'] >= '5200' && $a['code'] < '5300');
        // Beban/Pendapatan Lain: sisanya
        $other = $expenses->filter(fn($a) => $a['code'] >= '5300');

        $totalRevenue = $revenue->sum('balance');
        $totalCogs    = $cogs->sum('balance');
        $grossProfit  = $totalRevenue - $totalCogs;
        $totalOpex    = $opex->sum('balance');
        $opIncome     = $grossProfit - $totalOpex;
        $totalOther   = $other->sum('balance');
        $netIncome    = $opIncome - $totalOther;

        return [
            'from'             => $from,
            'to'               => $to,
            'revenue'          => ['items' => $revenue->values(),  'total' => $totalRevenue],
            'cogs'             => ['items' => $cogs->values(),     'total' => $totalCogs],
            'gross_profit'     => $grossProfit,
            'opex'             => ['items' => $opex->values(),     'total' => $totalOpex],
            'operating_income' => $opIncome,
            'other_expense'    => ['items' => $other->values(),    'total' => $totalOther],
            'net_income'       => $netIncome,
        ];
    }

    // ─── Cash Flow Statement (Indirect Method) ────────────────────

    /**
     * Laporan Arus Kas metode tidak langsung (indirect method).
     *
     * Sections:
     *   operating  — dimulai dari net income, disesuaikan perubahan modal kerja
     *   investing  — perubahan aset tetap
     *   financing  — perubahan hutang jangka panjang & ekuitas
     *   net_change — perubahan kas bersih
     *   opening_cash / closing_cash
     */
    public function cashFlowStatement(int $tenantId, string $from, string $to): array
    {
        // Saldo awal (sebelum $from)
        $prevDate = date('Y-m-d', strtotime($from . ' -1 day'));

        $openingCash = $this->getCashBalance($tenantId, null, $prevDate);
        $closingCash = $this->getCashBalance($tenantId, null, $to);

        // Net income periode ini
        $netIncome = $this->calcNetIncome($tenantId, $from, $to);

        // Perubahan modal kerja (working capital changes)
        $wcChanges = $this->workingCapitalChanges($tenantId, $from, $to, $prevDate);

        // Arus kas dari aktivitas operasi
        $operatingTotal = $netIncome + $wcChanges['total'];

        // Arus kas dari aktivitas investasi (perubahan aset tetap)
        $investingItems = $this->investingActivities($tenantId, $from, $to);
        $investingTotal = collect($investingItems)->sum('amount');

        // Arus kas dari aktivitas pendanaan (perubahan hutang jangka panjang & ekuitas)
        $financingItems = $this->financingActivities($tenantId, $from, $to);
        $financingTotal = collect($financingItems)->sum('amount');

        $netChange = $operatingTotal + $investingTotal + $financingTotal;

        return [
            'from'         => $from,
            'to'           => $to,
            'operating'    => [
                'net_income'     => $netIncome,
                'wc_adjustments' => $wcChanges['items'],
                'total'          => $operatingTotal,
            ],
            'investing'    => [
                'items' => $investingItems,
                'total' => $investingTotal,
            ],
            'financing'    => [
                'items' => $financingItems,
                'total' => $financingTotal,
            ],
            'net_change'   => $netChange,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'reconciled'   => abs(($openingCash + $netChange) - $closingCash) < 1,
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Ambil semua akun non-header dengan saldo untuk periode tertentu.
     * Jika $from null → saldo akumulatif dari awal (untuk Balance Sheet).
     */
    private function getAccountBalances(int $tenantId, ?string $from, string $to): \Illuminate\Support\Collection
    {
        return ChartOfAccount::where('tenant_id', $tenantId)
            ->where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->map(function ($acc) use ($tenantId, $from, $to) {
                $query = JournalEntryLine::where('account_id', $acc->id)
                    ->whereHas('journalEntry', fn($q) => $q
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'posted')
                        ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                        ->whereDate('date', '<=', $to)
                    );

                $debit  = (float) $query->sum('debit');
                $credit = (float) $query->sum('credit');
                $balance = $acc->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;

                return [
                    'id'      => $acc->id,
                    'code'    => $acc->code,
                    'name'    => $acc->name,
                    'type'    => $acc->type,
                    'normal_balance' => $acc->normal_balance,
                    'debit'   => $debit,
                    'credit'  => $credit,
                    'balance' => $balance,
                ];
            })
            ->filter(fn($a) => abs($a['balance']) > 0.001);
    }

    /** Hitung net income (revenue - expense) untuk periode */
    private function calcNetIncome(int $tenantId, ?string $from, string $to): float
    {
        $accounts = $this->getAccountBalances($tenantId, $from, $to);
        $revenue  = $accounts->where('type', 'revenue')->sum('balance');
        $expense  = $accounts->where('type', 'expense')->sum('balance');
        return $revenue - $expense;
    }

    /** Saldo kas + bank (kode 1101, 1102) */
    private function getCashBalance(int $tenantId, ?string $from, string $to): float
    {
        $cashAccounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', ['1101', '1102'])
            ->pluck('id');

        if ($cashAccounts->isEmpty()) return 0;

        $debit  = (float) JournalEntryLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('tenant_id', $tenantId)
                ->where('status', 'posted')
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->whereDate('date', '<=', $to)
            )->sum('debit');

        $credit = (float) JournalEntryLine::whereIn('account_id', $cashAccounts)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('tenant_id', $tenantId)
                ->where('status', 'posted')
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->whereDate('date', '<=', $to)
            )->sum('credit');

        return $debit - $credit;
    }

    /**
     * Perubahan modal kerja untuk indirect method.
     * Kenaikan aset lancar (selain kas) = pengurangan kas (negatif)
     * Kenaikan kewajiban lancar = penambahan kas (positif)
     */
    private function workingCapitalChanges(int $tenantId, string $from, string $to, string $prevDate): array
    {
        $items = [];

        // Aset lancar non-kas (1103-1107)
        $wcAssetCodes = ['1103', '1104', '1105', '1106', '1107'];
        foreach ($wcAssetCodes as $code) {
            $acc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', $code)->first();
            if (! $acc) continue;

            $balancePrev = $acc->balance($tenantId, null, $prevDate);
            $balanceCurr = $acc->balance($tenantId, null, $to);
            $change = $balanceCurr - $balancePrev;

            if (abs($change) < 0.01) continue;

            // Kenaikan aset lancar = arus kas negatif (indirect method)
            $items[] = [
                'label'  => 'Perubahan ' . $acc->name,
                'amount' => -$change,
            ];
        }

        // Kewajiban lancar (2101-2107)
        $wcLiabCodes = ['2101', '2102', '2103', '2104', '2105', '2106', '2107'];
        foreach ($wcLiabCodes as $code) {
            $acc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', $code)->first();
            if (! $acc) continue;

            $balancePrev = $acc->balance($tenantId, null, $prevDate);
            $balanceCurr = $acc->balance($tenantId, null, $to);
            $change = $balanceCurr - $balancePrev;

            if (abs($change) < 0.01) continue;

            // Kenaikan kewajiban lancar = arus kas positif
            $items[] = [
                'label'  => 'Perubahan ' . $acc->name,
                'amount' => $change,
            ];
        }

        // Tambahkan beban penyusutan (non-cash expense, add back)
        $depAcc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', '5204')->first();
        if ($depAcc) {
            $depAmount = $depAcc->balance($tenantId, $from, $to);
            if ($depAmount > 0.01) {
                $items[] = ['label' => 'Tambah: Beban Penyusutan (non-kas)', 'amount' => $depAmount];
            }
        }

        return [
            'items' => $items,
            'total' => collect($items)->sum('amount'),
        ];
    }

    /** Arus kas investasi: perubahan aset tetap (kode 1201) */
    private function investingActivities(int $tenantId, string $from, string $to): array
    {
        $items = [];

        $fixedAssetAcc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', '1201')->first();
        if ($fixedAssetAcc) {
            $prevDate    = date('Y-m-d', strtotime($from . ' -1 day'));
            $balancePrev = $fixedAssetAcc->balance($tenantId, null, $prevDate);
            $balanceCurr = $fixedAssetAcc->balance($tenantId, null, $to);
            $change      = $balanceCurr - $balancePrev;

            if (abs($change) > 0.01) {
                $items[] = [
                    'label'  => $change > 0 ? 'Pembelian Aset Tetap' : 'Penjualan/Pelepasan Aset Tetap',
                    'amount' => -$change, // kenaikan aset = arus kas keluar
                ];
            }
        }

        return $items;
    }

    /** Arus kas pendanaan: perubahan hutang jangka panjang & ekuitas */
    private function financingActivities(int $tenantId, string $from, string $to): array
    {
        $items = [];
        $prevDate = date('Y-m-d', strtotime($from . ' -1 day'));

        $codes = [
            '2201' => 'Perubahan Hutang Bank Jangka Panjang',
            '3101' => 'Setoran Modal',
        ];

        foreach ($codes as $code => $label) {
            $acc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', $code)->first();
            if (! $acc) continue;

            $balancePrev = $acc->balance($tenantId, null, $prevDate);
            $balanceCurr = $acc->balance($tenantId, null, $to);
            $change      = $balanceCurr - $balancePrev;

            if (abs($change) > 0.01) {
                $items[] = ['label' => $label, 'amount' => $change];
            }
        }

        return $items;
    }
}
