<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsolidationOwnership extends Model
{
    use BelongsToTenant;

    protected $table = 'consolidation_ownership';

    protected $fillable = [
        'company_group_id',
        'parent_tenant_id',
        'subsidiary_tenant_id',
        'ownership_percentage',
        'effective_from',
        'effective_to',
        'consolidation_method',
        'notes',
    ];

    protected $casts = [
        'ownership_percentage' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function parentTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'parent_tenant_id');
    }

    public function subsidiaryTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'subsidiary_tenant_id');
    }

    public function isActive(?string $date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : now();

        if ($checkDate->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_to && $checkDate->gt($this->effective_to)) {
            return false;
        }

        return true;
    }

    public function getConsolidationMethodLabel(): string
    {
        return match ($this->consolidation_method) {
            'full' => 'Full Consolidation (100%)',
            'proportional' => 'Proportional Consolidation',
            'equity' => 'Equity Method',
            default => $this->consolidation_method,
        };
    }
}
