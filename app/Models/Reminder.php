<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reminder extends Model
{
    protected $fillable = [
        'tenant_id', 'user_id', 'title', 'notes',
        'remind_at', 'status', 'channel', 'related_type', 'related_id',
    ];

    protected $casts = [
        'remind_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }

    public function scopePending($q) { return $q->where('status', 'pending'); }
    public function scopeDue($q) { return $q->where('remind_at', '<=', now()); }
}
