<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectInvoice extends Model
{
    protected $fillable = [
        'project_id', 'tenant_id', 'invoice_id', 'billing_type',
        'period_start', 'period_end', 'hours', 'hourly_rate',
        'labor_amount', 'expense_amount', 'total_amount',
        'milestone_id', 'status', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start'  => 'date',
            'period_end'    => 'date',
            'hours'         => 'decimal:2',
            'hourly_rate'   => 'decimal:2',
            'labor_amount'  => 'decimal:2',
            'expense_amount'=> 'decimal:2',
            'total_amount'  => 'decimal:2',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function milestone(): BelongsTo { return $this->belongsTo(ProjectMilestone::class, 'milestone_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class, 'project_invoice_id'); }
}
