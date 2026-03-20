<?php

namespace App\Services\ERP;

use App\Models\ExpenseCategory;
use App\Models\Transaction;
use Illuminate\Support\Str;

class FinanceTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'get_finance_summary',
                'description' => 'Tampilkan ringkasan keuangan (pemasukan, pengeluaran, profit) dalam periode tertentu.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'today, this_week, this_month, last_month, atau YYYY-MM'],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name'        => 'get_expense_breakdown',
                'description' => 'Tampilkan rincian pengeluaran per kategori.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period' => ['type' => 'string', 'description' => 'Periode laporan'],
                    ],
                    'required' => ['period'],
                ],
            ],
            [
                'name'        => 'add_transaction',
                'description' => 'Catat transaksi keuangan baru (pemasukan atau pengeluaran).',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'type'        => ['type' => 'string', 'description' => 'income atau expense'],
                        'amount'      => ['type' => 'number', 'description' => 'Jumlah nominal'],
                        'category'    => ['type' => 'string', 'description' => 'Nama kategori'],
                        'description' => ['type' => 'string', 'description' => 'Keterangan transaksi'],
                        'date'        => ['type' => 'string', 'description' => 'Tanggal transaksi YYYY-MM-DD (default: hari ini)'],
                        'payment_method' => ['type' => 'string', 'description' => 'cash, transfer, dll'],
                    ],
                    'required' => ['type', 'amount', 'description'],
                ],
            ],
            [
                'name'        => 'create_expense_category',
                'description' => 'Buat kategori pengeluaran/pemasukan baru. Gunakan untuk: '
                    . '"tambah kategori pengeluaran Bahan Baku", '
                    . '"buat kategori Operasional dan Gaji", '
                    . '"tambah kategori pengeluaran: Bahan Baku, Operasional, Gaji".',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'names' => [
                            'type'  => 'array',
                            'items' => ['type' => 'string'],
                            'description' => 'Daftar nama kategori yang ingin dibuat (bisa satu atau lebih)',
                        ],
                        'type' => ['type' => 'string', 'description' => 'expense atau income. Default: expense'],
                    ],
                    'required' => ['names'],
                ],
            ],
        ];
    }

    public function createExpenseCategory(array $args): array
    {
        $type = $args['type'] ?? 'expense';
        $created = [];
        $skipped = [];

        foreach ($args['names'] as $name) {
            $name = trim($name);
            if (!$name) continue;

            $exists = ExpenseCategory::where('tenant_id', $this->tenantId)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                $skipped[] = $name;
                continue;
            }

            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 5)) . '-' . rand(10, 99);

            ExpenseCategory::create([
                'tenant_id'   => $this->tenantId,
                'name'        => $name,
                'code'        => $code,
                'type'        => $type,
                'is_active'   => true,
            ]);

            $created[] = $name;
        }

        $msg = '';
        if (!empty($created)) {
            $msg .= "Kategori berhasil dibuat: **" . implode('**, **', $created) . "**.";
        }
        if (!empty($skipped)) {
            $msg .= " Sudah ada sebelumnya: " . implode(', ', $skipped) . ".";
        }

        return [
            'status'  => empty($created) ? 'error' : 'success',
            'message' => trim($msg) ?: 'Tidak ada kategori yang dibuat.',
        ];
    }

    public function getFinanceSummary(array $args): array
    {
        $income  = $this->periodQuery('income', $args['period'])->sum('amount');
        $expense = $this->periodQuery('expense', $args['period'])->sum('amount');
        $profit  = $income - $expense;

        return [
            'status' => 'success',
            'data'   => [
                'period'       => $args['period'],
                'income'       => 'Rp ' . number_format($income, 0, ',', '.'),
                'expense'      => 'Rp ' . number_format($expense, 0, ',', '.'),
                'profit'       => 'Rp ' . number_format($profit, 0, ',', '.'),
                'profit_status'=> $profit >= 0 ? 'SURPLUS' : 'DEFISIT',
            ],
        ];
    }

    public function getExpenseBreakdown(array $args): array
    {
        $breakdown = $this->periodQuery('expense', $args['period'])
            ->with('category')
            ->get()
            ->groupBy(fn($t) => $t->category?->name ?? 'Tidak Berkategori')
            ->map(fn($group) => [
                'category' => $group->first()->category?->name ?? 'Tidak Berkategori',
                'total'    => 'Rp ' . number_format($group->sum('amount'), 0, ',', '.'),
                'count'    => $group->count(),
            ])
            ->values();

        if ($breakdown->isEmpty()) {
            return ['status' => 'success', 'message' => 'Tidak ada pengeluaran pada periode ini.'];
        }

        return ['status' => 'success', 'data' => $breakdown->toArray()];
    }

    public function addTransaction(array $args): array
    {
        $category = null;
        if (!empty($args['category'])) {
            $category = ExpenseCategory::where('tenant_id', $this->tenantId)
                ->where('name', 'like', "%{$args['category']}%")
                ->first();
        }

        $transaction = Transaction::create([
            'tenant_id'           => $this->tenantId,
            'user_id'             => $this->userId,
            'expense_category_id' => $category?->id,
            'number'              => 'TRX-' . strtoupper(Str::random(8)),
            'type'                => $args['type'],
            'date'                => $args['date'] ?? today()->toDateString(),
            'amount'              => $args['amount'],
            'payment_method'      => $args['payment_method'] ?? 'cash',
            'description'         => $args['description'],
        ]);

        return [
            'status'  => 'success',
            'message' => "Transaksi {$transaction->number} berhasil dicatat. {$args['type']} sebesar Rp " . number_format($args['amount'], 0, ',', '.'),
        ];
    }

    protected function periodQuery(string $type, string $period)
    {
        $query = Transaction::where('tenant_id', $this->tenantId)->where('type', $type);

        return match ($period) {
            'today'      => $query->whereDate('date', today()),
            'this_week'  => $query->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()]),
            'this_month' => $query->whereMonth('date', now()->month)->whereYear('date', now()->year),
            'last_month' => $query->whereMonth('date', now()->subMonth()->month)->whereYear('date', now()->subMonth()->year),
            default      => strlen($period) === 7
                ? $query->whereYear('date', substr($period, 0, 4))->whereMonth('date', substr($period, 5, 2))
                : $query,
        };
    }
}
