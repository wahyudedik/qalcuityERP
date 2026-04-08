<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaReview extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'booking_id',
        'guest_id',
        'therapist_id',
        'treatment_id',
        'rating',
        'comment',
        'ratings_breakdown',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'ratings_breakdown' => 'array',
            'is_published' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(SpaBooking::class, 'booking_id');
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(HotelGuest::class, 'guest_id');
    }

    public function therapist(): BelongsTo
    {
        return $this->belongsTo(SpaTherapist::class, 'therapist_id');
    }

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(SpaTreatment::class, 'treatment_id');
    }
}
