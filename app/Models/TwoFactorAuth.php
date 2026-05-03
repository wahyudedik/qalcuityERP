<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TwoFactorAuth extends Model
{
    use HasFactory;

    protected $table = 'two_factor_auths';

    protected $fillable = [
        'user_id',
        'secret_key',
        'recovery_codes',
        'enabled',
        'method',
        'enabled_at',
        'last_used_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'enabled_at' => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = [
        'secret_key',
        'recovery_codes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
