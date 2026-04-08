<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiTarget extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'metric', 'label', 'period',
        'target', 'actual', 'unit', 'color', 'is_active',
    ];

    protected $casts = [
        'target'    => 'decimal:2',
        'actual'    => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function achievementPercent(): float
    {
        if ((float) $this->target <= 0) return 0;
        return min(round((float) $this->actual / (float) $this->target * 100, 1), 999);
    }

    public function statusColor(): string
    {
        $pct = $this->achievementPercent();
        if ($pct >= 100) return 'emerald';
        if ($pct >= 75)  return 'blue';
        if ($pct >= 50)  return 'amber';
        return 'red';
    }
}
