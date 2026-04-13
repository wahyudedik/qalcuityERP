<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OfflineSyncConflict extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'entity_type',
        'entity_id',
        'local_id',
        'offline_timestamp',
        'server_state',
        'local_state',
        'offline_changes',
        'status',
        'resolution_strategy',
        'detected_at',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'offline_timestamp' => 'datetime',
        'server_state' => 'array',
        'local_state' => 'array',
        'detected_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function resolvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function entity()
    {
        return match ($this->entity_type) {
            'inventory' => ProductStock::find($this->entity_id),
            'sale' => SalesOrder::find($this->entity_id),
            'customer' => Customer::find($this->entity_id),
            default => null,
        };
    }

    public function resolveWithLocalWins(): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolution_strategy' => 'local_wins',
            'resolved_at' => now(),
        ]);
    }

    public function resolveWithServerWins(): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolution_strategy' => 'server_wins',
            'resolved_at' => now(),
        ]);
    }

    public function resolveWithMerge(): bool
    {
        return $this->update([
            'status' => 'resolved',
            'resolution_strategy' => 'merge',
            'resolved_at' => now(),
        ]);
    }

    public function discard(): bool
    {
        return $this->update([
            'status' => 'discarded',
            'resolved_at' => now(),
        ]);
    }
}
