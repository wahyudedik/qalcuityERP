<?php

namespace App\Services;

use App\Models\Budget;
use Illuminate\Support\Collection;

/**
 * BudgetAiService — AI contextual untuk fitur Budget.
 *
 * 1. predictOverrun()   — prediksi apakah budget akan overrun berdasarkan tren realisasi
 * 2. suggestAllocation()— suggest alokasi budget berdasarkan histori tahun lalu
 */
class BudgetAiService
{
    // ─── 1. Overrun Prediction ────────────────────────────────────

    /**
     * Prediksi overrun untuk setiap budget di periode tertentu.
     *
     * Strategi:
     * - Hitung burn rate harian (realized / hari yang sudah lewat di bulan ini)
     * - Proyeksikan ke akhir bulan
     * - Bandingkan dengan amount
     * - Tambahkan konteks histori: apakah bulan-bulan sebelumnya juga over?
     *
     * Return: array keyed by budget_id => [
     *   'risk'          => 'high'|'medium'|'low'|'safe',
     *   'projected'     => float,   // proyeksi realisasi akhir bulan
     *   'overrun_amount'=> float,   // selisih proyeksi vs anggaran (positif = over)
     *   'message'       => string,
     *   'history_over'  => int,     // berapa bulan terakhir over budget
     * ]
     */
    public function predictOverrun(int $tenantId, string $period, Collection $budgets): array
    {
        if ($budgets->isEmpty()) return [];

        [$year, $month] = explode('-', $period);
        $daysInMonth  = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        $today        = now();
        $periodDate   = \Carbon\Carbon::createFromDate($year, $month, 1);

        // Hari yang sudah lewat di bulan ini (min 1 untuk hindari div/0)
        $daysPassed = $periodDate->isSameMonth($today)
            ? max(1, $today->day)
            : $daysInMonth;

        $results = [];

        foreach ($budgets as $budget) {
            if ($budget->amount <= 0) continue;

            // Burn rate harian
            $burnRate  = $budget->realized / $daysPassed;
            $projected = $burnRate * $daysInMonth;
            $overrun   = $projected - $budget->amount;
            $pct       = round($projected / $budget->amount * 100, 1);

            // Histori: berapa bulan terakhir (6 bulan) yang over budget
            $historyOver = $this->countHistoryOverruns($tenantId, $budget->name, $period, 6);

            // Risk level
            $risk = match (true) {
                $pct >= 110                          => 'high',
                $pct >= 90 || $historyOver >= 3      => 'medium',
                $pct >= 75 || $historyOver >= 2      => 'low',
                default                              => 'safe',
            };

            $projFmt = 'Rp ' . number_format($projected, 0, ',', '.');
            $budFmt  = 'Rp ' . number_format($budget->amount, 0, ',', '.');

            $message = match ($risk) {
                'high'   => "Proyeksi akhir bulan {$projFmt} ({$pct}% dari anggaran). Kemungkinan besar overrun.",
                'medium' => "Proyeksi {$projFmt} mendekati batas anggaran {$budFmt}. Perlu dipantau.",
                'low'    => "Tren realisasi normal, proyeksi {$projFmt}. Masih dalam batas aman.",
                default  => "Realisasi terkendali. Proyeksi {$projFmt} dari anggaran {$budFmt}.",
            };

            if ($historyOver >= 2) {
                $message .= " (Over budget {$historyOver}x dalam 6 bulan terakhir.)";
            }

            $results[$budget->id] = [
                'risk'           => $risk,
                'projected'      => round($projected, 2),
                'overrun_amount' => round($overrun, 2),
                'pct'            => $pct,
                'message'        => $message,
                'history_over'   => $historyOver,
            ];
        }

        return $results;
    }

    /**
     * Hitung berapa kali budget dengan nama tertentu over di N bulan terakhir.
     */
    private function countHistoryOverruns(int $tenantId, string $name, string $currentPeriod, int $months): int
    {
        $periods = [];
        $date = \Carbon\Carbon::createFromFormat('Y-m', $currentPeriod)->subMonth();
        for ($i = 0; $i < $months; $i++) {
            $periods[] = $date->format('Y-m');
            $date->subMonth();
        }

        return Budget::where('tenant_id', $tenantId)
            ->where('name', $name)
            ->whereIn('period', $periods)
            ->where('status', 'active')
            ->whereColumn('realized', '>', 'amount')
            ->count();
    }

    // ─── 2. Allocation Suggestion ─────────────────────────────────

    /**
     * Suggest alokasi budget untuk periode tertentu berdasarkan histori tahun lalu.
     *
     * Strategi:
     * - Ambil semua budget aktif dari periode yang sama tahun lalu
     * - Hitung rata-rata realisasi aktual (bukan anggaran) sebagai basis
     * - Tambahkan buffer 10% sebagai saran
     * - Jika tidak ada histori, gunakan rata-rata 3 bulan terakhir
     *
     * Return: [
     *   ['name', 'department', 'category', 'suggested_amount', 'basis_amount',
     *    'basis', 'confidence', 'last_year_realized', 'last_year_amount']
     * ]
     */
    public function suggestAllocation(int $tenantId, string $period): array
    {
        [$year, $month] = explode('-', $period);

        // Periode yang sama tahun lalu
        $lastYearPeriod = ($year - 1) . '-' . $month;

        $lastYear = Budget::where('tenant_id', $tenantId)
            ->where('period', $lastYearPeriod)
            ->where('status', 'active')
            ->get();

        if ($lastYear->isNotEmpty()) {
            return $this->suggestFromLastYear($tenantId, $lastYear, $period);
        }

        // Fallback: rata-rata 3 bulan terakhir
        return $this->suggestFromRecentMonths($tenantId, $period);
    }

    /**
     * Suggest berdasarkan realisasi tahun lalu + buffer 10%.
     */
    private function suggestFromLastYear(int $tenantId, Collection $lastYear, string $period): array
    {
        $suggestions = [];

        foreach ($lastYear as $budget) {
            $basis  = $budget->realized > 0 ? $budget->realized : $budget->amount;
            $buffer = $basis * 0.10;
            $suggested = round($basis + $buffer, -3); // bulatkan ke ribuan

            // Cek apakah sudah ada budget untuk periode ini
            $existing = Budget::where('tenant_id', $tenantId)
                ->where('name', $budget->name)
                ->where('period', $period)
                ->where('status', 'active')
                ->first();

            $suggestions[] = [
                'name'               => $budget->name,
                'department'         => $budget->department,
                'category'           => $budget->category,
                'suggested_amount'   => $suggested,
                'basis_amount'       => $basis,
                'confidence'         => $budget->realized > 0 ? 'high' : 'medium',
                'basis'              => $budget->realized > 0
                    ? 'Realisasi tahun lalu + buffer 10%'
                    : 'Anggaran tahun lalu + buffer 10% (realisasi tidak tersedia)',
                'last_year_realized' => $budget->realized,
                'last_year_amount'   => $budget->amount,
                'already_exists'     => $existing !== null,
                'existing_amount'    => $existing?->amount,
            ];
        }

        return $suggestions;
    }

    /**
     * Fallback: rata-rata realisasi 3 bulan terakhir + buffer 5%.
     */
    private function suggestFromRecentMonths(int $tenantId, string $period): array
    {
        $recentPeriods = [];
        $date = \Carbon\Carbon::createFromFormat('Y-m', $period)->subMonth();
        for ($i = 0; $i < 3; $i++) {
            $recentPeriods[] = $date->format('Y-m');
            $date->subMonth();
        }

        $recent = Budget::where('tenant_id', $tenantId)
            ->whereIn('period', $recentPeriods)
            ->where('status', 'active')
            ->get()
            ->groupBy('name');

        if ($recent->isEmpty()) return [];

        $suggestions = [];

        foreach ($recent as $name => $items) {
            $avgRealized = $items->avg('realized');
            $avgAmount   = $items->avg('amount');
            $basis       = $avgRealized > 0 ? $avgRealized : $avgAmount;
            $suggested   = round($basis * 1.05, -3);

            $existing = Budget::where('tenant_id', $tenantId)
                ->where('name', $name)
                ->where('period', $period)
                ->where('status', 'active')
                ->first();

            $suggestions[] = [
                'name'               => $name,
                'department'         => $items->first()->department,
                'category'           => $items->first()->category,
                'suggested_amount'   => $suggested,
                'basis_amount'       => round($basis, 2),
                'confidence'         => 'low',
                'basis'              => 'Rata-rata ' . count($recentPeriods) . ' bulan terakhir + buffer 5%',
                'last_year_realized' => null,
                'last_year_amount'   => null,
                'already_exists'     => $existing !== null,
                'existing_amount'    => $existing?->amount,
            ];
        }

        return $suggestions;
    }
}
