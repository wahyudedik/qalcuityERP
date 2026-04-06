<?php

namespace App\Services\MultiCompany;

use App\Models\InterCompanyTransaction;
use App\Models\InterCompanyAccount;

class InterCompanyTransactionService
{
    /**
     * Create inter-company transaction
     */
    public function createTransaction(array $data): InterCompanyTransaction
    {
        return InterCompanyTransaction::create([
            'company_group_id' => $data['company_group_id'],
            'from_tenant_id' => $data['from_tenant_id'],
            'to_tenant_id' => $data['to_tenant_id'],
            'transaction_type' => $data['transaction_type'],
            'reference_type' => $data['reference_type'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'IDR',
            'exchange_rate' => $data['exchange_rate'] ?? 1.0,
            'transaction_date' => $data['transaction_date'],
            'due_date' => $data['due_date'] ?? null,
            'status' => 'pending',
            'description' => $data['description'] ?? null,
            'line_items' => $data['line_items'] ?? null,
            'created_by_user_id' => $data['created_by_user_id'],
        ]);
    }

    /**
     * Approve transaction
     */
    public function approveTransaction(int $transactionId, int $approvedByUserId): bool
    {
        try {
            $transaction = InterCompanyTransaction::findOrFail($transactionId);

            $transaction->update([
                'status' => 'approved',
                'approved_by_user_id' => $approvedByUserId,
                'approved_at' => now(),
            ]);

            // Update inter-company accounts
            $this->updateInterCompanyAccounts($transaction);

            return true;
        } catch (\Exception $e) {
            \Log::error('Approve transaction failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Reject transaction
     */
    public function rejectTransaction(int $transactionId, string $reason): bool
    {
        try {
            $transaction = InterCompanyTransaction::findOrFail($transactionId);

            $transaction->update([
                'status' => 'cancelled',
                'rejection_reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Reject transaction failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Complete transaction
     */
    public function completeTransaction(int $transactionId): bool
    {
        try {
            $transaction = InterCompanyTransaction::findOrFail($transactionId);

            $transaction->update(['status' => 'completed']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Complete transaction failed', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get pending transactions
     */
    public function getPendingTransactions(int $groupId): array
    {
        return InterCompanyTransaction::where('company_group_id', $groupId)
            ->where('status', 'pending')
            ->with(['fromTenant', 'toTenant', 'createdBy'])
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get transaction history
     */
    public function getTransactionHistory(int $groupId, ?string $type = null, ?string $status = null): array
    {
        $query = InterCompanyTransaction::where('company_group_id', $groupId)
            ->with(['fromTenant', 'toTenant', 'approvedBy']);

        if ($type) {
            $query->where('transaction_type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        return $query->orderBy('transaction_date', 'desc')->get()->toArray();
    }

    /**
     * Get inter-company account balance
     */
    public function getAccountBalance(int $groupId, int $tenantId, int $counterpartyId, string $accountType): float
    {
        $account = InterCompanyAccount::where('company_group_id', $groupId)
            ->where('tenant_id', $tenantId)
            ->where('counterparty_tenant_id', $counterpartyId)
            ->where('account_type', $accountType)
            ->first();

        return $account ? $account->balance : 0.00;
    }

    /**
     * Reconcile inter-company accounts
     */
    public function reconcileAccounts(int $groupId, int $tenantId, int $counterpartyId): array
    {
        $receivable = $this->getAccountBalance($groupId, $tenantId, $counterpartyId, 'receivable');
        $payable = $this->getAccountBalance($groupId, $tenantId, $counterpartyId, 'payable');

        $netBalance = $receivable - $payable;

        // Update reconciliation date
        InterCompanyAccount::where('company_group_id', $groupId)
            ->where('tenant_id', $tenantId)
            ->where('counterparty_tenant_id', $counterpartyId)
            ->update(['last_reconciliation_date' => now()]);

        return [
            'receivable' => $receivable,
            'payable' => $payable,
            'net_balance' => $netBalance,
            'reconciled_at' => now(),
        ];
    }

    /**
     * Update inter-company accounts
     */
    protected function updateInterCompanyAccounts(InterCompanyTransaction $transaction): void
    {
        // For sales: from_tenant has receivable, to_tenant has payable
        if ($transaction->transaction_type === 'sale') {
            $this->updateAccount(
                $transaction->company_group_id,
                $transaction->from_tenant_id,
                $transaction->to_tenant_id,
                'receivable',
                $transaction->amount
            );

            $this->updateAccount(
                $transaction->company_group_id,
                $transaction->to_tenant_id,
                $transaction->from_tenant_id,
                'payable',
                $transaction->amount
            );
        }

        // For purchases: reverse of sales
        if ($transaction->transaction_type === 'purchase') {
            $this->updateAccount(
                $transaction->company_group_id,
                $transaction->from_tenant_id,
                $transaction->to_tenant_id,
                'payable',
                $transaction->amount
            );

            $this->updateAccount(
                $transaction->company_group_id,
                $transaction->to_tenant_id,
                $transaction->from_tenant_id,
                'receivable',
                $transaction->amount
            );
        }
    }

    /**
     * Update single account
     */
    protected function updateAccount(int $groupId, int $tenantId, int $counterpartyId, string $accountType, float $amount): void
    {
        $account = InterCompanyAccount::firstOrCreate(
            [
                'company_group_id' => $groupId,
                'tenant_id' => $tenantId,
                'counterparty_tenant_id' => $counterpartyId,
                'account_type' => $accountType,
            ],
            [
                'balance' => 0.00,
                'currency' => 'IDR',
            ]
        );

        $account->increment('balance', $amount);
    }
}
