<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MortalityLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'pond_id',
        'fishing_trip_id',
        'count',
        'total_weight',
        'cause_of_death',
        'symptoms',
        'action_taken',
        'reported_by_user_id',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'count' => 'integer',
            'total_weight' => 'decimal:2',
            'reported_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function pond(): BelongsTo
    {
        return $this->belongsTo(AquaculturePond::class, 'pond_id');
    }

    public function fishingTrip(): BelongsTo
    {
        return $this->belongsTo(FishingTrip::class, 'fishing_trip_id');
    }

    public function reportedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by_user_id');
    }
}
