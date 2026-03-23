<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    protected $fillable = [
        'tenant_id', 'parent_id', 'code', 'name', 'type', 'description', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function tenant(): BelongsTo   { return $this->belongsTo(Tenant::class); }
    public function parent(): BelongsTo   { return $this->belongsTo(CostCenter::class, 'parent_id'); }
    public function children(): HasMany   { return $this->hasMany(CostCenter::class, 'parent_id'); }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'department'   => 'Departemen',
            'branch'       => 'Cabang',
            'project'      => 'Proyek',
            'product_line' => 'Lini Produk',
            default        => ucfirst($this->type),
        };
    }

    /**
     * Laporan P&L per cost center untuk rentang tanggal tertentu.
     * Mengambil dari journal_entry_lines yang di-tag ke cost center ini.
     */
    public function plReport(string $from, string $to): array
    {
        $lines = \App\Models\JournalEntryLine::where('cost_center_id', $this->id)
            ->whereHas('journalEntry', fn($q) => $q
                ->where('status', 'posted')
                ->whereBetween('date', [$from, $to])
            )
            ->with('account')
            ->get();

        $revenue = 0;
        $expense = 0;

        foreach ($lines as $line) {
            if (!$line->account) continue;
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
            'cost_center' => $this->name,
            'revenue'     => $revenue,
            'expense'     => $expense,
            'profit'      => $revenue - $expense,
        ];
    }
}
