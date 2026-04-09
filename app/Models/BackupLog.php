<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'backup_type',
        'status',
        'file_path',
        'size_bytes',
        'error_message',
        'initiated_by',
        'notes',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }
}
