<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Tenant;
use App\Models\JournalEntryLine;

class ChartOfAccount extends Model
{
    use AuditsChanges, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'parent_id',
        'code',
        'name',
        'type',
        'normal_balance',
        'level',
        'is_header',
        'is_active',
        'description',
    ];

    protected $casts = [
        'is_header' => 'boolean',
        'is_active' => 'boolean',
        'level' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }
    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }
    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'account_id');
    }

    /** Saldo akun (debit - credit atau credit - debit tergantung normal_balance) */
    public function balance(int $tenantId, ?string $from = null, ?string $to = null): float
    {
        $query = $this->journalLines()
            ->whereHas(
                'journalEntry',
                fn($q) => $q
                    ->where('tenant_id', $tenantId)
                    ->where('status', 'posted')
                    ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                    ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
            );

        $debit = (float) $query->sum('debit');
        $credit = (float) $query->sum('credit');

        return $this->normal_balance === 'debit' ? $debit - $credit : $credit - $debit;
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'asset' => 'Aset',
            'liability' => 'Kewajiban',
            'equity' => 'Ekuitas',
            'revenue' => 'Pendapatan',
            'expense' => 'Beban',
            default => $this->type,
        };
    }
}
