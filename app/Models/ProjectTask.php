<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectTask extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'project_id', 'tenant_id', 'assigned_to', 'name', 'description',
        'status', 'progress_method', 'target_volume', 'actual_volume', 'volume_unit',
        'weight', 'due_date', 'budget', 'actual_cost',
    ];

    protected function casts(): array
    {
        return [
            'due_date'      => 'date',
            'budget'        => 'decimal:2',
            'actual_cost'   => 'decimal:2',
            'target_volume' => 'decimal:3',
            'actual_volume' => 'decimal:3',
        ];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function volumeLogs(): HasMany { return $this->hasMany(TaskVolumeLog::class)->orderByDesc('date'); }

    /**
     * Is this task tracked by volume?
     */
    public function isVolumeTracked(): bool
    {
        return $this->progress_method === 'volume' && $this->target_volume > 0;
    }

    /**
     * Volume progress percentage (0-100).
     */
    public function volumeProgress(): float
    {
        if (!$this->isVolumeTracked()) return 0;
        return min(100, round(($this->actual_volume / $this->target_volume) * 100, 1));
    }

    /**
     * Remaining volume.
     */
    public function remainingVolume(): float
    {
        return max(0, (float) $this->target_volume - (float) $this->actual_volume);
    }

    /**
     * Effective progress for project calculation.
     * Status-based: done=100%, in_progress=50%, todo=0%
     * Volume-based: actual/target * 100
     */
    public function effectiveProgress(): float
    {
        if ($this->status === 'cancelled') return 0;

        if ($this->isVolumeTracked()) {
            return $this->volumeProgress();
        }

        return match ($this->status) {
            'done'        => 100,
            'in_progress' => 50,
            'review'      => 75,
            default       => 0,
        };
    }

    /**
     * Auto-update status based on volume progress.
     */
    public function syncStatusFromVolume(): void
    {
        if (!$this->isVolumeTracked()) return;

        $pct = $this->volumeProgress();
        $newStatus = match (true) {
            $pct >= 100 => 'done',
            $pct > 0    => 'in_progress',
            default     => $this->status,
        };

        if ($newStatus !== $this->status && $this->status !== 'cancelled') {
            $this->update(['status' => $newStatus]);
        }
    }
}
