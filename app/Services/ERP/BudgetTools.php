<?php

namespace App\Services\ERP;

use App\Models\Budget;
use App\Models\Transaction;

class BudgetTools
{
    public function __construct(protected int $tenantId, protected int $userId) {}

    public static function definitions(): array
    {
        return [
            [
                'name'        => 'create_budget',
                'description' => 'Buat anggaran baru per departemen atau kategori.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'name'        => ['type' => 'string', 'description' => 'Nama anggaran'],
                        'department'  => ['type' => 'string', 'description' => 'Departemen (opsional)'],
                        'category'    => ['type' => 'string', 'description' => 'Kategori pengeluaran (opsional)'],
                        'amount'      => ['type' => 'number', 'description' => 'Jumlah anggaran (Rp)'],
                        'period'      => ['type' => 'string', 'description' => 'Periode YYYY-MM atau YYYY (default: bulan ini)'],
                        'period_type' => ['type' => 'string', 'description' => 'monthly, quarterly, annual'],
                    ],
                    'required' => ['name', 'amount'],
                ],
            ],
            [
                'name'        => 'get_budget_vs_actual',
                'description' => 'Bandingkan anggaran vs realisasi pengeluaran. Tampilkan alert jika over budget.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'period'     => ['type' => 'string', 'description' => 'Periode YYYY-MM (default: bulan ini)'],
                        'department' => ['type' => 'string', 'description' => 'Filter departemen (opsional)'],
                    ],
                ],
            ],
            [
                'name'        => 'update_budget_realized',
                'description' => 'Update realisasi anggaran secara manual.',
                'parameters'  => [
                    'type'       => 'object',
                    'properties' => [
                        'budget_name' => ['type' => 'string', 'description' => 'Nama anggaran'],
                        'realized'    => ['type' => 'number', 'description' => 'Jumlah realisasi terbaru (Rp)'],
                        'period'      => ['type' => 'string', 'description' => 'Periode YYYY-MM'],
                    ],
                    'required' => ['budget_name', 'realized'],
                ],
            ],
        ];
    }

    public function createBudget(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');

        $budget = Budget::create([
            'tenant_id'   => $this->tenantId,
            'name'        => $args['name'],
            'department'  => $args['department'] ?? null,
            'category'    => $args['category'] ?? null,
            'period'      => $period,
            'period_type' => $args['period_type'] ?? 'monthly',
            'amount'      => $args['amount'],
            'realized'    => 0,
            'status'      => 'active',
        ]);

        return [
            'status'  => 'success',
            'message' => "Anggaran **{$budget->name}** sebesar Rp " . number_format($budget->amount, 0, ',', '.')
                . " untuk periode {$period} berhasil dibuat.",
        ];
    }

    public function getBudgetVsActual(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $query  = Budget::where('tenant_id', $this->tenantId)->where('period', $period)->where('status', 'active');

        if (!empty($args['department'])) {
            $query->where('department', 'like', "%{$args['department']}%");
        }

        $budgets = $query->get();

        if ($budgets->isEmpty()) {
            return ['status' => 'success', 'message' => "Belum ada anggaran untuk periode {$period}."];
        }

        // Sync realisasi dari transaksi
        [$year, $month] = explode('-', $period . '-01');
        foreach ($budgets as $budget) {
            if ($budget->category) {
                $realized = Transaction::where('tenant_id', $this->tenantId)
                    ->where('type', 'expense')
                    ->whereYear('date', $year)
                    ->whereMonth('date', $month)
                    ->whereHas('category', fn($q) => $q->where('name', 'like', "%{$budget->category}%"))
                    ->sum('amount');
                $budget->update(['realized' => $realized]);
            }
        }

        $budgets = $budgets->fresh();
        $totalBudget   = $budgets->sum('amount');
        $totalRealized = $budgets->sum('realized');
        $overBudget    = $budgets->filter(fn($b) => $b->realized > $b->amount);

        $data = $budgets->map(fn($b) => [
            'nama'       => $b->name,
            'departemen' => $b->department ?? '-',
            'anggaran'   => 'Rp ' . number_format($b->amount, 0, ',', '.'),
            'realisasi'  => 'Rp ' . number_format($b->realized, 0, ',', '.'),
            'sisa'       => 'Rp ' . number_format($b->amount - $b->realized, 0, ',', '.'),
            'pemakaian'  => $b->usage_percent . '%',
            'status'     => $b->realized > $b->amount ? 'OVER BUDGET' : ($b->usage_percent >= 80 ? 'HAMPIR HABIS' : 'AMAN'),
        ])->toArray();

        $result = [
            'status'         => 'success',
            'period'         => $period,
            'total_anggaran' => 'Rp ' . number_format($totalBudget, 0, ',', '.'),
            'total_realisasi'=> 'Rp ' . number_format($totalRealized, 0, ',', '.'),
            'sisa_anggaran'  => 'Rp ' . number_format($totalBudget - $totalRealized, 0, ',', '.'),
            'data'           => $data,
        ];

        if ($overBudget->isNotEmpty()) {
            $result['alert'] = '⚠️ OVER BUDGET: ' . $overBudget->pluck('name')->implode(', ');
        }

        return $result;
    }

    public function updateBudgetRealized(array $args): array
    {
        $period = $args['period'] ?? now()->format('Y-m');
        $budget = Budget::where('tenant_id', $this->tenantId)
            ->where('name', 'like', "%{$args['budget_name']}%")
            ->where('period', $period)
            ->first();

        if (!$budget) {
            return ['status' => 'error', 'message' => "Anggaran '{$args['budget_name']}' periode {$period} tidak ditemukan."];
        }

        $budget->update(['realized' => $args['realized']]);
        $status = $budget->realized > $budget->amount ? '⚠️ OVER BUDGET' : '✅ Dalam batas';

        return [
            'status'  => 'success',
            'message' => "Realisasi **{$budget->name}** diperbarui: Rp " . number_format($args['realized'], 0, ',', '.')
                . " dari anggaran Rp " . number_format($budget->amount, 0, ',', '.') . ". {$status}.",
        ];
    }
}
