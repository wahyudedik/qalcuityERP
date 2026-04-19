<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseRequisition extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CONVERTED = 'converted';

    const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CONVERTED,
    ];

    protected $fillable = [
        'tenant_id',
        'requested_by',
        'approved_by',
        'number',
        'department',
        'required_date',
        'purpose',
        'status',
        'rejection_reason',
        'approved_at',
        'estimated_total',
    ];

    protected $casts = [
        'required_date' => 'date',
        'approved_at' => 'datetime',
        'estimated_total' => 'decimal:2',
    ];

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseRequisitionItem::class);
    }
    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'converted' => 'Sudah Jadi PO',
            default => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
            'rejected' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
            'converted' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            default => 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400',
        };
    }
}