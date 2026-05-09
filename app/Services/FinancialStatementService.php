<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * FinancialStatementService
 *
 * Menghasilkan 3 laporan keuangan formal dari GL (journal entries):
 *  1. Balance Sheet (Neraca)
 *  2. Income Statement / P&L (Laporan Laba Rugi)
 *  3. Cash Flow Statement (Laporan Arus Kas — indirect method)
 *
 * SEMUA data bersumber dari JournalEntryLine (double-entry).
 * Tabel `transactions` TIDAK digunakan — menghindari inkonsistensi.
 */
class FinancialStatementService
{
    // ─── Balance Sheet ────────────────────────────────────────────

    public function balanceSheet(int $tenantId, string $asOf): array
    {
        $accounts = $this->getAccountBalances($tenantId, null, $asOf);

        $assets = $accounts->where('type', 'asset');
        $liabilities = $accounts->where('type', 'liability');
        $equity = $accounts->where('type', 'equity');

        $currentAssets = $assets->filter(fn ($a) => $a['code'] >= '1100' && $a['code'] < '1200');
        $nonCurrentAssets = $assets->filter(fn ($a) => $a['code'] >= '1200');
        $currentLiab = $liabilities->filter(fn ($a) => $a['code'] >= '2100' && $a['code'] < '2200');
        $longTermLiab = $liabilities->filter(fn ($a) => $a['code'] >= '2200');

        $totalAssets = $assets->sum('balance');
        $totalLiabilities = $liabilities->sum('balance');
        $totalEquity = $equity->sum('balance');

        // Net income from revenue - expense (already in $accounts)
        $revenue = $accounts->where('type', 'revenue')->sum('balance');
        $expense = $accounts->where('type', 'expense')->sum('balance');
        $netIncome = $revenue - $expense;

        $totalLiabEquity = $totalLiabilities + $totalEquity + $netIncome;

        // GL integrity check
        $integrity = $this->checkGlIntegrity($tenantId, $asOf);

        // Balance validation assertion (Bug 1.22)
        $difference = abs($totalAssets - ($totalLiabilities + $totalEquity + $netIncome));
        if ($difference > 0.01) {
            $balanceWarning = [
                'is_balanced' => false,
                'difference' => $difference,
                'message' => 'Persamaan akuntansi tidak seimbang. Selisih: '.number_format($difference, 2, ',', '.'),
            ];
        } else {
            $balanceWarning = ['is_balanced' => true];
        }

        return [
            'as_of' => $asOf,
            'assets' => [
                'current' => $currentAssets->values(),
                'non_current' => $nonCurrentAssets->values(),
                'total' => $totalAssets,
            ],
            'liabilities' => [
                'current' => $currentLiab->values(),
                'long_term' => $longTermLiab->values(),
                'total' => $totalLiabilities,
            ],
            'equity' => [
                'items' => $equity->values(),
                'total' => $totalEquity,
            ],
            'net_income' => $netIncome,
            'total_l_e' => $totalLiabEquity,
            'total_assets' => $totalAssets,
            'is_balanced' => abs($totalAssets - $totalLiabEquity) < 1,
            'balance_warning' => $balanceWarning,
            'gl_integrity' => $integrity,
        ];
    }

    // ─── Income Statement (P&L) ───────────────────────────────────

    public function incomeStatement(int $tenantId, string $from, string $to): array
    {
        $accounts = $this->getAccountBalances($tenantId, $from, $to);

        $revenue = $accounts->where('type', 'revenue');
        $expenses = $accounts->where('type', 'expense');

        $cogs = $expenses->filter(fn ($a) => $a['code'] >= '5100' && $a['code'] < '5200');
        $opex = $expenses->filter(fn ($a) => $a['code'] >= '5200' && $a['code'] < '5300');
        $other = $expenses->filter(fn ($a) => $a['code'] >= '5300');

        $totalRevenue = $revenue->sum('balance');
        $totalCogs = $cogs->sum('balance');
        $grossProfit = $totalRevenue - $totalCogs;
        $totalOpex = $opex->sum('balance');
        $opIncome = $grossProfit - $totalOpex;
        $totalOther = $other->sum('balance');
        $netIncome = $opIncome - $totalOther;

        return [
            'from' => $from,
            'to' => $to,
            'revenue' => ['items' => $revenue->values(), 'total' => $totalRevenue],
            'cogs' => ['items' => $cogs->values(), 'total' => $totalCogs],
            'gross_profit' => $grossProfit,
            'opex' => ['items' => $opex->values(), 'total' => $totalOpex],
            'operating_income' => $opIncome,
            'other_expense' => ['items' => $other->values(), 'total' => $totalOther],
            'net_income' => $netIncome,
        ];
    }

    // ─── Cash Flow Statement (Indirect Method) ────────────────────

    public function cashFlowStatement(int $tenantId, string $from, string $to): array
    {
        $prevDate = date('Y-m-d', strtotime($from.' -1 day'));

        $openingCash = $this->getCashBalance($tenantId, null, $prevDate);
        $closingCash = $this->getCashBalance($tenantId, null, $to);

        $netIncome = $this->calcNetIncome($tenantId, $from, $to);
        $wcChanges = $this->workingCapitalChanges($tenantId, $from, $to, $prevDate);

        $operatingTotal = $netIncome + $wcChanges['total'];

        $investingItems = $this->investingActivities($tenantId, $from, $to);
        $investingTotal = collect($investingItems)->sum('amount');

        $financingItems = $this->financingActivities($tenantId, $from, $to);
        $financingTotal = collect($financingItems)->sum('amount');

        $netChange = $operatingTotal + $investingTotal + $financingTotal;

        return [
            'from' => $from,
            'to' => $to,
            'operating' => [
                'net_income' => $netIncome,
                'wc_adjustments' => $wcChanges['items'],
                'total' => $operatingTotal,
            ],
            'investing' => ['items' => $investingItems, 'total' => $investingTotal],
            'financing' => ['items' => $financingItems, 'total' => $financingTotal],
            'net_change' => $netChange,
            'opening_cash' => $openingCash,
            'closing_cash' => $closingCash,
            'reconciled' => abs(($openingCash + $netChange) - $closingCash) < 1,
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Get all account balances — SINGLE aggregate query (was N+1 per account).
     * Groups debit/credit sums by account_id, then maps to COA metadata.
     */
    private function getAccountBalances(int $tenantId, ?string $from, string $to): Collection
    {
        // 1. Get all non-header active accounts for this tenant
        $coaMap = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('is_header', false)
            ->where('is_active', true)
            ->orderBy('code')
            ->get()
            ->keyBy('id');

        if ($coaMap->isEmpty()) {
            return collect();
        }

        // 2. Single aggregate query: SUM debit/credit grouped by account_id
        $sums = JournalEntryLine::select(
            'journal_entry_lines.account_id',
            DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
            DB::raw('SUM(journal_entry_lines.credit) as total_credit')
        )
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenantId)
            ->where('journal_entries.status', 'posted')
            ->when($from, fn ($q) => $q->whereDate('journal_entries.date', '>=', $from))
            ->whereDate('journal_entries.date', '<=', $to)
            ->whereIn('journal_entry_lines.account_id', $coaMap->keys())
            ->groupBy('journal_entry_lines.account_id')
            ->get()
            ->keyBy('account_id');

        // 3. Map to result array
        return $coaMap->map(function ($acc) use ($sums) {
            $row = $sums[$acc->id] ?? null;
            $debit = (float) ($row?->total_debit ?? 0);
            $credit = (float) ($row?->total_credit ?? 0);
            $balance = $acc->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;

            return [
                'id' => $acc->id,
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'normal_balance' => $acc->normal_balance,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $balance,
            ];
        })->filter(fn ($a) => abs($a['balance']) > 0.001);
    }

    /** Net income = revenue balance - expense balance */
    private function calcNetIncome(int $tenantId, ?string $from, string $to): float
    {
        $accounts = $this->getAccountBalances($tenantId, $from, $to);

        return $accounts->where('type', 'revenue')->sum('balance')
            - $accounts->where('type', 'expense')->sum('balance');
    }

    /** Cash + Bank balance (codes 1101, 1102) — single query */
    private function getCashBalance(int $tenantId, ?string $from, string $to): float
    {
        $cashAccountIds = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', ['1101', '1102'])
            ->pluck('id');

        if ($cashAccountIds->isEmpty()) {
            return 0;
        }

        $row = JournalEntryLine::select(
            DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
            DB::raw('SUM(journal_entry_lines.credit) as total_credit')
        )
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenantId)
            ->where('journal_entries.status', 'posted')
            ->when($from, fn ($q) => $q->whereDate('journal_entries.date', '>=', $from))
            ->whereDate('journal_entries.date', '<=', $to)
            ->whereIn('journal_entry_lines.account_id', $cashAccountIds)
            ->first();

        return (float) ($row?->total_debit ?? 0) - (float) ($row?->total_credit ?? 0);
    }

    /**
     * Working capital changes for indirect cash flow method.
     * Batch query for all WC accounts instead of per-code queries.
     */
    private function workingCapitalChanges(int $tenantId, string $from, string $to, string $prevDate): array
    {
        $items = [];

        // Batch: get balances for all WC accounts at both dates
        $wcCodes = ['1103', '1104', '1105', '1106', '1107', '2101', '2102', '2103', '2104', '2105', '2106', '2107', '5204'];
        $wcAccounts = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('code', $wcCodes)
            ->get()
            ->keyBy('code');

        if ($wcAccounts->isEmpty()) {
            return ['items' => [], 'total' => 0];
        }

        // Get balances at prevDate (single query)
        $prevBalances = $this->batchAccountBalances($tenantId, $wcAccounts->pluck('id'), null, $prevDate);
        // Get balances at $to (single query)
        $currBalances = $this->batchAccountBalances($tenantId, $wcAccounts->pluck('id'), null, $to);

        // Current assets (non-cash): increase = cash outflow (negative)
        foreach (['1103', '1104', '1105', '1106', '1107'] as $code) {
            $acc = $wcAccounts[$code] ?? null;
            if (! $acc) {
                continue;
            }

            $prev = (float) ($prevBalances[$acc->id] ?? 0);
            $curr = (float) ($currBalances[$acc->id] ?? 0);
            $change = $curr - $prev;

            if (abs($change) < 0.01) {
                continue;
            }
            $items[] = ['label' => 'Perubahan '.$acc->name, 'amount' => -$change];
        }

        // Current liabilities: increase = cash inflow (positive)
        foreach (['2101', '2102', '2103', '2104', '2105', '2106', '2107'] as $code) {
            $acc = $wcAccounts[$code] ?? null;
            if (! $acc) {
                continue;
            }

            $prev = (float) ($prevBalances[$acc->id] ?? 0);
            $curr = (float) ($currBalances[$acc->id] ?? 0);
            $change = $curr - $prev;

            if (abs($change) < 0.01) {
                continue;
            }
            $items[] = ['label' => 'Perubahan '.$acc->name, 'amount' => $change];
        }

        // Depreciation add-back (non-cash expense) — from period balances
        $depAcc = $wcAccounts['5204'] ?? null;
        if ($depAcc) {
            $depRow = $this->batchAccountBalances($tenantId, collect([$depAcc->id]), $from, $to);
            $depAmount = (float) ($depRow[$depAcc->id] ?? 0);
            if ($depAmount > 0.01) {
                $items[] = ['label' => 'Tambah: Beban Penyusutan (non-kas)', 'amount' => $depAmount];
            }
        }

        return ['items' => $items, 'total' => collect($items)->sum('amount')];
    }

    /** Investing activities: fixed asset changes */
    private function investingActivities(int $tenantId, string $from, string $to): array
    {
        $items = [];
        $prevDate = date('Y-m-d', strtotime($from.' -1 day'));

        $fixedAssetAcc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', '1201')->first();
        if ($fixedAssetAcc) {
            $prev = $this->batchAccountBalances($tenantId, collect([$fixedAssetAcc->id]), null, $prevDate);
            $curr = $this->batchAccountBalances($tenantId, collect([$fixedAssetAcc->id]), null, $to);
            $change = (float) ($curr[$fixedAssetAcc->id] ?? 0) - (float) ($prev[$fixedAssetAcc->id] ?? 0);

            if (abs($change) > 0.01) {
                $items[] = [
                    'label' => $change > 0 ? 'Pembelian Aset Tetap' : 'Penjualan/Pelepasan Aset Tetap',
                    'amount' => -$change,
                ];
            }
        }

        return $items;
    }

    /** Financing activities: long-term debt & equity changes */
    private function financingActivities(int $tenantId, string $from, string $to): array
    {
        $items = [];
        $prevDate = date('Y-m-d', strtotime($from.' -1 day'));

        $codes = ['2201' => 'Perubahan Hutang Bank Jangka Panjang', '3101' => 'Setoran Modal'];

        foreach ($codes as $code => $label) {
            $acc = ChartOfAccount::where('tenant_id', $tenantId)->where('code', $code)->first();
            if (! $acc) {
                continue;
            }

            $prev = $this->batchAccountBalances($tenantId, collect([$acc->id]), null, $prevDate);
            $curr = $this->batchAccountBalances($tenantId, collect([$acc->id]), null, $to);
            $change = (float) ($curr[$acc->id] ?? 0) - (float) ($prev[$acc->id] ?? 0);

            if (abs($change) > 0.01) {
                $items[] = ['label' => $label, 'amount' => $change];
            }
        }

        return $items;
    }

    /**
     * Batch account balances — single query for multiple account IDs.
     * Returns [account_id => balance] map.
     */
    private function batchAccountBalances(int $tenantId, Collection $accountIds, ?string $from, string $to): array
    {
        if ($accountIds->isEmpty()) {
            return [];
        }

        $rows = JournalEntryLine::select(
            'journal_entry_lines.account_id',
            DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
            DB::raw('SUM(journal_entry_lines.credit) as total_credit')
        )
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenantId)
            ->where('journal_entries.status', 'posted')
            ->when($from, fn ($q) => $q->whereDate('journal_entries.date', '>=', $from))
            ->whereDate('journal_entries.date', '<=', $to)
            ->whereIn('journal_entry_lines.account_id', $accountIds)
            ->groupBy('journal_entry_lines.account_id')
            ->get();

        // BUG-REP-003 FIX: Scope ChartOfAccount by tenant_id to prevent cross-tenant data leak
        $coaMap = ChartOfAccount::where('tenant_id', $tenantId)
            ->whereIn('id', $accountIds)
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($rows as $row) {
            $acc = $coaMap[$row->account_id] ?? null;
            if (! $acc) {
                continue;
            }
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;
            $result[$row->account_id] = $acc->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
        }

        return $result;
    }

    /**
     * GL Integrity Check — verify all posted journals are balanced.
     * Returns summary for display in reports.
     */
    public function checkGlIntegrity(int $tenantId, string $asOf): array
    {
        // Check: total debit = total credit across all posted journals
        $totals = JournalEntryLine::select(
            DB::raw('SUM(journal_entry_lines.debit) as total_debit'),
            DB::raw('SUM(journal_entry_lines.credit) as total_credit')
        )
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->where('journal_entries.tenant_id', $tenantId)
            ->where('journal_entries.status', 'posted')
            ->whereDate('journal_entries.date', '<=', $asOf)
            ->first();

        $totalDebit = (float) ($totals?->total_debit ?? 0);
        $totalCredit = (float) ($totals?->total_credit ?? 0);
        $diff = abs($totalDebit - $totalCredit);

        // Find unbalanced journals (if any)
        $unbalancedCount = 0;
        if ($diff > 0.01) {
            $unbalancedCount = JournalEntry::where('tenant_id', $tenantId)
                ->where('status', 'posted')
                ->whereDate('date', '<=', $asOf)
                ->whereRaw('(SELECT SUM(debit) FROM journal_entry_lines WHERE journal_entry_id = journal_entries.id) != (SELECT SUM(credit) FROM journal_entry_lines WHERE journal_entry_id = journal_entries.id)')
                ->count();
        }

        return [
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'difference' => $diff,
            'is_balanced' => $diff < 0.01,
            'unbalanced_count' => $unbalancedCount,
            'journal_count' => JournalEntry::where('tenant_id', $tenantId)->where('status', 'posted')->whereDate('date', '<=', $asOf)->count(),
        ];
    }
}
