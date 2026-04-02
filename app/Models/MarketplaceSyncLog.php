<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplaceSyncLog extends Model
{
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
