<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransfer extends Model
{
    use BelongsToTenant;
use HasFactory;

    protected $fillable = [
        'company_group_id',
        'from_tenant_id',
        'to_tenant_id',
        'transfer_number',
        'transfer_date',
        'expected_arrival_date',
        'actual_arrival_date',
        'status',
        'shipping_method',
        'tracking_number',
        'shipping_cost',
        'notes',
        'created_by_user_id',
        'received_by_user_id',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'expected_arrival_date' => 'date',
        'actual_arrival_date' => 'date',
        'shipping_cost' => 'decimal:2',
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
    public function items()
    {
        return $this->hasMany(InventoryTransferItem::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }
}
