<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BanquetEvent extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'event_number',
        'event_name',
        'description',
        'client_guest_id',
        'client_name',
        'client_phone',
        'client_email',
        'company_name',
        'event_type',
        'event_date',
        'start_time',
        'end_time',
        'expected_guests',
        'confirmed_guests',
        'venue_room',
        'setup_requirements',
        'menu_selection',
        'venue_rental_fee',
        'food_beverage_total',
        'additional_charges',
        'total_amount',
        'deposit_amount',
        'status',
        'assigned_coordinator',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'event_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'expected_guests' => 'integer',
            'confirmed_guests' => 'integer',
            'venue_rental_fee' => 'decimal:2',
            'food_beverage_total' => 'decimal:2',
            'additional_charges' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'menu_selection' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function clientGuest(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'client_guest_id');
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_coordinator');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(BanquetEventOrder::class, 'banquet_event_id');
    }

    /**
     * Generate unique event number
     */
    public static function generateEventNumber(): string
    {
        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return "BNQ-{$date}-".str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate total amount
     */
    public function calculateTotal(): void
    {
        $this->total_amount = $this->venue_rental_fee + $this->food_beverage_total + $this->additional_charges;
        $this->save();
    }

    /**
     * Check if deposit is paid
     */
    public function isDepositPaid(): bool
    {
        return $this->deposit_amount > 0;
    }

    /**
     * Get remaining balance
     */
    public function getRemainingBalanceAttribute(): float
    {
        return $this->total_amount - $this->deposit_amount;
    }
}
