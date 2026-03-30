<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskVolumeLog extends Model
{
    protected $fillable = [
        'project_task_id', 'tenant_id', 'user_id',
        'volume', 'cumulative', 'date', 'description', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'volume'     => 'decimal:3',
            'cumulative' => 'decimal:3',
            'date'       => 'date',
        ];
    }

    public function task(): BelongsTo { return $this->belongsTo(ProjectTask::class, 'project_task_id'); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
