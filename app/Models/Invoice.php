<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model
{
    protected $fillable = [
        'tenant_id', 'sales_order_id', 'customer_id', 'number',
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
    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function payments(): MorphMany { return $this->morphMany(Payment::class, 'payable'); }

    /**
     * Recalculate paid_amount, remaining_amount, dan status dari total payments.
     * Dipanggil setelah setiap Payment baru disimpan.
     */
    public function updatePaymentStatus(): void
    {
        $paid = $this->payments()->sum('amount');
        $paid = min($paid, $this->total_amount); // tidak boleh melebihi total

        $this->paid_amount      = $paid;
        $this->remaining_amount = $this->total_amount - $paid;
        $this->status           = match (true) {
            $paid <= 0                          => 'unpaid',
            $paid >= $this->total_amount        => 'paid',
            default                             => 'partial',
        };
        $this->save();
    }

    /** Hari keterlambatan (negatif = belum jatuh tempo) */
    public function daysOverdue(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_date->startOfDay(), false) * -1;
    }
}
