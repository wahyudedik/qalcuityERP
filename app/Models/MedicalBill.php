<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalBill extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'patient_id',
        'visit_id',
        'admission_id',
        'bill_number',
        'bill_date',
        'due_date',
        'subtotal',
        'discount_amount',
        'discount_percentage',
        'tax_amount',
        'total_amount',
        'has_insurance',
        'insurance_provider_id',
        'policy_number',
        'group_number',
        'insurance_coverage',
        'insurance_deductible',
        'patient_payable',
        'amount_paid',
        'balance_due',
        'payment_status',
        'billing_status',
        'financial_class',
        'finalized_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'has_insurance' => 'boolean',
        'insurance_coverage' => 'decimal:2',
        'insurance_deductible' => 'decimal:2',
        'patient_payable' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'finalized_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Scope: By payment status
     */
    public function scopePaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: Overdue bills
     */
    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    /**
     * Scope: Has balance
     */
    public function scopeHasBalance($query)
    {
        return $query->where('balance_due', '>', 0);
    }

    /**
     * Scope: By financial class
     */
    public function scopeFinancialClass($query, $class)
    {
        return $query->where('financial_class', $class);
    }

    /**
     * Calculate totals
     */
    public function calculateTotals()
    {
        $subtotal = $this->items->sum('total');
        $discount = $subtotal * ($this->discount_percentage / 100);
        $afterDiscount = $subtotal - $discount;
        $tax = $afterDiscount * 0.11; // 11% tax (configurable)
        $total = $afterDiscount + $tax;

        $this->update([
            'subtotal' => $subtotal,
            'discount_amount' => $discount,
            'tax_amount' => $tax,
            'total_amount' => $total,
            'patient_payable' => $total - $this->insurance_coverage,
            'balance_due' => $total - $this->insurance_coverage - $this->amount_paid,
        ]);
    }

    /**
     * Check if bill is fully paid
     */
    public function isFullyPaid()
    {
        return $this->balance_due <= 0;
    }

    /**
     * Check if bill is overdue
     */
    public function isOverdue()
    {
        return $this->due_date && $this->due_date < now() && !$this->isFullyPaid();
    }

    /**
     * Get payment percentage
     */
    public function getPaymentPercentageAttribute()
    {
        if ($this->patient_payable > 0) {
            return round(($this->amount_paid / $this->patient_payable) * 100, 2);
        }
        return 0;
    }

    /**
     * Get aging days
     */
    public function getAgingDaysAttribute()
    {
        return now()->diffInDays($this->bill_date);
    }

    /**
     * Get aging bucket
     */
    public function getAgingBucketAttribute()
    {
        $days = $this->aging_days;

        if ($days <= 30)
            return 'current';
        if ($days <= 60)
            return '31-60';
        if ($days <= 90)
            return '61-90';
        if ($days <= 120)
            return '91-120';
        return '120+';
    }
}
