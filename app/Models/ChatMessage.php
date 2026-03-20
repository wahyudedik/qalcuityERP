<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatMessage extends Model
{
    protected $fillable = ['chat_session_id', 'role', 'content', 'model_used', 'token_count', 'function_calls'];

    protected function casts(): array
    {
        return ['function_calls' => 'array'];
    }

    public function session(): BelongsTo { return $this->belongsTo(ChatSession::class); }
}
