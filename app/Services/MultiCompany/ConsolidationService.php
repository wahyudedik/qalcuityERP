<?php

namespace App\Services\MultiCompany;

use App\Models\ConsolidatedReport;
use App\Models\InterCompanyTransaction;
use App\Models\Tenant;
use App\Models\TenantGroupMember;

class ConsolidationService
{
    /**
     * Generate consolidated balance sheet
     */
    public function generateBalanceSheet(int $groupId, string $periodStart, string $periodEnd, int $userId): ConsolidatedReport
    {
        // Get all active subsidiaries
        $subsidiaries = $this->getActiveSubsidiaries($groupId);

        // Collect balance sheet data from each subsidiary
        $consolidatedData = [
            'assets' => $this->consolidateAssets($subsidiaries),
            'liabilities' => $this->consolidateLiabilities($subsidiaries),
            'equity' => $this->consolidateEquity($subsidiaries),
        ];

        // Apply elimination entries
        $eliminations = $this->calculateEliminations($groupId, $periodStart, $periodEnd);

        // Create report
        return ConsolidatedReport::create([
            'company_group_id' => $groupId,
            'report_type' => 'balance_sheet',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'currency' => 'IDR',
            'report_data' => $consolidatedData,
            'elimination_entries' => $eliminations,
            'subsidiary_contributions' => $this->getSubsidiaryContributions($subsidiaries),
            'status' => 'draft',
            'prepared_by_user_id' => $userId,
        ]);
    }

    /**
     * Generate consolidated income statement
     */
    public function generateIncomeStatement(int $groupId, string $periodStart, string $periodEnd, int $userId): ConsolidatedReport
    {
        $subsidiaries = $this->getActiveSubsidiaries($groupId);

        $consolidatedData = [
            'revenue' => $this->consolidateRevenue($subsidiaries, $periodStart, $periodEnd),
            'expenses' => $this->consolidateExpenses($subsidiaries, $periodStart, $periodEnd),
            'net_income' => 0, // Will be calculated
        ];

        $consolidatedData['net_income'] = $consolidatedData['revenue'] - $consolidatedData['expenses'];

        $eliminations = $this->calculateEliminations($groupId, $periodStart, $periodEnd);

        return ConsolidatedReport::create([
            'company_group_id' => $groupId,
            'report_type' => 'income_statement',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'currency' => 'IDR',
            'report_data' => $consolidatedData,
            'elimination_entries' => $eliminations,
            'subsidiary_contributions' => $this->getSubsidiaryContributions($subsidiaries),
            'status' => 'draft',
            'prepared_by_user_id' => $userId,
        ]);
    }

    /**
     * Finalize report
     */
    public function finalizeReport(int $reportId): bool
    {
        try {
            $report = ConsolidatedReport::findOrFail($reportId);
            $report->update(['status' => 'finalized']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Finalize report failed', [
                'report_id' => $reportId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Approve report
     */
    public function approveReport(int $reportId, int $approvedByUserId): bool
    {
        try {
            $report = ConsolidatedReport::findOrFail($reportId);
            $report->update([
                'status' => 'published',
                'approved_by_user_id' => $approvedByUserId,
                'approved_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Approve report failed', [
                'report_id' => $reportId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get report history
     */
    public function getReportHistory(int $groupId, ?string $reportType = null): array
    {
        $query = ConsolidatedReport::where('company_group_id', $groupId)
            ->with(['preparedBy', 'approvedBy']);

        if ($reportType) {
            $query->where('report_type', $reportType);
        }

        return $query->orderBy('period_end', 'desc')->get()->toArray();
    }

    /**
     * Calculate eliminations for inter-company transactions
     */
    protected function calculateEliminations(int $groupId, string $periodStart, string $periodEnd): array
    {
        // Get all inter-company transactions in period
        $transactions = InterCompanyTransaction::where('company_group_id', $groupId)
            ->whereBetween('transaction_date', [$periodStart, $periodEnd])
            ->where('status', 'completed')
            ->get();

        $eliminations = [
            'revenue' => 0,
            'expense' => 0,
            'receivable' => 0,
            'payable' => 0,
        ];

        foreach ($transactions as $txn) {
            if ($txn->transaction_type === 'sale') {
                $eliminations['revenue'] += $txn->amount;
                $eliminations['receivable'] += $txn->amount;
            } elseif ($txn->transaction_type === 'purchase') {
                $eliminations['expense'] += $txn->amount;
                $eliminations['payable'] += $txn->amount;
            }
        }

        return $eliminations;
    }

    /**
     * Get active subsidiaries
     */
    protected function getActiveSubsidiaries(int $groupId): array
    {
        return TenantGroupMember::where('company_group_id', $groupId)
            ->where('is_active', true)
            ->pluck('tenant_id')
            ->toArray();
    }

    /**
     * Consolidate assets (placeholder - would query actual financial data)
     */
    protected function consolidateAssets(array $subsidiaries): float
    {
        // In production, this would query each subsidiary's balance sheet
        // For now, return placeholder
        return 0.00;
    }

    /**
     * Consolidate liabilities
     */
    protected function consolidateLiabilities(array $subsidiaries): float
    {
        return 0.00;
    }

    /**
     * Consolidate equity
     */
    protected function consolidateEquity(array $subsidiaries): float
    {
        return 0.00;
    }

    /**
     * Consolidate revenue
     */
    protected function consolidateRevenue(array $subsidiaries, string $start, string $end): float
    {
        return 0.00;
    }

    /**
     * Consolidate expenses
     */
    protected function consolidateExpenses(array $subsidiaries, string $start, string $end): float
    {
        return 0.00;
    }

    /**
     * Get subsidiary contributions breakdown
     */
    protected function getSubsidiaryContributions(array $subsidiaries): array
    {
        $contributions = [];

        foreach ($subsidiaries as $tenantId) {
            $tenant = Tenant::find($tenantId);
            $contributions[] = [
                'tenant_id' => $tenantId,
                'tenant_name' => $tenant ? $tenant->name : 'Unknown',
                // Would include actual financial contributions
            ];
        }

        return $contributions;
    }
}
