<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\Invoice;
use App\Models\InvoiceInstallment;
use App\Models\JournalEntryLine;
use App\Models\Payable;
use Carbon\Carbon;

class CashFlowProjectionService
{
    /**
     * Project cash flow for the next $days days.
     *
     * Returns:
     *  - opening_balance: float
     *  - weeks: array of weekly buckets
     *  - daily: array of daily entries (date => [inflow, outflow, net, balance])
     *  - totals: [inflow, outflow, net]
     *  - alerts: array of deficit/low-cash warnings
     *  - low_cash_threshold: float
     */
    public function project(int $tenantId, int $days = 90): array
    {
        $today = Carbon::today();
        $endDate = $today->copy()->addDays($days - 1);

        // Opening cash balance from GL (kas/bank accounts)
        $openingBalance = $this->getOpeningCashBalance($tenantId);

        // Build daily map
        $dailyMap = $this->buildDailyMap($tenantId, $today, $endDate);

        // Compute running balance
        $runningBalance = $openingBalance;
        $daily = [];
        $totalInflow = 0;
        $totalOutflow = 0;

        for ($d = 0; $d < $days; $d++) {
            $date = $today->copy()->addDays($d)->toDateString();
            $inflow = $dailyMap[$date]['inflow'] ?? 0;
            $outflow = $dailyMap[$date]['outflow'] ?? 0;
            $net = $inflow - $outflow;

            $runningBalance += $net;
            $totalInflow += $inflow;
            $totalOutflow += $outflow;

            $daily[$date] = [
                'inflow' => $inflow,
                'outflow' => $outflow,
                'net' => $net,
                'balance' => $runningBalance,
            ];
        }

        // Group into weeks
        $weeks = $this->groupIntoWeeks($daily, $today);

        // Alerts
        $lowCashThreshold = max($openingBalance * 0.1, 1_000_000); // 10% of opening or 1jt
        $alerts = $this->buildAlerts($daily, $lowCashThreshold);

        return [
            'opening_balance' => $openingBalance,
            'weeks' => $weeks,
            'daily' => $daily,
            'totals' => [
                'inflow' => $totalInflow,
                'outflow' => $totalOutflow,
                'net' => $totalInflow - $totalOutflow,
            ],
            'alerts' => $alerts,
            'low_cash_threshold' => $lowCashThreshold,
            'days' => $days,
            'generated_at' => now()->toDateTimeString(),
        ];
    }

    // ─── Private helpers ──────────────────────────────────────────

    private function getOpeningCashBalance(int $tenantId): float
    {
        // Sum debit - credit for cash/bank type accounts (type = 'asset', name contains kas/bank)
        $cashAccountIds = ChartOfAccount::where('tenant_id', $tenantId)
            ->where('type', 'asset')
            ->where(function ($q) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%kas%'])
                    ->orWhereRaw('LOWER(name) LIKE ?', ['%bank%'])
                    ->orWhereRaw('LOWER(code) LIKE ?', ['1-1%']); // typical cash codes
            })
            ->pluck('id');

        if ($cashAccountIds->isEmpty()) {
            return 0;
        }

        $debit = JournalEntryLine::whereIn('account_id', $cashAccountIds)->sum('debit');
        $credit = JournalEntryLine::whereIn('account_id', $cashAccountIds)->sum('credit');

        return max(0, (float) $debit - (float) $credit);
    }

    private function buildDailyMap(int $tenantId, Carbon $from, Carbon $to): array
    {
        $map = [];

        // AR inflows: unpaid/partial invoices grouped by due_date
        Invoice::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from, $to])
            ->selectRaw('due_date, SUM(remaining_amount) as total')
            ->groupBy('due_date')
            ->get()
            ->each(function ($row) use (&$map) {
                $date = Carbon::parse($row->due_date)->toDateString();
                $map[$date]['inflow'] = ($map[$date]['inflow'] ?? 0) + (float) $row->total;
            });

        // AR inflows: installments
        InvoiceInstallment::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from, $to])
            ->selectRaw('due_date, SUM(amount - COALESCE(paid_amount,0)) as total')
            ->groupBy('due_date')
            ->get()
            ->each(function ($row) use (&$map) {
                $date = Carbon::parse($row->due_date)->toDateString();
                $map[$date]['inflow'] = ($map[$date]['inflow'] ?? 0) + (float) $row->total;
            });

        // AP outflows: unpaid/partial payables grouped by due_date
        Payable::where('tenant_id', $tenantId)
            ->whereIn('status', ['unpaid', 'partial'])
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$from, $to])
            ->selectRaw('due_date, SUM(remaining_amount) as total')
            ->groupBy('due_date')
            ->get()
            ->each(function ($row) use (&$map) {
                $date = Carbon::parse($row->due_date)->toDateString();
                $map[$date]['outflow'] = ($map[$date]['outflow'] ?? 0) + (float) $row->total;
            });

        return $map;
    }

    private function groupIntoWeeks(array $daily, Carbon $today): array
    {
        $weeks = [];
        $dates = array_keys($daily);

        foreach ($dates as $date) {
            $d = Carbon::parse($date);
            $weekNum = (int) $d->diffInWeeks($today);
            $weekKey = 'week_'.$weekNum;

            if (! isset($weeks[$weekKey])) {
                $weeks[$weekKey] = [
                    'label' => 'Minggu '.($weekNum + 1).' ('.$d->copy()->startOfWeek()->format('d M').' - '.$d->copy()->endOfWeek()->format('d M Y').')',
                    'inflow' => 0,
                    'outflow' => 0,
                    'net' => 0,
                    'balance' => 0, // end-of-week balance
                ];
            }

            $weeks[$weekKey]['inflow'] += $daily[$date]['inflow'];
            $weeks[$weekKey]['outflow'] += $daily[$date]['outflow'];
            $weeks[$weekKey]['net'] += $daily[$date]['net'];
            $weeks[$weekKey]['balance'] = $daily[$date]['balance']; // last day of week
        }

        return array_values($weeks);
    }

    private function buildAlerts(array $daily, float $threshold): array
    {
        $alerts = [];
        $deficitStart = null;
        $deficitDays = 0;

        foreach ($daily as $date => $data) {
            if ($data['balance'] < 0) {
                if ($deficitStart === null) {
                    $deficitStart = $date;
                }
                $deficitDays++;
            } else {
                if ($deficitStart !== null) {
                    $alerts[] = [
                        'type' => 'deficit',
                        'message' => "Saldo negatif selama {$deficitDays} hari mulai {$deficitStart}",
                        'date' => $deficitStart,
                        'days' => $deficitDays,
                    ];
                    $deficitStart = null;
                    $deficitDays = 0;
                }

                if ($data['balance'] < $threshold && $data['balance'] >= 0) {
                    $alerts[] = [
                        'type' => 'low_cash',
                        'message' => 'Saldo kas rendah pada '.Carbon::parse($date)->format('d M Y').' (Rp '.number_format($data['balance'], 0, ',', '.').')',
                        'date' => $date,
                        'days' => 0,
                    ];
                }
            }
        }

        // Close open deficit streak
        if ($deficitStart !== null) {
            $alerts[] = [
                'type' => 'deficit',
                'message' => "Saldo negatif selama {$deficitDays} hari mulai {$deficitStart}",
                'date' => $deficitStart,
                'days' => $deficitDays,
            ];
        }

        // Deduplicate low_cash (keep only first 5)
        $lowCash = array_filter($alerts, fn ($a) => $a['type'] === 'low_cash');
        $deficit = array_filter($alerts, fn ($a) => $a['type'] === 'deficit');

        return array_values(array_merge(
            array_values($deficit),
            array_slice(array_values($lowCash), 0, 5)
        ));
    }
}
