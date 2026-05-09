<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * LeaveBalanceService - Accurate leave balance calculation with pro-rata support
 *
 * BUG-HRM-003 FIX: Proper pro-rated leave calculation based on:
 * 1. Employee join date (pro-rata for first year)
 * 2. Resignation date (pro-rata for partial year)
 * 3. Monthly accrual (earned leave per month)
 * 4. Carry-over from previous year (if enabled)
 *
 * Indonesian Labor Law (UU Ketenagakerjaan No. 13/2003):
 * - Minimum 12 days annual leave after 12 months continuous service
 * - Pro-rata calculation for partial years
 * - Can carry over to next year (company policy)
 */
class LeaveBalanceService
{
    /**
     * BUG-HRM-003 FIX: Calculate accurate leave balance for employee
     *
     * @param  int|null  $year  Year to calculate (default: current year)
     * @return array Complete leave balance breakdown
     */
    public function calculateLeaveBalance(Employee $employee, ?int $year = null): array
    {
        $year = $year ?? now()->year;

        // Get employee service period
        $joinDate = Carbon::parse($employee->hire_date);
        $resignDate = $employee->resignation_date ? Carbon::parse($employee->resignation_date) : null;

        // Check if employee is eligible for leave (must work 12 months first)
        $isEligible = $this->isEligibleForLeave($employee, $year);

        // Calculate total entitlement for the year
        $annualEntitlement = $this->calculateAnnualEntitlement($employee, $year);

        // Calculate used leave
        $usedLeave = $this->calculateUsedLeave($employee, $year);

        // Calculate carry-over from previous year
        $carryOver = $this->calculateCarryOver($employee, $year);

        // Calculate total available
        $totalAvailable = $annualEntitlement + $carryOver;
        $remaining = max(0, $totalAvailable - $usedLeave);

        // Calculate monthly accrual (earned so far this year)
        $accrued = $this->calculateAccruedLeave($employee, $year);

        return [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'year' => $year,
            'join_date' => $joinDate->format('Y-m-d'),
            'resignation_date' => $resignDate?->format('Y-m-d'),
            'is_eligible' => $isEligible,
            'annual_entitlement' => $annualEntitlement,
            'carry_over' => $carryOver,
            'total_available' => $totalAvailable,
            'used' => $usedLeave,
            'remaining' => $remaining,
            'monthly_accrual' => $accrued['accrued_so_far'],
            'monthly_rate' => $accrued['monthly_rate'],
            'months_worked' => $accrued['months_worked'],
            'breakdown' => [
                'base_entitlement' => 12, // Standard 12 days
                'pro_rata_factor' => $annualEntitlement / 12,
                'pro_rata_reason' => $this->getProRataReason($employee, $year),
            ],
        ];
    }

    /**
     * BUG-HRM-003 FIX: Calculate annual leave entitlement with pro-rata
     *
     * Rules:
     * 1. First year: Pro-rata from month 13 onwards
     * 2. Subsequent years: Full 12 days (or company policy)
     * 3. Resignation year: Pro-rata until resignation date
     *
     * @return float Entitlement days
     */
    public function calculateAnnualEntitlement(Employee $employee, int $year): float
    {
        $joinDate = Carbon::parse($employee->hire_date);
        $resignDate = $employee->resignation_date ? Carbon::parse($employee->resignation_date) : null;

        $baseEntitlement = 12; // Standard annual leave

        // Check if this is the first year of employment
        $joinYear = $joinDate->year;

        if ($year === $joinYear) {
            // First year: Not eligible yet (must complete 12 months)
            return 0;
        }

        if ($year === $joinYear + 1) {
            // Second year: Pro-rata from month 13
            return $this->calculateFirstYearProRata($employee, $year, $baseEntitlement);
        }

        // Check if resignation year
        if ($resignDate && $year === $resignDate->year) {
            return $this->calculateResignationProRata($employee, $year, $baseEntitlement);
        }

        // Normal year: Full entitlement
        return $baseEntitlement;
    }

    /**
     * BUG-HRM-003 FIX: Calculate pro-rata for first eligible year
     *
     * Example:
     * Join Date: March 2023
     * Eligible from: March 2024 (month 13)
     * Entitlement for 2024: 10 months (Mar-Dec) / 12 months * 12 days = 10 days
     */
    protected function calculateFirstYearProRata(Employee $employee, int $year, float $baseEntitlement): float
    {
        $joinDate = Carbon::parse($employee->hire_date);

        // Eligibility starts from month 13
        $eligibilityDate = $joinDate->copy()->addMonths(12);

        // If eligibility starts this year
        if ($eligibilityDate->year === $year) {
            $monthsEligible = 12 - ($eligibilityDate->month - 1);
            $proRata = ($monthsEligible / 12) * $baseEntitlement;

            Log::info('Leave: First year pro-rata calculation', [
                'employee_id' => $employee->id,
                'join_date' => $joinDate->format('Y-m-d'),
                'eligibility_date' => $eligibilityDate->format('Y-m-d'),
                'months_eligible' => $monthsEligible,
                'pro_rata_days' => round($proRata, 2),
            ]);

            return round($proRata, 2);
        }

        return $baseEntitlement;
    }

    /**
     * BUG-HRM-003 FIX: Calculate pro-rata for resignation year
     *
     * Example:
     * Resignation Date: June 2024
     * Entitlement for 2024: 6 months (Jan-Jun) / 12 months * 12 days = 6 days
     */
    protected function calculateResignationProRata(Employee $employee, int $year, float $baseEntitlement): float
    {
        $resignDate = Carbon::parse($employee->resignation_date);

        // Calculate months worked in resignation year
        $yearStart = Carbon::parse("{$year}-01-01");
        $monthsWorked = $yearStart->diffInMonths($resignDate) + 1;

        $proRata = ($monthsWorked / 12) * $baseEntitlement;

        Log::info('Leave: Resignation year pro-rata calculation', [
            'employee_id' => $employee->id,
            'resignation_date' => $resignDate->format('Y-m-d'),
            'months_worked' => $monthsWorked,
            'pro_rata_days' => round($proRata, 2),
        ]);

        return round($proRata, 2);
    }

    /**
     * BUG-HRM-003 FIX: Check if employee is eligible for leave
     *
     * Must complete 12 months of continuous service
     */
    public function isEligibleForLeave(Employee $employee, int $year): bool
    {
        $joinDate = Carbon::parse($employee->hire_date);
        $eligibilityDate = $joinDate->copy()->addMonths(12);

        // Check if eligibility date has passed
        $eligibilityYear = $eligibilityDate->year;

        if ($year < $eligibilityYear) {
            return false; // Not yet eligible
        }

        // Check if already resigned before eligibility
        if ($employee->resignation_date) {
            $resignDate = Carbon::parse($employee->resignation_date);
            if ($resignDate->lt($eligibilityDate)) {
                return false; // Resigned before becoming eligible
            }
        }

        return true;
    }

    /**
     * BUG-HRM-003 FIX: Calculate used leave days
     */
    public function calculateUsedLeave(Employee $employee, int $year): float
    {
        return (float) LeaveRequest::where('employee_id', $employee->id)
            ->where('type', 'annual')
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days');
    }

    /**
     * BUG-HRM-003 FIX: Calculate carry-over from previous year
     *
     * Company policy can limit carry-over (e.g., max 5 days)
     */
    public function calculateCarryOver(Employee $employee, int $year): float
    {
        // Check if carry-over is enabled (default: yes)
        if (! $this->isCarryOverEnabled()) {
            return 0;
        }

        $previousYear = $year - 1;

        // Calculate previous year balance
        $previousEntitlement = $this->calculateAnnualEntitlement($employee, $previousYear);
        $previousUsed = $this->calculateUsedLeave($employee, $previousYear);
        $previousRemaining = max(0, $previousEntitlement - $previousUsed);

        // Apply carry-over limit (default: max 5 days or company policy)
        $carryOverLimit = $this->getCarryOverLimit();
        $carryOver = min($previousRemaining, $carryOverLimit);

        if ($carryOver > 0) {
            Log::info('Leave: Carry-over calculated', [
                'employee_id' => $employee->id,
                'year' => $year,
                'previous_remaining' => $previousRemaining,
                'carry_over_limit' => $carryOverLimit,
                'carry_over' => $carryOver,
            ]);
        }

        return $carryOver;
    }

    /**
     * BUG-HRM-003 FIX: Calculate monthly accrual (earned leave so far)
     */
    public function calculateAccruedLeave(Employee $employee, int $year): array
    {
        $entitlement = $this->calculateAnnualEntitlement($employee, $year);
        $monthlyRate = $entitlement / 12;

        // Calculate months worked in this year
        $joinDate = Carbon::parse($employee->hire_date);
        $resignDate = $employee->resignation_date ? Carbon::parse($employee->resignation_date) : null;

        $yearStart = Carbon::parse("{$year}-01-01");
        $yearEnd = Carbon::parse("{$year}-12-31");

        // Determine effective start date for this year
        $effectiveStart = $joinDate->gt($yearStart) ? $joinDate : $yearStart;
        $effectiveEnd = $resignDate ? $resignDate->lt($yearEnd) ? $resignDate : $yearEnd : $yearEnd;

        $monthsWorked = $effectiveStart->diffInMonths($effectiveEnd) + 1;
        $accruedSoFar = $monthsWorked * $monthlyRate;

        return [
            'monthly_rate' => round($monthlyRate, 2),
            'months_worked' => $monthsWorked,
            'accrued_so_far' => round($accruedSoFar, 2),
            'total_entitlement' => $entitlement,
        ];
    }

    /**
     * Check if employee has enough leave balance
     */
    public function checkLeaveBalance(Employee $employee, float $requestedDays, ?int $year = null): array
    {
        $balance = $this->calculateLeaveBalance($employee, $year);

        $hasEnough = $balance['remaining'] >= $requestedDays;
        $shortage = max(0, $requestedDays - $balance['remaining']);

        return [
            'has_enough' => $hasEnough,
            'requested' => $requestedDays,
            'available' => $balance['remaining'],
            'shortage' => $shortage,
            'message' => $hasEnough
                ? 'Cuti mencukupi. Sisa setelah pengajuan: '.round($balance['remaining'] - $requestedDays, 2).' hari.'
                : "Cuti tidak mencukupi. Kurang {$shortage} hari.",
        ];
    }

    /**
     * Get pro-rata reason for display
     */
    protected function getProRataReason(Employee $employee, int $year): string
    {
        $joinDate = Carbon::parse($employee->hire_date);

        if ($year === $joinDate->year) {
            return 'Tahun pertama employment - belum eligible (harus 12 bulan)';
        }

        if ($year === $joinDate->year + 1) {
            $eligibilityDate = $joinDate->copy()->addMonths(12);

            return "Pro-rata dari bulan eligible ({$eligibilityDate->format('M Y')})";
        }

        if ($employee->resignation_date && $year === Carbon::parse($employee->resignation_date)->year) {
            $resignDate = Carbon::parse($employee->resignation_date);

            return "Pro-rata sampai tanggal resign ({$resignDate->format('d M Y')})";
        }

        return 'Full year entitlement';
    }

    /**
     * Check if carry-over is enabled
     *
     * TODO: Make configurable per tenant
     */
    protected function isCarryOverEnabled(): bool
    {
        return config('hrm.leave_carry_over_enabled', true);
    }

    /**
     * Get carry-over limit
     *
     * TODO: Make configurable per tenant
     */
    protected function getCarryOverLimit(): float
    {
        return config('hrm.leave_carry_over_limit', 5); // Default max 5 days
    }
}
