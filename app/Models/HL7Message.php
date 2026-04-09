<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HL7Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'message_type',
        'direction',
        'source_system',
        'destination_system',
        'payload',
        'status',
        'error_message',
        'retry_count',
        'sent_at',
        'received_at',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'processed_at' => 'datetime',
        'retry_count' => 'integer',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(ExternalSystem::class, 'source_system');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(ExternalSystem::class, 'destination_system');
    }
}
