<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use BelongsToTenant;
    protected $fillable = ['user_id', 'tenant_id', 'endpoint', 'p256dh', 'auth'];

    public function user() { return $this->belongsTo(User::class); }
}
