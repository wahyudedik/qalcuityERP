<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsolidationAdjustment extends Model
{
    protected $fillable = [
        'company_group_id',
        'consolidation_report_id',
        'created_by',
        'number',
        'date',
        'description',
        'status',
        'posted_at',
    ];

    protected $casts = [
        'date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function consolidationReport(): BelongsTo
    {
        return $this->belongsTo(ConsolidationReport::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(ConsolidationAdjustmentLine::class, 'adjustment_id');
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

    public function post(): void
    {
        if (! $this->isBalanced()) {
            throw new \RuntimeException('Adjustment tidak balance: debit ≠ credit.');
        }

        $this->update([
            'status' => 'posted',
            'posted_at' => now(),
        ]);
    }
}
