<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTier extends Model
{
    use BelongsToTenant;

    protected $fillable = ['tenant_id', 'program_id', 'name', 'min_points', 'multiplier', 'color'];

    protected $casts = ['multiplier' => 'float'];
}
