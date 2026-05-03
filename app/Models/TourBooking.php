<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourBooking extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'booking_number',
        'tour_package_id',
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'departure_date',
        'adults',
        'children',
        'infants',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'currency',
        'status',
        'payment_status',
        'paid_amount',
        'payment_due_date',
        'special_requests',
        'notes',
        'assigned_guide',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected $casts = [
        'adults' => 'integer',
        'children' => 'integer',
        'infants' => 'integer',
        'total_pax' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'departure_date' => 'date',
        'payment_due_date' => 'date',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(BookingPassenger::class);
    }

    public function visaApplications(): HasMany
    {
        return $this->hasMany(VisaApplication::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(TravelDocument::class);
    }

    public function assignedGuide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_guide');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accessors
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'blue',
            'paid' => 'green',
            'cancelled' => 'red',
            'completed' => 'gray',
            'refunded' => 'orange',
            default => 'gray'
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'red',
            'partial' => 'yellow',
            'paid' => 'green',
            'refunded' => 'gray',
            default => 'gray'
        };
    }

    public function getBalanceDueAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    public function getIsFullyPaidAttribute(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Scopes
     */
    public function scopeUpcoming($query)
    {
        return $query->where('departure_date', '>=', now())
            ->whereIn('status', ['pending', 'confirmed', 'paid']);
    }

    public function scopePast($query)
    {
        return $query->where('departure_date', '<', now());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Methods
     */
    public function confirm(): void
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
    }

    public function addPayment(float $amount): void
    {
        $newPaidAmount = $this->paid_amount + $amount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'payment_status' => $newPaidAmount >= $this->total_amount ? 'paid' : 'partial',
        ]);

        // Create journal entry for the payment if accounting integration is available
        $this->createPaymentJournalEntry($amount);
    }

    /**
     * Create a journal entry for a tour booking payment.
     * Debits Cash/Bank, Credits Tour Revenue.
     */
    protected function createPaymentJournalEntry(float $amount): void
    {
        try {
            $journalService = app(\App\Services\JournalService::class);

            $journalService->createJournalEntry([
                'tenant_id' => $this->tenant_id,
                'date' => now()->toDateString(),
                'description' => "Pembayaran booking tour #{$this->booking_number}",
                'reference_type' => 'tour_booking',
                'reference_id' => $this->id,
                'source' => 'tour_travel',
                'lines' => [
                    [
                        'description' => "Pembayaran tour - {$this->customer_name}",
                        'debit' => $amount,
                        'credit' => 0,
                    ],
                    [
                        'description' => "Pendapatan tour - {$this->tourPackage?->name}",
                        'debit' => 0,
                        'credit' => $amount,
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            // Log but don't fail the payment if journal creation fails
            \Illuminate\Support\Facades\Log::warning('Tour booking journal entry creation failed', [
                'booking_id' => $this->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the CRM lead associated with this booking's customer (if converted from a lead).
     */
    public function crmLead()
    {
        return $this->hasOne(\App\Models\CrmLead::class, 'converted_to_customer_id', 'customer_id');
    }
}
