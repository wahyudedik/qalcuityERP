<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timesheet extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'project_id', 'tenant_id', 'user_id',
        'date', 'hours', 'description', 'hourly_rate',
        'billing_status', 'project_invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'hours' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function laborCost(): float
    {
        return (float) $this->hours * (float) $this->hourly_rate;
    }
}
