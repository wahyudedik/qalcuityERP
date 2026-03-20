<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPoint extends Model
{
    protected $fillable = [
        'tenant_id', 'customer_id', 'program_id', 'total_points',
        'lifetime_points', 'tier', 'tier_updated_at',
    ];

    protected $casts = ['tier_updated_at' => 'datetime'];

    public function customer() { return $this->belongsTo(Customer::class); }
    public function program() { return $this->belongsTo(LoyaltyProgram::class); }
    public function transactions() { return $this->hasMany(LoyaltyTransaction::class, 'customer_id', 'customer_id'); }
}
