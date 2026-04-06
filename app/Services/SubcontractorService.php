<?php

namespace App\Services;

use App\Models\Subcontractor;
use App\Models\SubcontractorContract;
use App\Models\SubcontractorPayment;

/**
 * Subcontractor Management Service untuk Konstruksi
 */
class SubcontractorService
{
    /**
     * Register new subcontractor
     */
    public function registerSubcontractor(array $data, int $tenantId): Subcontractor
    {
        return Subcontractor::create([
            'tenant_id' => $tenantId,
            'company_name' => $data['company_name'],
            'contact_person' => $data['contact_person'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'specialization' => $data['specialization'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'tax_id' => $data['tax_id'] ?? null,
            'status' => 'active',
            'rating' => 0,
            'total_projects' => 0,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Create subcontractor contract
     */
    public function createContract(array $data, int $tenantId): SubcontractorContract
    {
        // Generate contract number
        $contractNumber = 'SC-' . date('Ymd') . '-' . str_pad(
            SubcontractorContract::where('tenant_id', $tenantId)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        return SubcontractorContract::create([
            'tenant_id' => $tenantId,
            'subcontractor_id' => $data['subcontractor_id'],
            'project_id' => $data['project_id'],
            'contract_number' => $contractNumber,
            'scope_of_work' => $data['scope_of_work'],
            'contract_value' => $data['contract_value'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'status' => 'draft',
            'payment_terms' => $data['payment_terms'] ?? null,
            'retention_percentage' => $data['retention_percentage'] ?? 5,
            'warranty_period_months' => $data['warranty_period_months'] ?? 12,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Activate contract
     */
    public function activateContract(int $contractId, int $tenantId): SubcontractorContract
    {
        $contract = SubcontractorContract::where('id', $contractId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $contract->update(['status' => 'active']);

        // Update subcontractor total projects
        $contract->subcontractor->increment('total_projects');

        return $contract;
    }

    /**
     * Submit progress billing/payment claim
     */
    public function submitPaymentClaim(array $data, int $tenantId): SubcontractorPayment
    {
        $contract = SubcontractorContract::where('id', $data['contract_id'])
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        // Calculate retention and net payable
        $retentionDeducted = $data['claimed_amount'] * ($contract->retention_percentage / 100);
        $netPayable = $data['claimed_amount'] - $retentionDeducted;

        // Generate invoice number
        $invoiceNumber = 'INV-SC-' . date('Ymd') . '-' . str_pad(
            SubcontractorPayment::where('tenant_id', $tenantId)->count() + 1,
            4,
            '0',
            STR_PAD_LEFT
        );

        return SubcontractorPayment::create([
            'tenant_id' => $tenantId,
            'contract_id' => $data['contract_id'],
            'invoice_number' => $invoiceNumber,
            'billing_period' => $data['billing_period'],
            'work_description' => $data['work_description'],
            'claimed_amount' => $data['claimed_amount'],
            'approved_amount' => 0,
            'retention_deducted' => $retentionDeducted,
            'net_payable' => $netPayable,
            'status' => 'pending',
            'remarks' => $data['remarks'] ?? null,
        ]);
    }

    /**
     * Approve payment claim
     */
    public function approvePayment(int $paymentId, int $tenantId, float $approvedAmount): SubcontractorPayment
    {
        $payment = SubcontractorPayment::where('id', $paymentId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $payment->update([
            'approved_amount' => $approvedAmount,
            'status' => 'approved',
        ]);

        return $payment;
    }

    /**
     * Mark payment as paid
     */
    public function markAsPaid(int $paymentId, int $tenantId, ?string $paymentDate = null): SubcontractorPayment
    {
        $payment = SubcontractorPayment::where('id', $paymentId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $payment->update([
            'status' => 'paid',
            'payment_date' => $paymentDate ?? now(),
        ]);

        return $payment;
    }

    /**
     * Get subcontractor performance summary
     */
    public function getPerformanceSummary(int $subcontractorId, int $tenantId): array
    {
        $subcontractor = Subcontractor::where('id', $subcontractorId)
            ->where('tenant_id', $tenantId)
            ->with(['contracts.project'])
            ->firstOrFail();

        $contracts = $subcontractor->contracts;
        $completedContracts = $contracts->where('status', 'completed');

        return [
            'subcontractor' => [
                'id' => $subcontractor->id,
                'company_name' => $subcontractor->company_name,
                'specialization' => $subcontractor->specialization,
                'rating' => $subcontractor->calculateAverageRating(),
                'total_projects' => $subcontractor->total_projects,
                'active_contracts' => $subcontractor->getActiveContractsCount(),
            ],
            'financial_summary' => [
                'total_contract_value' => $contracts->sum('contract_value'),
                'total_paid' => $contracts->flatMap->payments->where('status', 'paid')->sum('approved_amount'),
                'total_outstanding' => $contracts->sum(fn($c) => $c->getRemainingBalance()),
                'total_retention_held' => $contracts->sum(fn($c) => $c->getRetentionAmount()),
            ],
            'performance_metrics' => [
                'completed_projects' => $completedContracts->count(),
                'avg_performance_rating' => $completedContracts->avg('performance_rating') ?? 0,
                'on_time_completion_rate' => $this->calculateOnTimeRate($completedContracts),
                'avg_contract_duration_days' => $contracts->avg(
                    fn($c) =>
                    $c->start_date && $c->end_date
                    ? $c->start_date->diffInDays($c->end_date)
                    : 0
                ) ?? 0,
            ],
        ];
    }

    /**
     * Calculate on-time completion rate
     */
    private function calculateOnTimeRate($contracts): float
    {
        if ($contracts->isEmpty()) {
            return 0;
        }

        $onTimeCount = $contracts->filter(function ($contract) {
            return $contract->end_date && $contract->end_date->gte(now());
        })->count();

        return round(($onTimeCount / $contracts->count()) * 100, 1);
    }

    /**
     * Get subcontractors by specialization
     */
    public function getBySpecialization(string $specialization, int $tenantId): array
    {
        return Subcontractor::where('tenant_id', $tenantId)
            ->where('specialization', $specialization)
            ->where('status', 'active')
            ->orderByDesc('rating')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'company_name' => $s->company_name,
                'contact_person' => $s->contact_person,
                'phone' => $s->phone,
                'rating' => $s->rating,
                'total_projects' => $s->total_projects,
            ])
            ->toArray();
    }
}
