<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAchievement extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'achievement_id',
        'current_progress',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'current_progress' => 'integer',
            'earned_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isEarned(): bool
    {
        return !is_null($this->earned_at);
    }

    public function progressPercent(): int
    {
        if ($this->isEarned()) return 100;
        $target = $this->achievement->requirement_value;
        return $target > 0 ? min(100, (int) round(($this->current_progress / $target) * 100)) : 0;
    }
}
