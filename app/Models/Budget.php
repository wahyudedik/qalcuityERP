<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use App\Traits\AuditsChanges;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use BelongsToTenant;
    use AuditsChanges;
    protected $fillable = [
        'tenant_id',
        'name',
        'department',
        'period',
        'period_type',
        'amount',
        'realized',
        'category',
        'status',
        'notes',
    ];

    protected $casts = ['amount' => 'float', 'realized' => 'float'];

    public function getVarianceAttribute(): float
    {
        return $this->amount - $this->realized;
    }
    public function getUsagePercentAttribute(): float
    {
        return $this->amount > 0 ? round($this->realized / $this->amount * 100, 1) : 0;
    }
}