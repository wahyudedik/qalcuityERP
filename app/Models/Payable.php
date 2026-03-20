<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Payable extends Model
{
    protected $fillable = [
        'tenant_id', 'purchase_order_id', 'supplier_id', 'number',
        'total_amount', 'paid_amount', 'remaining_amount', 'status', 'due_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'due_date'         => 'date',
            'total_amount'     => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseOrder(): BelongsTo { return $this->belongsTo(PurchaseOrder::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function payments(): MorphMany { return $this->morphMany(Payment::class, 'payable'); }

    /**
     * Recalculate paid_amount, remaining_amount, dan status dari total payments.
     */
    public function updatePaymentStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $paid = min($paid, $this->total_amount);

        $this->paid_amount      = $paid;
        $this->remaining_amount = $this->total_amount - $paid;
        $this->status           = match (true) {
            $paid <= 0                   => 'unpaid',
            $paid >= $this->total_amount => 'paid',
            default                      => 'partial',
        };
        $this->save();
    }

    /** Hari keterlambatan (negatif = belum jatuh tempo) */
    public function daysOverdue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false) * -1;
    }
}
