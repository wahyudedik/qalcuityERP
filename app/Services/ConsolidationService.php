<?php

namespace App\Services;

use App\Models\CompanyGroup;
use App\Models\IntercompanyTransaction;
use App\Models\SalesOrder;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

/**
 * ConsolidationService — Task 55
 * Laporan konsolidasi keuangan antar perusahaan dalam satu grup.
 */
class ConsolidationService
{
    /**
     * Laporan konsolidasi: gabungkan data keuangan semua member grup.
     */
    public function consolidatedReport(CompanyGroup $group, string $period): array
    {
        $parts = explode('-', $period);
        $year  = $parts[0];
        $month = (int) ($parts[1] ?? 0);

        $tenantIds = $group->members()->pluck('id');
        if ($tenantIds->isEmpty()) return [];

        $fmt = fn($n) => 'Rp ' . number_format(abs($n ?? 0), 0, ',', '.');

        // Revenue per tenant
        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($tenantIds as $tid) {
            $query = SalesOrder::where('tenant_id', $tid)
                ->whereNotIn('status', ['cancelled']);

            if ($month > 0) {
                $query->whereYear('date', $year)->whereMonth('date', $month);
            } else {
                $query->whereYear('date', $year);
            }

            $rev = (float) $query->sum('total');

            $expQuery = Transaction::where('tenant_id', $tid)->where('type', 'expense');
            if ($month > 0) {
                $expQuery->whereYear('date', $year)->whereMonth('date', $month);
            } else {
                $expQuery->whereYear('date', $year);
            }
            $exp = (float) $expQuery->sum('amount');

            $tenant = \App\Models\Tenant::find($tid);
            $revenues[$tid] = ['name' => $tenant?->name, 'amount' => $rev];
            $expenses[$tid] = ['name' => $tenant?->name, 'amount' => $exp];
            $totalRevenue += $rev;
            $totalExpense += $exp;
        }

        // Eliminasi transaksi intercompany
        $intercompanyElimination = (float) IntercompanyTransaction::where('company_group_id', $group->id)
            ->where('status', 'posted')
            ->when($month > 0, fn($q) => $q->whereYear('date', $year)->whereMonth('date', $month))
            ->when($month === 0, fn($q) => $q->whereYear('date', $year))
            ->sum('amount');

        $consolidatedRevenue = $totalRevenue - $intercompanyElimination;
        $consolidatedProfit  = $consolidatedRevenue - $totalExpense;

        return [
            'group_name'              => $group->name,
            'period'                  => $period,
            'member_count'            => $tenantIds->count(),
            'revenues'                => $revenues,
            'expenses'                => $expenses,
            'total_revenue'           => $totalRevenue,
            'total_expense'           => $totalExpense,
            'intercompany_elimination' => $intercompanyElimination,
            'consolidated_revenue'    => $consolidatedRevenue,
            'consolidated_profit'     => $consolidatedProfit,
            'profit_margin'           => $consolidatedRevenue > 0
                ? round($consolidatedProfit / $consolidatedRevenue * 100, 1)
                : 0,
            'formatted' => [
                'total_revenue'    => $fmt($totalRevenue),
                'total_expense'    => $fmt($totalExpense),
                'elimination'      => $fmt($intercompanyElimination),
                'cons_revenue'     => $fmt($consolidatedRevenue),
                'cons_profit'      => ($consolidatedProfit >= 0 ? '' : '-') . $fmt($consolidatedProfit),
            ],
        ];
    }

    /**
     * Buat transaksi intercompany.
     */
    public function createIntercompanyTransaction(
        CompanyGroup $group,
        int $fromTenantId,
        int $toTenantId,
        string $type,
        float $amount,
        string $description,
        string $date
    ): IntercompanyTransaction {
        $ref = 'IC-' . strtoupper($type) . '-' . now()->format('YmdHis');

        return IntercompanyTransaction::create([
            'company_group_id' => $group->id,
            'from_tenant_id'   => $fromTenantId,
            'to_tenant_id'     => $toTenantId,
            'type'             => $type,
            'reference'        => $ref,
            'amount'           => $amount,
            'currency_code'    => $group->currency_code,
            'description'      => $description,
            'status'           => 'pending',
            'date'             => $date,
        ]);
    }
}
