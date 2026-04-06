<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTip extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'tip_category',
        'tip_title',
        'tip_content',
        'related_module',
        'dismissed',
        'dismissed_at',
        'shown_at',
        'times_shown',
    ];

    protected $casts = [
        'dismissed' => 'boolean',
        'dismissed_at' => 'datetime',
        'shown_at' => 'datetime',
        'times_shown' => 'integer',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dismiss(): void
    {
        $this->update([
            'dismissed' => true,
            'dismissed_at' => now(),
        ]);
    }

    public function recordShown(): void
    {
        $this->increment('times_shown');
        $this->update(['shown_at' => now()]);
    }
}
