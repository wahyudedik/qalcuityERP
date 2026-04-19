<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomatedBackup extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'backup_type',
        'status',
        'file_path',
        'file_size_mb',
        'tables_included',
        'records_count',
        'error_message',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'tables_included' => 'array',
        'records_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getBackupTypeNameAttribute(): string
    {
        return match ($this->backup_type) {
            'daily' => 'Daily Backup',
            'weekly' => 'Weekly Backup',
            'monthly' => 'Monthly Backup',
            'manual' => 'Manual Backup',
            'pre_change' => 'Pre-Change Backup',
            default => ucfirst($this->backup_type)
        };
    }

    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return now()->greaterThan($this->expires_at);
    }

    public function deleteFile(): void
    {
        if ($this->file_path && file_exists(storage_path($this->file_path))) {
            unlink(storage_path($this->file_path));
        }

        $this->delete();
    }
}