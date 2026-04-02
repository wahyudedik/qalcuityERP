<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CompanyGroup extends Model
{
    protected $fillable = ['owner_user_id', 'name', 'currency_code'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'company_group_members', 'company_group_id', 'tenant_id')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function intercompanyTransactions(): HasMany
    {
        return $this->hasMany(IntercompanyTransaction::class);
    }

    public function consolidationReports(): HasMany
    {
        return $this->hasMany(\App\Models\ConsolidationReport::class);
    }

    public function masterAccounts(): HasMany
    {
        return $this->hasMany(\App\Models\ConsolidationMasterAccount::class);
    }

    public function accountMappings(): HasMany
    {
        return $this->hasMany(\App\Models\ConsolidationAccountMapping::class);
    }

    public function eliminations(): HasMany
    {
        return $this->hasMany(\App\Models\ConsolidationElimination::class);
    }

    public function adjustments(): HasMany
    {
        return $this->hasMany(\App\Models\ConsolidationAdjustment::class);
    }

    public function ownerships(): HasMany
    {
        return $this->hasMany(\App\Models\ConsolidationOwnership::class);
    }
}
