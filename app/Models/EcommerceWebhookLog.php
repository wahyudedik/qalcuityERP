<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class EcommerceWebhookLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'channel_id',
        'platform',
        'event_type',
        'payload',
        'signature',
        'is_valid',
        'processed_at',
        'error_message',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_valid' => 'boolean',
        'processed_at' => 'datetime',
    ];

    // ─── Relations ────────────────────────────────────────────────

    public function channel()
    {
        return $this->belongsTo(EcommerceChannel::class, 'channel_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
