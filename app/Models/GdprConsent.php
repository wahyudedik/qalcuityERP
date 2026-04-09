<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GdprConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_type',
        'ip_address',
        'user_agent',
        'consented_at',
        'withdrawn_at',
        'is_active',
    ];

    protected $casts = [
        'consented_at' => 'datetime',
        'withdrawn_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
