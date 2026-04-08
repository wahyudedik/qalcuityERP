<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockFeedLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'livestock_herd_id', 'tenant_id', 'user_id', 'date',
        'feed_type', 'quantity_kg', 'cost', 'population_at_feeding',
        'avg_body_weight_kg', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date'               => 'date',
            'quantity_kg'        => 'decimal:3',
            'cost'               => 'decimal:2',
            'avg_body_weight_kg' => 'decimal:3',
        ];
    }

    public function herd(): BelongsTo { return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id'); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    /** Feed per head (gram) */
    public function feedPerHead(): float
    {
        return $this->population_at_feeding > 0
            ? round($this->quantity_kg * 1000 / $this->population_at_feeding, 1)
            : 0;
    }
}
