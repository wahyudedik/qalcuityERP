<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guest extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'guest_code',
        'name',
        'email',
        'phone',
        'id_type',
        'id_number',
        'address',
        'city',
        'country',
        'nationality',
        'date_of_birth',
        'preferred_language',
        'communication_preference',
        'vip_level',
        'notes',
        'preferences',
        'loyalty_points',
        'membership_since',
        'total_stays',
        'last_stay_at',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'last_stay_at' => 'datetime',
            'membership_since' => 'date',
            'total_stays' => 'integer',
            'loyalty_points' => 'integer',
            'preferences' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function checkInOuts(): HasMany
    {
        return $this->hasMany(CheckInOut::class);
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(GuestPreference::class);
    }

    public function organizedGroups(): HasMany
    {
        return $this->hasMany(GroupBooking::class, 'organizer_guest_id');
    }

    public function earlyLateRequests(): HasMany
    {
        return $this->hasMany(EarlyLateRequest::class);
    }

    public function walkInReservations(): HasMany
    {
        return $this->hasMany(WalkInReservation::class);
    }

    /**
     * Check if guest is VIP
     */
    public function isVip(): bool
    {
        return in_array($this->vip_level, ['silver', 'gold', 'platinum']);
    }

    /**
     * Get guest's preferred communication method
     */
    public function getPreferredCommunicationMethod(): string
    {
        return $this->communication_preference ?? 'email';
    }

    /**
     * Add loyalty points to guest
     */
    public function addLoyaltyPoints(int $points): void
    {
        $this->increment('loyalty_points', $points);
    }

    /**
     * Record a preference for this guest
     */
    public function recordPreference(string $category, string $key, ?string $value = null, int $priority = 1, bool $autoApply = true): GuestPreference
    {
        return $this->preferences()->create([
            'tenant_id' => $this->tenant_id,
            'category' => $category,
            'preference_key' => $key,
            'preference_value' => $value,
            'priority' => $priority,
            'is_auto_applied' => $autoApply,
        ]);
    }
}
