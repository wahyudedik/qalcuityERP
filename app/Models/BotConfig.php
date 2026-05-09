<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class BotConfig extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'platform', 'token', 'webhook_url',
        'phone_number', 'chat_id', 'notification_events', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notification_events' => 'array',
    ];
}
