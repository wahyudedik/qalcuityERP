<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\CompanyGroup;
use App\Models\IntercompanyTransaction;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Payable;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * ConsolidationService — Multi-company consolidated financial reporting.
 *
 * Generates consolidated P&L, Balance Sheet, and Cash Flow
 * with intercompany elimination.
 */
class ConsolidationService
{
    private function fmt(float $n): string
    {
        return ($n < 0 ? '-' : '') . 'Rp ' . number_format(abs($n), 0, ',', '.');
    }

    /**
     * Full consolidated report: P&L + Balance Sheet + summary.
     */
    public function consolidatedReport(CompanyGroup $group, string $period): array
    {
        $memberIds = $group->members->pluck('id')->toArray();
        if (empty($memberIds)) return $this->emptyReport();

        [$year, $month] = explode('-', $period);

        $pnl = $this->consolidatedPnL($memberIds, $year, $month, $group->id);
        $bs = $this->consolidatedBalanceSheet($memberIds, $year, $month);
        $elimination = $this->intercompanyElimination($group->id, $year, $month);

        return array_merge($pnl, [
            'balance_sheet' => $bs,
            'elimination'   => $elimination,
            'formatted'     => [
                'total_revenue' => $this->fmt($pnl['total_revenue']),
                'total_expense' => $this->fmt($pnl['total_expense']),
                'elimination'   => $this->fmt($elimination['total']),
                'cons_profit'   => $this->fmt($pnl['consolidated_profit']),
            ],
        ]);
    }

    /**
     * Consolidated Profit & Loss per member.
     */
    private function consolidatedPnL(array $memberIds, int $year, int $month, int $groupId): array
    {
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($memberIds as $tid) {
            $tenant = \App\Models\Tenant::find($tid);
            $name = $tenant?->name ?? "Tenant #{$tid}";

            // Try GL-based first (more accurate)
            $glRevenue = $this->glAccountSum($tid, 'revenue', $year, $month, 'credit');
            $glExpense = $this->glAccountSum($tid, 'expense', $year, $month, 'debit');
            $glCogs = $this->glAccountSum($tid, 'cogs', $year, $month, 'debit');

            if ($glRevenue > 0 || $glExpense > 0) {
                $rev = $glRevenue;
                $exp = $glExpense + $glCogs;
            } else {
                // Fallback to transaction-based
                $rev = (float) SalesOrder::where('tenant_id', $tid)
                    ->whereNotIn('status', ['cancelled'])
                    ->whereYear('date', $year)->whereMonth('date', $month)
                    ->sum('total');
                $exp = (float) Transaction::where('tenant_id', $tid)
                    ->where('type', 'expense')
                    ->whereYear('date', $year)->whereMonth('date', $month)
                    ->sum('amount');
            }

            $revenues[$tid] = ['name' => $name, 'amount' => $rev];
            $expenses[$tid] = ['name' => $name, 'amount' => $exp];
            $totalRevenue += $rev;
            $totalExpense += $exp;
        }

        $eliminationTotal = (float) IntercompanyTransaction::where('company_group_id', $groupId)
            ->where('status', 'posted')
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->sum('amount');

        return [
            'revenues'            => $revenues,
            'expenses'            => $expenses,
            'total_revenue'       => $totalRevenue,
            'total_expense'       => $totalExpense,
            'consolidated_profit' => $totalRevenue - $totalExpense - $eliminationTotal,
        ];
    }

    /**
     * Consolidated Balance Sheet — aggregate all members' GL balances.
     */
    private function consolidatedBalanceSheet(array $memberIds, int $year, int $month): array
    {
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        $categories = [
            'asset'     => ['label' => 'Aset', 'normal' => 'debit'],
            'liability' => ['label' => 'Kewajiban', 'normal' => 'credit'],
            'equity'    => ['label' => 'Ekuitas', 'normal' => 'credit'],
        ];

        $result = [];
        foreach ($categories as $type => $meta) {
            $total = 0;
            $perMember = [];

            foreach ($memberIds as $tid) {
                $tenant = \App\Models\Tenant::find($tid);
                $accounts = ChartOfAccount::where('tenant_id', $tid)
                    ->where('type', $type)
                    ->where('is_header', false)
                    ->where('is_active', true)
                    ->get();

                $memberTotal = 0;
                foreach ($accounts as $acc) {
                    $debit = (float) JournalEntryLine::where('account_id', $acc->id)
                        ->whereHas('journalEntry', fn($q) => $q
                            ->where('tenant_id', $tid)
                            ->where('status', 'posted')
                            ->where('date', '<=', $endDate))
                        ->sum('debit');
                    $credit = (float) JournalEntryLine::where('account_id', $acc->id)
                        ->whereHas('journalEntry', fn($q) => $q
                            ->where('tenant_id', $tid)
                            ->where('status', 'posted')
                            ->where('date', '<=', $endDate))
                        ->sum('credit');

                    $balance = $meta['normal'] === 'debit' ? $debit - $credit : $credit - $debit;
                    $memberTotal += $balance;
                }

                $perMember[] = ['name' => $tenant?->name ?? "#{$tid}", 'amount' => $memberTotal];
                $total += $memberTotal;
            }

            $result[$type] = [
                'label'      => $meta['label'],
                'total'      => $total,
                'per_member' => $perMember,
            ];
        }

        return $result;
    }

    /**
     * Intercompany elimination detail.
     */
    private function intercompanyElimination(int $groupId, int $year, int $month): array
    {
        $transactions = IntercompanyTransaction::where('company_group_id', $groupId)
            ->where('status', 'posted')
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->with(['fromTenant', 'toTenant'])
            ->get();

        $byType = $transactions->groupBy('type')->map(fn($items) => [
            'count'  => $items->count(),
            'amount' => $items->sum('amount'),
        ]);

        return [
            'total'      => (float) $transactions->sum('amount'),
            'by_type'    => $byType->toArray(),
            'items'      => $transactions->map(fn($t) => [
                'from'   => $t->fromTenant?->name ?? "#{$t->from_tenant_id}",
                'to'     => $t->toTenant?->name ?? "#{$t->to_tenant_id}",
                'type'   => $t->type,
                'amount' => (float) $t->amount,
                'ref'    => $t->reference,
            ])->toArray(),
        ];
    }

    /**
     * Sum GL account balances by type for a tenant in a period.
     */
    private function glAccountSum(int $tenantId, string $accountType, int $year, int $month, string $side): float
    {
        $accountIds = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('type', $accountType)
            ->where('is_header', false)
            ->where('is_active', true)
            ->pluck('id');

        if ($accountIds->isEmpty()) return 0;

        return (float) JournalEntryLine::whereIn('account_id', $accountIds)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('tenant_id', $tenantId)
                ->where('status', 'posted')
                ->whereYear('date', $year)
                ->whereMonth('date', $month))
            ->sum($side);
    }

    /**
     * Consolidated Cash Flow — operating, investing, financing.
     */
    public function consolidatedCashFlow(CompanyGroup $group, string $period): array
    {
        $memberIds = $group->members->pluck('id')->toArray();
        [$year, $month] = explode('-', $period);

        $cashCodes = ['1101', '1102'];
        $result = ['operating' => 0, 'investing' => 0, 'financing' => 0, 'per_member' => []];

        foreach ($memberIds as $tid) {
            $tenant = \App\Models\Tenant::find($tid);
            $cashAccounts = ChartOfAccount::where('tenant_id', $tid)
                ->whereIn('code', $cashCodes)->pluck('id');

            if ($cashAccounts->isEmpty()) continue;

            $inflow = (float) JournalEntryLine::whereIn('account_id', $cashAccounts)
                ->whereHas('journalEntry', fn($q) => $q
                    ->where('tenant_id', $tid)->where('status', 'posted')
                    ->whereYear('date', $year)->whereMonth('date', $month))
                ->sum('debit');

            $outflow = (float) JournalEntryLine::whereIn('account_id', $cashAccounts)
                ->whereHas('journalEntry', fn($q) => $q
                    ->where('tenant_id', $tid)->where('status', 'posted')
                    ->whereYear('date', $year)->whereMonth('date', $month))
                ->sum('credit');

            $net = $inflow - $outflow;
            $result['operating'] += $net;
            $result['per_member'][] = [
                'name'    => $tenant?->name ?? "#{$tid}",
                'inflow'  => $inflow,
                'outflow' => $outflow,
                'net'     => $net,
            ];
        }

        $result['net_change'] = $result['operating'] + $result['investing'] + $result['financing'];
        return $result;
    }

    /**
     * Multi-period trend for a group (last N months).
     */
    public function consolidatedTrend(CompanyGroup $group, int $months = 6): array
    {
        $memberIds = $group->members->pluck('id')->toArray();
        $trend = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $year = $date->year;
            $month = $date->month;
            $label = $date->format('M Y');

            $rev = 0;
            $exp = 0;
            foreach ($memberIds as $tid) {
                $rev += (float) SalesOrder::where('tenant_id', $tid)
                    ->whereNotIn('status', ['cancelled'])
                    ->whereYear('date', $year)->whereMonth('date', $month)
                    ->sum('total');
                $exp += (float) Transaction::where('tenant_id', $tid)
                    ->where('type', 'expense')
                    ->whereYear('date', $year)->whereMonth('date', $month)
                    ->sum('amount');
            }

            $trend[] = ['label' => $label, 'revenue' => $rev, 'expense' => $exp, 'profit' => $rev - $exp];
        }

        return $trend;
    }

    /**
     * Create an intercompany transaction.
     */
    public function createIntercompanyTransaction(
        CompanyGroup $group,
        int    $fromTenantId,
        int    $toTenantId,
        string $type,
        float  $amount,
        string $description,
        string $date
    ): IntercompanyTransaction {
        return IntercompanyTransaction::create([
            'company_group_id' => $group->id,
            'from_tenant_id'   => $fromTenantId,
            'to_tenant_id'     => $toTenantId,
            'type'             => $type,
            'amount'           => $amount,
            'description'      => $description,
            'date'             => $date,
            'reference'        => 'IC-' . strtoupper(substr(uniqid(), -6)),
            'currency_code'    => $group->currency_code ?? 'IDR',
            'status'           => 'pending',
        ]);
    }

    private function emptyReport(): array
    {
        return [
            'revenues' => [],
            'expenses' => [],
            'total_revenue' => 0,
            'total_expense' => 0,
            'consolidated_profit' => 0,
            'balance_sheet' => [],
            'elimination' => ['total' => 0, 'by_type' => [], 'items' => []],
            'formatted' => [
                'total_revenue' => 'Rp 0',
                'total_expense' => 'Rp 0',
                'elimination' => 'Rp 0',
                'cons_profit' => 'Rp 0',
            ],
        ];
    }
}
