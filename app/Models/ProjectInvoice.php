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
        'gross_amount', 'retention_amount', 'retention_released',
        'retention_released_flag', 'retention_release_date',
        'termin_number', 'progress_pct',
        'milestone_id', 'status', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start'           => 'date',
            'period_end'             => 'date',
            'hours'                  => 'decimal:2',
            'hourly_rate'            => 'decimal:2',
            'labor_amount'           => 'decimal:2',
            'expense_amount'         => 'decimal:2',
            'total_amount'           => 'decimal:2',
            'gross_amount'           => 'decimal:2',
            'retention_amount'       => 'decimal:2',
            'retention_released'     => 'decimal:2',
            'retention_released_flag'=> 'boolean',
            'retention_release_date' => 'date',
            'progress_pct'           => 'decimal:2',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function milestone(): BelongsTo { return $this->belongsTo(ProjectMilestone::class, 'milestone_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function timesheets(): HasMany { return $this->hasMany(Timesheet::class, 'project_invoice_id'); }

    /**
     * Outstanding retention (held but not yet released).
     */
    public function retentionOutstanding(): float
    {
        return max(0, (float) $this->retention_amount - (float) $this->retention_released);
    }

    /**
     * Is retention fully released?
     */
    public function isRetentionReleased(): bool
    {
        return $this->retention_released_flag || $this->retentionOutstanding() <= 0;
    }
}
