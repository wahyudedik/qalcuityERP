<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CrmActivity extends Model
{
    protected $fillable = [
        'tenant_id', 'lead_id', 'user_id', 'type', 'description',
        'outcome', 'next_follow_up',
    ];

    protected $casts = ['next_follow_up' => 'date'];

    public function lead() { return $this->belongsTo(CrmLead::class); }
    public function user() { return $this->belongsTo(User::class); }
}
