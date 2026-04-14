<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationChannel extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'channel',
        'phone_number',
        'bot_token',
        'api_key',
        'api_secret',
        'configuration',
        'is_active',
        'is_default',
        'messages_sent_today',
        'daily_limit',
        'last_message_at',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'messages_sent_today' => 'integer',
        'daily_limit' => 'integer',
        'last_message_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function messages()
    {
        return $this->hasMany(MessageLog::class, 'channel_id');
    }
}