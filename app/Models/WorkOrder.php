<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkOrder extends Model
{
    protected $fillable = [
        'tenant_id', 'product_id', 'recipe_id', 'user_id',
        'number', 'target_quantity', 'unit', 'status',
        'material_cost', 'labor_cost', 'overhead_cost', 'total_cost',
        'started_at', 'completed_at', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'target_quantity' => 'decimal:3',
            'material_cost'   => 'decimal:2',
            'labor_cost'      => 'decimal:2',
            'overhead_cost'   => 'decimal:2',
            'total_cost'      => 'decimal:2',
            'started_at'      => 'datetime',
            'completed_at'    => 'datetime',
        ];
    }

    /**
     * Graf transisi status yang valid.
     * pending → in_progress | cancelled
     * in_progress → completed | cancelled
     * completed / cancelled → tidak bisa berubah
     */
    public const VALID_TRANSITIONS = [
        'pending'     => ['in_progress', 'cancelled'],
        'in_progress' => ['completed', 'cancelled'],
        'completed'   => [],
        'cancelled'   => [],
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function recipe(): BelongsTo { return $this->belongsTo(Recipe::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function outputs(): HasMany { return $this->hasMany(ProductionOutput::class); }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, self::VALID_TRANSITIONS[$this->status] ?? []);
    }

    /** Total good_qty dari semua output */
    public function totalGoodQty(): float
    {
        return (float) $this->outputs()->sum('good_qty');
    }

    /** Total reject_qty dari semua output */
    public function totalRejectQty(): float
    {
        return (float) $this->outputs()->sum('reject_qty');
    }

    /** Yield rate: good / (good + reject) * 100 */
    public function yieldRate(): ?float
    {
        $total = $this->totalGoodQty() + $this->totalRejectQty();
        return $total > 0 ? round(($this->totalGoodQty() / $total) * 100, 1) : null;
    }

    /** Biaya per unit good */
    public function costPerGoodUnit(): ?float
    {
        $good = $this->totalGoodQty();
        return $good > 0 ? round($this->total_cost / $good, 2) : null;
    }
}
