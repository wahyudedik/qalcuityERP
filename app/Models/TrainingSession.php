<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingSession extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'training_program_id', 'start_date', 'end_date',
        'location', 'trainer', 'max_participants', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function tenant(): BelongsTo          { return $this->belongsTo(Tenant::class); }
    public function program(): BelongsTo         { return $this->belongsTo(TrainingProgram::class, 'training_program_id'); }
    public function participants(): HasMany       { return $this->hasMany(TrainingParticipant::class); }

    public function isFull(): bool
    {
        return $this->max_participants > 0
            && $this->participants()->count() >= $this->max_participants;
    }
}
