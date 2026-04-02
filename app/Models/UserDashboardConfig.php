<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDashboardConfig extends Model
{
    protected $fillable = ['user_id', 'widgets'];

    protected function casts(): array
    {
        return ['widgets' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
