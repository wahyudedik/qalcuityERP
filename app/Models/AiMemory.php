<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMemory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'key',
        'value',
        'frequency',
        'last_seen_at',
        'first_observed_at',
        'confidence_score',
        'metadata',
    ];

    protected $casts = [
        'value' => 'array',
        'last_seen_at' => 'datetime',
        'first_observed_at' => 'datetime',
        'confidence_score' => 'float',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
