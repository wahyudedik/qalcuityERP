<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceSyncLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'channel_id',
        'mapping_id',
        'type',
        'status',
        'error_message',
        'attempt_count',
        'next_retry_at',
        'payload',
        'response',
        'sync_id', // BUG-API-003 FIX: Track sync batch ID
        'data_before', // BUG-API-003 FIX: Store data before sync
        'data_after', // BUG-API-003 FIX: Store data after sync
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
        'next_retry_at' => 'datetime',
        'attempt_count' => 'integer',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(EcommerceChannel::class, 'channel_id');
    }

    public function mapping(): BelongsTo
    {
        return $this->belongsTo(EcommerceProductMapping::class, 'mapping_id');
    }
}
