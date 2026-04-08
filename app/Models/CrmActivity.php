<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'lead_id', 'user_id', 'type', 'description',
        'outcome', 'next_follow_up',
    ];

    protected $casts = ['next_follow_up' => 'date'];

    public function lead() { return $this->belongsTo(CrmLead::class); }
    public function user() { return $this->belongsTo(User::class); }
}
