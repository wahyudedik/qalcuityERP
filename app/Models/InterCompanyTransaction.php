<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InterCompanyTransaction extends Model
{
    use BelongsToTenant;
use HasFactory;

    protected $fillable = [
        'company_group_id',
        'from_tenant_id',
        'to_tenant_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'amount',
        'currency',
        'exchange_rate',
        'transaction_date',
        'due_date',
        'status',
        'description',
        'line_items',
        'created_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'exchange_rate' => 'decimal:6',
        'transaction_date' => 'date',
        'due_date' => 'date',
        'line_items' => 'array',
        'approved_at' => 'datetime',
    ];

    public function companyGroup()
    {
        return $this->belongsTo(CompanyGroup::class);
    }
    public function fromTenant()
    {
        return $this->belongsTo(Tenant::class, 'from_tenant_id');
    }
    public function toTenant()
    {
        return $this->belongsTo(Tenant::class, 'to_tenant_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
