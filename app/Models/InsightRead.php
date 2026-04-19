<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsightRead extends Model
{
    protected $fillable = [
        'insight_id',
        'user_id',
        'status',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
        ];
    }

    public function insight(): BelongsTo
    {
        return $this->belongsTo(ProactiveInsight::class, 'insight_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
