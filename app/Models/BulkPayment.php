<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BulkPayment extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'number', 'type', 'party_id', 'party_type',
        'payment_date', 'total_amount', 'applied_amount', 'overpayment',
        'payment_method', 'status', 'created_by', 'notes',
    ];

    protected $casts = [
        'payment_date'   => 'date',
        'total_amount'   => 'decimal:2',
        'applied_amount' => 'decimal:2',
        'overpayment'    => 'decimal:2',
    ];

    public function tenant(): BelongsTo  { return $this->belongsTo(Tenant::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function party(): MorphTo     { return $this->morphTo('party'); }
    public function items(): HasMany     { return $this->hasMany(BulkPaymentItem::class); }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft'     => 'Draft',
            'applied'   => 'Diterapkan',
            'cancelled' => 'Dibatalkan',
            default     => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'draft'     => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            'applied'   => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            default     => 'bg-gray-100 text-gray-500',
        };
    }
}
