<?php

namespace App\Models;

use App\Services\FbInventoryService;
use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FbOrder extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'order_number',
        'order_type',
        'guest_id',
        'reservation_id',
        'room_number',
        'table_number',
        'created_by',
        'server_id',
        'status',
        'subtotal',
        'tax_amount',
        'service_charge',
        'discount_amount',
        'total_amount',
        'special_instructions',
        'ordered_at',
        'confirmed_at',
        'prepared_at',
        'served_at',
        'completed_at',
        'payment_status',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_charge' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'ordered_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'prepared_at' => 'datetime',
            'served_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(Guest::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'server_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(FbOrderItem::class, 'order_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FbPayment::class, 'fb_order_id');
    }

    /**
     * Generate unique order number
     */
    public static function generateOrderNumber(string $type): string
    {
        $prefix = match ($type) {
            'restaurant_dine_in' => 'DIN',
            'restaurant_takeaway' => 'TKO',
            'room_service' => 'RS',
            'minibar' => 'MB',
            'banquet' => 'BNQ',
            default => 'ORD',
        };

        $date = now()->format('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;

        return "{$prefix}-{$date}-".str_pad($count, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate totals
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('subtotal');
        $this->tax_amount = $this->subtotal * 0.10; // 10% tax
        $this->service_charge = $this->subtotal * 0.05; // 5% service charge
        $this->total_amount = $this->subtotal + $this->tax_amount + $this->service_charge - $this->discount_amount;
        $this->save();
    }

    /**
     * Update order status
     */
    public function updateStatus(string $newStatus): void
    {
        $field = match ($newStatus) {
            'confirmed' => 'confirmed_at',
            'preparing' => null,
            'ready' => 'prepared_at',
            'served' => 'served_at',
            'completed' => 'completed_at',
            default => null,
        };

        $data = ['status' => $newStatus];
        if ($field) {
            $data[$field] = now();
        }

        $this->update($data);

        ActivityLog::record(
            'fb_order_status_updated',
            "Order #{$this->order_number} status changed to {$newStatus}",
            $this,
            ['order_id' => $this->id, 'new_status' => $newStatus]
        );
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }

    /**
     * Complete order and trigger stock deduction
     */
    public function completeOrder(): void
    {
        $this->updateStatus('completed');

        // Trigger automatic stock deduction
        $inventoryService = new FbInventoryService($this->tenant_id);
        try {
            $inventoryService->deductStockForOrder($this);
        } catch (\Exception $e) {
            // Log error but don't fail the order completion
            \Log::error('Stock deduction failed for order: '.$this->order_number, [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
