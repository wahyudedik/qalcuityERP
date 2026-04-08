<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationRoomChange extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'from_room_id',
        'to_room_id',
        'room_type_id',
        'change_type',
        'effective_date',
        'rate_difference',
        'reason',
        'notes',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'rate_difference' => 'decimal:2',
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

    public function fromRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }

    public function roomType(): BelongsTo
    {
        return $this->belongsTo(RoomType::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Check if this is an upgrade
     */
    public function isUpgrade(): bool
    {
        return $this->change_type === 'upgrade';
    }

    /**
     * Check if this is a downgrade
     */
    public function isDowngrade(): bool
    {
        return $this->change_type === 'downgrade';
    }
}
