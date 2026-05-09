<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaBookingItem extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'booking_id',
        'treatment_id',
        'sequence_order',
        'scheduled_start',
        'scheduled_end',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
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

    public function treatment(): BelongsTo
    {
        return $this->belongsTo(SpaTreatment::class, 'treatment_id');
    }
}
