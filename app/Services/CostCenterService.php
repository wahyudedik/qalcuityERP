<?php

namespace App\Services;

use App\Models\CostCenter;
use App\Models\JournalEntryLine;
use Illuminate\Support\Collection;

class CostCenterService
{
    /**
     * Buat cost center baru dengan validasi hierarki dan duplikasi kode.
     *
     * @throws \RuntimeException jika kode duplikat atau kedalaman hierarki > 3
     */
    public function create(int $tenantId, array $data): CostCenter
    {
        // Validasi kode duplikat per tenant
        $exists = CostCenter::where('tenant_id', $tenantId)
            ->where('code', $data['code'])
            ->exists();

        if ($exists) {
            throw new \RuntimeException(
                "Kode cost center '{$data['code']}' sudah digunakan dalam tenant ini."
            );
        }

        // Validasi kedalaman hierarki
        $this->validateDepth($tenantId, $data['parent_id'] ?? null);

        return CostCenter::create(array_merge($data, ['tenant_id' => $tenantId]));
    }

    /**
     * Validasi kedalaman hierarki (maks 3 level).
     *
     * @throws \RuntimeException jika kedalaman melebihi 3
     */
    public function validateDepth(int $tenantId, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        } // Level 1 (root), selalu valid

        $depth = 1;
        $current = CostCenter::where('tenant_id', $tenantId)->find($parentId);

        while ($current && $current->parent_id !== null) {
            $depth++;
            if ($depth >= 3) {
                throw new \RuntimeException(
                    'Hierarki cost center maksimal 3 level. Cost center ini sudah berada di level '.($depth + 1).'.'
                );
            }
            $current = CostCenter::where('tenant_id', $tenantId)->find($current->parent_id);
        }
    }

    /**
     * Hapus cost center dengan validasi: tidak ada children aktif, tidak ada transaksi.
     *
     * @throws \RuntimeException jika ada children atau transaksi terkait
     */
    public function delete(CostCenter $costCenter): void
    {
        // Cek children aktif
        if ($costCenter->children()->exists()) {
            throw new \RuntimeException(
                'Cost center tidak bisa dihapus karena memiliki sub-divisi.'
            );
        }

        // Cek transaksi terkait (JournalEntryLine)
        $hasTransactions = JournalEntryLine::where('cost_center_id', $costCenter->id)->exists();
        if ($hasTransactions) {
            throw new \RuntimeException(
                'Cost center tidak bisa dihapus karena sudah memiliki transaksi terkait.'
            );
        }

        $costCenter->delete();
    }

    /**
     * Ambil semua ID cost center dalam satu subtree (self + semua descendants).
     */
    public function getSubtreeIds(int $costCenterId): array
    {
        $ids = [$costCenterId];
        $children = CostCenter::where('parent_id', $costCenterId)->pluck('id');
        foreach ($children as $childId) {
            $ids = array_merge($ids, $this->getSubtreeIds($childId));
        }

        return $ids;
    }

    /**
     * Laporan P&L per cost center untuk rentang tanggal.
     * Mengagregasi nilai dari seluruh hierarki di bawah setiap cost center.
     */
    public function plReport(int $tenantId, string $from, string $to): Collection
    {
        $centers = CostCenter::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with('children.children')
            ->orderBy('code')
            ->get();

        return $centers->map(function ($cc) use ($from, $to) {
            $subtreeIds = $this->getSubtreeIds($cc->id);

            return $this->aggregatePL($subtreeIds, $cc->name, $from, $to);
        });
    }

    /**
     * Laporan neraca per cost center (saldo akumulatif sejak awal).
     */
    public function balanceSheetSegment(int $tenantId, ?int $costCenterId, string $asOf): array
    {
        $subtreeIds = $costCenterId
            ? $this->getSubtreeIds($costCenterId)
            : CostCenter::where('tenant_id', $tenantId)->pluck('id')->toArray();

        $lines = JournalEntryLine::whereIn('cost_center_id', $subtreeIds)
            ->whereHas('journalEntry', fn ($q) => $q
                ->where('status', 'posted')
                ->whereDate('date', '<=', $asOf)
            )
            ->with('account')
            ->get();

        $assets = 0;
        $liabilities = 0;
        $equity = 0;

        foreach ($lines as $line) {
            if (! $line->account) {
                continue;
            }
            $net = $line->debit - $line->credit;
            match ($line->account->type) {
                'asset' => $assets += $net,
                'liability' => $liabilities += -$net,
                'equity' => $equity += -$net,
                default => null,
            };
        }

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
        ];
    }

    /**
     * Hitung baris total konsolidasi dari collection hasil plReport().
     */
    public function plTotals(Collection $report): array
    {
        return [
            'revenue' => $report->sum('revenue'),
            'expense' => $report->sum('expense'),
            'profit' => $report->sum('profit'),
        ];
    }

    /**
     * Agregasi P&L dari kumpulan cost center IDs.
     */
    private function aggregatePL(array $costCenterIds, string $label, string $from, string $to): array
    {
        $lines = JournalEntryLine::whereIn('cost_center_id', $costCenterIds)
            ->whereHas('journalEntry', fn ($q) => $q
                ->where('status', 'posted')
                ->whereBetween('date', [$from, $to])
            )
            ->with('account')
            ->get();

        $revenue = 0;
        $expense = 0;

        foreach ($lines as $line) {
            if (! $line->account) {
                continue;
            }
            if ($line->account->type === 'revenue') {
                $revenue += $line->account->normal_balance === 'credit'
                    ? ($line->credit - $line->debit)
                    : ($line->debit - $line->credit);
            } elseif ($line->account->type === 'expense') {
                $expense += $line->account->normal_balance === 'debit'
                    ? ($line->debit - $line->credit)
                    : ($line->credit - $line->debit);
            }
        }

        return [
            'label' => $label,
            'revenue' => $revenue,
            'expense' => $expense,
            'profit' => $revenue - $expense,
        ];
    }
}
