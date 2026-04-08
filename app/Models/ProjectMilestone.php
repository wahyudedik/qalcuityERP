<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMilestone extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'project_id', 'tenant_id', 'name', 'description', 'amount',
        'percentage', 'due_date', 'status', 'completed_by',
        'completed_at', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'percentage'   => 'decimal:2',
            'due_date'     => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function completedByUser(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
}
