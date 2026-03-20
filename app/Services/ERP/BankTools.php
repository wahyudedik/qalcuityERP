<?php

namespace App\Services\ERP;

use App\Models\BankAccount;
use App\Models\BankStatement;

class BankTools
{
    public function __construct(private int $tenantId, private int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'list_bank_accounts',
                'description' => 'Daftar rekening bank perusahaan',
                'parameters'  => ['type' => 'object', 'properties' => []],
            ],
            [
                'name'        => 'get_bank_statements',
                'description' => 'Ambil mutasi rekening bank',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'bank_account_id' => ['type' => 'integer', 'description' => 'ID rekening bank'],
                        'status'          => ['type' => 'string', 'description' => 'Filter: unmatched, matched'],
                        'limit'           => ['type' => 'integer', 'description' => 'Jumlah data'],
                    ],
                    'required' => ['bank_account_id'],
                ],
            ],
            [
                'name'        => 'get_reconciliation_summary',
                'description' => 'Ringkasan rekonsiliasi bank: total matched vs unmatched',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'bank_account_id' => ['type' => 'integer', 'description' => 'ID rekening bank'],
                    ],
                    'required' => ['bank_account_id'],
                ],
            ],
        ];
    }

    public function listBankAccounts(array $args): array
    {
        $accounts = BankAccount::where('tenant_id', $this->tenantId)->get();
        return ['status' => 'success', 'accounts' => $accounts->toArray()];
    }

    public function getBankStatements(array $args): array
    {
        $q = BankStatement::where('tenant_id', $this->tenantId)
            ->where('bank_account_id', $args['bank_account_id']);
        if (!empty($args['status'])) $q->where('status', $args['status']);
        $data = $q->latest('transaction_date')->take($args['limit'] ?? 50)->get();
        return ['status' => 'success', 'statements' => $data->toArray()];
    }

    public function getReconciliationSummary(array $args): array
    {
        $q = BankStatement::where('tenant_id', $this->tenantId)
            ->where('bank_account_id', $args['bank_account_id']);

        return [
            'status'    => 'success',
            'matched'   => (clone $q)->where('status', 'matched')->count(),
            'unmatched' => (clone $q)->where('status', 'unmatched')->count(),
            'total_debit'  => (clone $q)->where('type', 'debit')->sum('amount'),
            'total_credit' => (clone $q)->where('type', 'credit')->sum('amount'),
        ];
    }
}
