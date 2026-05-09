<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class DigitalSignature extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'user_id', 'model_type', 'model_id',
        'signature_data', 'ip_address', 'signed_at',
    ];

    protected $casts = ['signed_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo('model');
    }
}
