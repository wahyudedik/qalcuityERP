<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TableReservation extends Model
{
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'table_id',
        'guest_id',
        'guest_name',
        'guest_phone',
        'party_size',
        'reservation_date',
        'reservation_time',
        'status',
        'special_requests',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'party_size' => 'integer',
            'reservation_date' => 'date',
            'reservation_time' => 'datetime:H:i',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(RestaurantTable::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if reservation is for today
     */
    public function isForToday(): bool
    {
        return $this->reservation_date->isToday();
    }

    /**
     * Mark as seated
     */
    public function markAsSeated(): void
    {
        $this->update(['status' => 'seated']);
        $this->table?->occupy();
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => 'completed']);
        $this->table?->release();
    }
}
