<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsolidationReport extends Model
{
    protected $fillable = [
        'company_group_id',
        'generated_by',
        'report_type',
        'period_type',
        'period_start',
        'period_end',
        'included_tenants',
        'report_data',
        'status',
        'finalized_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'included_tenants' => 'array',
        'report_data' => 'array',
        'finalized_at' => 'datetime',
    ];

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function eliminations(): HasMany
    {
        return $this->hasMany(ConsolidationElimination::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(ConsolidationAdjustment::class);
    }

    public function finalize(int $userId): void
    {
        $this->update([
            'status' => 'finalized',
            'finalized_at' => now(),
        ]);
    }

    public function getReportTypeLabel(): string
    {
        return match($this->report_type) {
            'balance_sheet' => 'Neraca Konsolidasi',
            'income_statement' => 'Laba Rugi Konsolidasi',
            'cash_flow' => 'Arus Kas Konsolidasi',
            default => $this->report_type,
        };
    }

    public function getPeriodTypeLabel(): string
    {
        return match($this->period_type) {
            'monthly' => 'Bulanan',
            'quarterly' => 'Kuartalan',
            'yearly' => 'Tahunan',
            default => $this->period_type,
        };
    }
}
