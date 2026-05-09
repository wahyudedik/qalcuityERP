<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property Carbon $start_date
 * @property Carbon $end_date
 */
class GroupBooking extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'organizer_guest_id',
        'group_name',
        'group_code',
        'type',
        'start_date',
        'end_date',
        'total_rooms',
        'total_guests',
        'total_amount',
        'paid_amount',
        'payment_status',
        'status',
        'notes',
        'benefits',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_rooms' => 'integer',
            'total_guests' => 'integer',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'benefits' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(Guest::class, 'organizer_guest_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Generate unique group code
     */
    public static function generateGroupCode(int $tenantId): string
    {
        $prefix = 'GRP';
        $year = date('Y');
        $count = static::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->count() + 1;

        return "{$prefix}/{$year}/".str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Check if group is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if group is confirmed
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Get payment completion percentage
     */
    public function getPaymentPercentageAttribute(): float
    {
        if ($this->total_amount == 0) {
            return 0;
        }

        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    /**
     * Get outstanding balance
     */
    public function getBalanceAttribute(): float
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Add a benefit to the group
     */
    public function addBenefit(string $benefit): void
    {
        $benefits = $this->benefits ?? [];
        $benefits[] = $benefit;
        $this->update(['benefits' => $benefits]);
    }
}
