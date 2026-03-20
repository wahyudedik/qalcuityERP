<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BotMessage extends Model
{
    protected $fillable = [
        'tenant_id', 'platform', 'direction', 'recipient',
        'message', 'status', 'event_type', 'payload', 'sent_at',
    ];

    protected $casts = ['payload' => 'array', 'sent_at' => 'datetime'];
}
