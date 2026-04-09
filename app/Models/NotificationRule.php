<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'event_type',
        'conditions',
        'channels',
        'priority',
        'recipients',
        'template',
        'is_active',
        'cooldown_minutes',
        'max_notifications_per_day',
    ];

    protected $casts = [
        'conditions' => 'array',
        'channels' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'cooldown_minutes' => 'integer',
        'max_notifications_per_day' => 'integer',
    ];

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
