<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodBackup extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'type', 'label', 'period_start', 'period_end',
        'file_path', 'file_size', 'status', 'summary', 'error_message',
        'created_by', 'completed_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end'   => 'date',
        'completed_at' => 'datetime',
        'summary'      => 'array',
        'file_size'    => 'integer',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }

    public function isCompleted(): bool { return $this->status === 'completed'; }
    public function isFailed(): bool    { return $this->status === 'failed'; }

    public function fileSizeHuman(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) return $bytes . ' B';
        if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
        return round($bytes / 1048576, 1) . ' MB';
    }
}
