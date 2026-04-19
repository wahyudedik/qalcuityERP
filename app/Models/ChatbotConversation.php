<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatbotConversation extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'user_message',
        'bot_response',
        'intent_detected',
        'confidence_score',
        'was_helpful',
        'feedback_notes',
        'context',
    ];

    protected $casts = [
        'confidence_score' => 'decimal:4',
        'was_helpful' => 'boolean',
        'context' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}