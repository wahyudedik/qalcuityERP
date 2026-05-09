<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockMovement extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'livestock_herd_id', 'tenant_id', 'user_id', 'date', 'type',
        'quantity', 'count_after', 'weight_kg', 'price_total',
        'reason', 'destination', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'weight_kg' => 'decimal:3',
            'price_total' => 'decimal:2',
        ];
    }

    public const TYPE_LABELS = [
        'purchase' => '🛒 Pembelian/Masuk',
        'birth' => '🐣 Kelahiran',
        'transfer_in' => '📥 Pindah Masuk',
        'transfer_out' => '📤 Pindah Keluar',
        'death' => '💀 Kematian',
        'cull' => '🔻 Afkir',
        'sold' => '💰 Dijual',
        'harvested' => '🔪 Dipotong/Panen',
        'adjustment' => '📝 Koreksi',
    ];

    public const INBOUND_TYPES = ['purchase', 'birth', 'transfer_in', 'adjustment'];

    public const OUTBOUND_TYPES = ['death', 'cull', 'sold', 'harvested', 'transfer_out'];

    public function herd(): BelongsTo
    {
        return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->type] ?? $this->type;
    }

    public function isInbound(): bool
    {
        return in_array($this->type, self::INBOUND_TYPES);
    }
}
