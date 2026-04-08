<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiLearnedPattern extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'user_id',
        'pattern_type',
        'entity_type',
        'entity_id',
        'pattern_data',
        'confidence',
        'analyzed_at',
    ];

    protected $casts = [
        'pattern_data' => 'array',
        'confidence'   => 'float',
        'analyzed_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
