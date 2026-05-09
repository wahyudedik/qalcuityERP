<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinibarInventory extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'room_number',
        'menu_item_id',
        'initial_stock',
        'current_stock',
        'minimum_stock',
        'last_restocked_at',
        'restocked_by',
    ];

    protected function casts(): array
    {
        return [
            'initial_stock' => 'integer',
            'current_stock' => 'integer',
            'minimum_stock' => 'integer',
            'last_restocked_at' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_number', 'number');
    }

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function restockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'restocked_by');
    }

    /**
     * Check if stock is low
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock;
    }

    /**
     * Restock item
     */
    public function restock(int $quantity, ?int $userId = null): void
    {
        $this->update([
            'current_stock' => $this->current_stock + $quantity,
            'initial_stock' => $this->initial_stock + $quantity,
            'last_restocked_at' => now(),
            'restocked_by' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Consume item
     */
    public function consume(int $quantity): bool
    {
        if ($this->current_stock < $quantity) {
            return false;
        }

        $this->decrement('current_stock', $quantity);

        return true;
    }
}
