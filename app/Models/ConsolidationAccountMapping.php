<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsolidationAccountMapping extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'company_group_id',
        'source_tenant_id',
        'source_account_id',
        'consolidated_account_id',
        'mapping_type',
        'notes',
    ];

    public function companyGroup(): BelongsTo
    {
        return $this->belongsTo(CompanyGroup::class);
    }

    public function sourceTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'source_tenant_id');
    }

    public function sourceAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'source_account_id');
    }

    public function consolidatedAccount(): BelongsTo
    {
        return $this->belongsTo(ConsolidationMasterAccount::class, 'consolidated_account_id');
    }
}
