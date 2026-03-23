<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IntercompanyTransaction extends Model
{
    protected $fillable = [
        'company_group_id', 'from_tenant_id', 'to_tenant_id',
        'type', 'reference', 'amount', 'currency_code',
        'description', 'status', 'date',
    ];

    protected $casts = ['date' => 'date'];

    public function group(): BelongsTo      { return $this->belongsTo(CompanyGroup::class, 'company_group_id'); }
    public function fromTenant(): BelongsTo { return $this->belongsTo(Tenant::class, 'from_tenant_id'); }
    public function toTenant(): BelongsTo   { return $this->belongsTo(Tenant::class, 'to_tenant_id'); }
}
