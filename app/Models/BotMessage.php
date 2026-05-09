<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BotMessage extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform', 'direction', 'recipient',
        'message', 'status', 'event_type', 'payload', 'sent_at',
    ];

    protected $casts = ['payload' => 'array', 'sent_at' => 'datetime'];
}
