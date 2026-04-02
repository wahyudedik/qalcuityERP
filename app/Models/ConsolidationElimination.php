<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsolidationElimination extends Model
{
    protected $fillable = [
        'company_group_id',
        'consolidation_report_id',
        'type',
        'reference',
        'related_transaction_id',
        'date',
        'description',
        'amount',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function consolidationReport(): BelongsTo
    {
        return $this->belongsTo(ConsolidationReport::class);
    }

    public function relatedTransaction(): BelongsTo
    {
        return $this->belongsTo(IntercompanyTransaction::class, 'related_transaction_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ConsolidationEliminationLine::class, 'elimination_id');
    }

    public function totalDebit(): float
    {
        return (float) $this->lines()->sum('debit');
    }

    public function totalCredit(): float
    {
        return (float) $this->lines()->sum('credit');
    }

    public function isBalanced(): bool
    {
        return abs($this->totalDebit() - $this->totalCredit()) < 0.01;
    }
}
