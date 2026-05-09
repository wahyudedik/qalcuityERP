<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LoyaltyProgram extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'points_per_idr', 'idr_per_point',
        'min_redeem_points', 'expiry_days', 'is_active',
    ];

    protected $casts = ['points_per_idr' => 'float', 'idr_per_point' => 'float', 'is_active' => 'boolean'];

    public function tiers()
    {
        return $this->hasMany(LoyaltyTier::class, 'program_id');
    }

    public function points()
    {
        return $this->hasMany(LoyaltyPoint::class, 'program_id');
    }

    public function calculatePoints(float $amount): int
    {
        return (int) floor($amount * $this->points_per_idr);
    }
}
