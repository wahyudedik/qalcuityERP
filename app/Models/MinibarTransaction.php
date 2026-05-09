<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MinibarTransaction extends Model
{
    use AuditsChanges, SoftDeletes;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'reservation_id',
        'room_number',
        'menu_item_id',
        'quantity_consumed',
        'unit_price',
        'total_charge',
        'consumption_date',
        'recorded_by',
        'billing_status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity_consumed' => 'integer',
            'unit_price' => 'decimal:2',
            'total_charge' => 'decimal:2',
            'consumption_date' => 'date',
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

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * Calculate total charge
     */
    public function calculateTotal(): void
    {
        $this->total_charge = $this->quantity_consumed * $this->unit_price;
        $this->save();
    }
}
