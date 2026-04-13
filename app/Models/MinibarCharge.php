<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinibarCharge extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'room_id',
        'item_id',
        'quantity',
        'unit_price',
        'total',
        'consumed_at',
        'status',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total' => 'decimal:2',
        'consumed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function item()
    {
        return $this->belongsTo(MinibarItem::class, 'item_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function markAsCharged(): void
    {
        $this->update(['status' => 'charged']);
    }

    public function void(string $reason = ''): void
    {
        $this->update([
            'status' => 'voided',
            'notes' => $reason,
        ]);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'amber',
            'charged' => 'green',
            'voided' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'charged' => 'Charged',
            'voided' => 'Voided',
            default => ucfirst($this->status),
        };
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCharged($query)
    {
        return $query->where('status', 'charged');
    }
}
