<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WalkInReservation extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'guest_id',
        'walk_in_number',
        'arrival_time',
        'source',
        'is_new_guest',
        'special_circumstances',
        'handled_by',
    ];

    protected function casts(): array
    {
        return [
            'arrival_time' => 'datetime',
            'is_new_guest' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /**
     * Generate unique walk-in number
     */
    public static function generateWalkInNumber(int $tenantId): string
    {
        $prefix = 'WI';
        $year = date('Y');
        $month = date('m');
        $count = static::where('tenant_id', $tenantId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count() + 1;

        return "{$prefix}/{$year}/{$month}/".str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Check if walk-in is from new guest
     */
    public function isNewGuest(): bool
    {
        return $this->is_new_guest;
    }

    /**
     * Get source label
     */
    public function getSourceLabelAttribute(): string
    {
        $labels = [
            'phone' => 'Phone Call',
            'email' => 'Email',
            'website' => 'Website',
            'ota' => 'OTA',
            'referral' => 'Referral',
            'street_walk' => 'Street Walk',
        ];

        return $labels[$this->source] ?? ucfirst($this->source);
    }
}
