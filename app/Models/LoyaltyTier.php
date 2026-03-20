<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    protected $fillable = ['tenant_id', 'program_id', 'name', 'min_points', 'multiplier', 'color'];
    protected $casts = ['multiplier' => 'float'];
}
