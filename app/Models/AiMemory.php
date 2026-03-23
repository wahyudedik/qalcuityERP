<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMemory extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'key', 'value', 'frequency', 'last_seen_at',
    ];

    protected $casts = [
        'value'        => 'array',
        'last_seen_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
