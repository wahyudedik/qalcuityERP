<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LinenMovement extends Model
{
    use BelongsToTenant;
    use SoftDeletes, AuditsChanges;

    protected $fillable = [
        'tenant_id',
        'linen_inventory_id',
        'movement_type',
        'quantity',
        'room_id',
        'from_location',
        'to_location',
        'reason',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function linenInventory(): BelongsTo
    {
        return $this->belongsTo(LinenInventory::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'from_location');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(User::class, 'to_location');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Boot method to handle movement logic
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($movement) {
            $movement->updateInventory();
        });
    }

    /**
     * Update inventory quantities based on movement
     */
    public function updateInventory(): void
    {
        $inventory = $this->linenInventory;
        $qty = $this->quantity;

        switch ($this->movement_type) {
            case 'add':
                $inventory->increment('available_quantity', $qty);
                break;

            case 'remove':
                $inventory->decrement('available_quantity', $qty);
                break;

            case 'transfer':
                // Transfer from available to in_use
                $inventory->decrement('available_quantity', $qty);
                $inventory->increment('in_use_quantity', $qty);
                break;

            case 'damage':
                $inventory->decrement('available_quantity', $qty);
                $inventory->increment('damaged_quantity', $qty);
                break;

            case 'laundry_out':
                $inventory->decrement('available_quantity', $qty);
                $inventory->increment('soiled_quantity', $qty);
                break;

            case 'laundry_in':
                $inventory->decrement('soiled_quantity', $qty);
                $inventory->increment('available_quantity', $qty);
                break;
        }

        $inventory->updateQuantities();
    }
}
