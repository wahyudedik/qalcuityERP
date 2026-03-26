<?php

namespace App\Services;

use App\Models\CompanyGroup;
use App\Models\IntercompanyTransaction;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * ConsolidationService — Multi-company consolidated reporting.
 *
 * Generates consolidated P&L with intercompany elimination.
 */
class ConsolidationService
{
    /**
     * Generate consolidated report for a company group for a given period (Y-m).
     */
    public function consolidatedReport(CompanyGroup $group, string $period): array
    {
        [$year, $month] = explode('-', $period);
        $memberIds = $group->members->pluck('id')->toArray();

        if (empty($memberIds)) {
            return [
                'revenues' => [], 'expenses' => [],
                'total_revenue' => 0, 'total_expense' => 0,
                'elimination' => 0, 'consolidated_profit' => 0,
                'formatted' => [
                    'total_revenue' => 'Rp 0', 'total_expense' => 'Rp 0',
                    'elimination' => 'Rp 0', 'cons_profit' => 'Rp 0',
                ],
            ];
        }

        $fmt = fn($n) => 'Rp ' . number_format(abs((float) $n), 0, ',', '.');

        // Revenue per member
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($memberIds as $tid) {
            $tenant = $group->members->firstWhere('id', $tid);
            $rev = (float) SalesOrder::where('tenant_id', $tid)
                ->whereNotIn('status', ['cancelled'])
                ->whereYear('date', $year)->whereMonth('date', $month)
                ->sum('total');

            $exp = (float) Transaction::where('tenant_id', $tid)
                ->where('type', 'expense')
                ->whereYear('date', $year)->whereMonth('date', $month)
                ->sum('amount');

            $revenues[$tid] = ['name' => $tenant?->name ?? "Tenant #{$tid}", 'amount' => $rev];
            $expenses[$tid] = ['name' => $tenant?->name ?? "Tenant #{$tid}", 'amount' => $exp];
            $totalRevenue += $rev;
            $totalExpense += $exp;
        }

        // Intercompany elimination — sum of posted IC transactions in this period
        $elimination = (float) IntercompanyTransaction::where('company_group_id', $group->id)
            ->where('status', 'posted')
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->sum('amount');

        $consProfit = $totalRevenue - $totalExpense - $elimination;

        return [
            'revenues'           => $revenues,
            'expenses'           => $expenses,
            'total_revenue'      => $totalRevenue,
            'total_expense'      => $totalExpense,
            'elimination'        => $elimination,
            'consolidated_profit'=> $consProfit,
            'formatted'          => [
                'total_revenue' => $fmt($totalRevenue),
                'total_expense' => $fmt($totalExpense),
                'elimination'   => $fmt($elimination),
                'cons_profit'   => ($consProfit >= 0 ? '' : '-') . $fmt($consProfit),
            ],
        ];
    }

    /**
     * Create an intercompany transaction with proper reference.
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
}
