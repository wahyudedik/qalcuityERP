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
        'currency_code', 'currency_rate', 'tax_rate_id', 'tax_amount', 'subtotal_amount',
        // Task 35: State machine
        'posting_status', 'posted_by', 'posted_at', 'cancelled_by', 'cancelled_at', 'cancel_reason',
        // Task 36: Revision
        'revision_number', 'original_invoice_id',
        // Task 37: Numbering
        'doc_sequence', 'doc_year',
    ];

    protected function casts(): array
    {
        return [
            'due_date'         => 'date',
            'total_amount'     => 'decimal:2',
            'paid_amount'      => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'subtotal_amount'  => 'decimal:2',
            'tax_amount'       => 'decimal:2',
            'currency_rate'    => 'float',
            'posted_at'        => 'datetime',
            'cancelled_at'     => 'datetime',
        ];
    }

    /** Apakah invoice sudah posted (immutable) */
    public function isPosted(): bool
    {
        return $this->posting_status === 'posted';
    }

    /** Apakah invoice masih draft (bisa diedit) */
    public function isDraft(): bool
    {
        return ($this->posting_status ?? 'draft') === 'draft';
    }

    /** Label status posting */
    public function postingStatusLabel(): string
    {
        return match ($this->posting_status ?? 'draft') {
            'draft'     => 'Draft',
            'posted'    => 'Diposting',
            'cancelled' => 'Dibatalkan',
            'voided'    => 'Dibatalkan (Void)',
            default     => ucfirst($this->posting_status ?? 'draft'),
        };
    }

    /** Warna badge status posting */
    public function postingStatusColor(): string
    {
        return match ($this->posting_status ?? 'draft') {
            'draft'     => 'bg-gray-100 text-gray-600 dark:bg-white/10 dark:text-slate-400',
            'posted'    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'cancelled' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            'voided'    => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
            default     => 'bg-gray-100 text-gray-500',
        };
    }

    /** Revisi transaksi */
    public function revisions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TransactionRevision::class, 'model_id')
            ->where('model_type', self::class)
            ->orderBy('revision');
    }

    public function taxRate() { return $this->belongsTo(\App\Models\TaxRate::class); }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function salesOrder(): BelongsTo { return $this->belongsTo(SalesOrder::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function payments(): MorphMany { return $this->morphMany(Payment::class, 'payable'); }
    public function installments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InvoiceInstallment::class);
    }
    public function salesReturns(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalesReturn::class);
    }
    public function downPaymentApplications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DownPaymentApplication::class);
    }

    /** Aging bucket: 0-30, 31-60, 61-90, 90+ hari */
    public function agingBucket(): string
    {
        if ($this->status === 'paid') return 'paid';
        $days = $this->daysOverdue();
        if ($days <= 0)  return 'current';
        if ($days <= 30) return '1-30';
        if ($days <= 60) return '31-60';
        if ($days <= 90) return '61-90';
        return '90+';
    }

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
