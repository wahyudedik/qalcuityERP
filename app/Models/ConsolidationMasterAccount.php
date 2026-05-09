<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsolidationMasterAccount extends Model
{
    protected $fillable = [
        'company_group_id',
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

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ConsolidationMasterAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ConsolidationMasterAccount::class, 'parent_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(ConsolidationAccountMapping::class, 'consolidated_account_id');
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
