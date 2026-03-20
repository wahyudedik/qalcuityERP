<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTask extends Model
{
    protected $fillable = [
        'project_id', 'tenant_id', 'assigned_to', 'name', 'description',
        'status', 'weight', 'due_date', 'budget', 'actual_cost',
    ];

    protected function casts(): array
    {
        return [
            'due_date'    => 'date',
            'budget'      => 'decimal:2',
            'actual_cost' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
}
