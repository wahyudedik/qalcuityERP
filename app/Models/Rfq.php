<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rfq extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'purchase_requisition_id',
        'created_by',
        'number',
        'issue_date',
        'deadline',
        'notes',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'deadline' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(RfqResponse::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function selectedResponse(): ?RfqResponse
    {
        return $this->responses()->where('is_selected', true)->first();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Terbuka',
            'closed' => 'Ditutup',
            'converted' => 'Sudah Jadi PO',
            default => ucfirst($this->status),
        };
    }
}
