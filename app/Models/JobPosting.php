<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobPosting extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'title', 'department', 'location', 'type',
        'description', 'requirements', 'salary_min', 'salary_max',
        'quota', 'deadline', 'status', 'created_by',
    ];

    protected $casts = ['deadline' => 'date'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'full_time' => 'Full Time',
            'part_time' => 'Part Time',
            'contract' => 'Kontrak',
            'internship' => 'Magang',
            default => $this->type,
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'open' => 'Buka',
            'closed' => 'Tutup',
            default => $this->status,
        };
    }

    public function hiredCount(): int
    {
        return $this->applications()->where('stage', 'hired')->count();
    }
}
