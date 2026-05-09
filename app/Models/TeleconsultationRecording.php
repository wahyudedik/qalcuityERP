<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeleconsultationRecording extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'consultation_id',
        'recording_id',
        'file_name',
        'file_size',
        'duration',
        'storage_provider',
        'storage_path',
        'cloud_url',
        'is_encrypted',
        'expires_at',
        'access_count',
        'max_access',
        'status',
        'notes',
    ];

    protected $casts = [
        'consultation_id' => 'integer',
        'file_size' => 'integer',
        'duration' => 'integer',
        'is_encrypted' => 'boolean',
        'expires_at' => 'datetime',
        'access_count' => 'integer',
        'max_access' => 'integer',
    ];

    /**
     * Get the consultation that owns the recording.
     */
    public function consultation()
    {
        return $this->belongsTo(Teleconsultation::class, 'consultation_id');
    }

    /**
     * Check if recording is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available'
            && (! $this->expires_at || $this->expires_at->isFuture())
            && (! $this->max_access || $this->access_count < $this->max_access);
    }

    /**
     * Check if recording is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Increment access count.
     */
    public function incrementAccess(): void
    {
        $this->increment('access_count');
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize(): string
    {
        $bytes = $this->file_size;

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' bytes';
    }

    /**
     * Get formatted duration.
     */
    public function getFormattedDuration(): string
    {
        $seconds = $this->duration;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
