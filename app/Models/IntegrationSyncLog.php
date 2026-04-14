<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationSyncLog extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'integration_id',
        'sync_type',
        'direction',
        'status',
        'records_processed',
        'records_failed',
        'error_message',
        'duration_seconds',
        'details',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Relationships
     */
    public function integration()
    {
        return $this->belongsTo(Integration::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scopes
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePartial($query)
    {
        return $query->where('status', 'partial');
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest()->limit($limit);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    public function scopeByDirection($query, string $direction)
    {
        return $query->where('direction', $direction);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    /**
     * Helpers
     */
    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function hasErrors(): bool
    {
        return $this->records_failed > 0;
    }

    public function isPartial(): bool
    {
        return $this->status === 'partial';
    }

    public function getSuccessRate(): float
    {
        $total = $this->records_processed + $this->records_failed;

        if ($total === 0) {
            return 0;
        }

        return ($this->records_processed / $total) * 100;
    }

    /**
     * Create successful sync log
     */
    public static function logSuccess(
        int $tenantId,
        int $integrationId,
        string $syncType,
        string $direction,
        int $recordsProcessed,
        int $durationSeconds,
        array $details = []
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'integration_id' => $integrationId,
            'sync_type' => $syncType,
            'direction' => $direction,
            'status' => 'success',
            'records_processed' => $recordsProcessed,
            'records_failed' => 0,
            'duration_seconds' => $durationSeconds,
            'details' => $details,
        ]);
    }

    /**
     * Create failed sync log
     */
    public static function logFailure(
        int $tenantId,
        int $integrationId,
        string $syncType,
        string $direction,
        string $errorMessage,
        int $recordsProcessed = 0,
        int $recordsFailed = 0
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'integration_id' => $integrationId,
            'sync_type' => $syncType,
            'direction' => $direction,
            'status' => 'failed',
            'records_processed' => $recordsProcessed,
            'records_failed' => $recordsFailed,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Create partial success log
     */
    public static function logPartial(
        int $tenantId,
        int $integrationId,
        string $syncType,
        string $direction,
        int $recordsProcessed,
        int $recordsFailed,
        string $errorMessage,
        int $durationSeconds,
        array $details = []
    ): self {
        return self::create([
            'tenant_id' => $tenantId,
            'integration_id' => $integrationId,
            'sync_type' => $syncType,
            'direction' => $direction,
            'status' => 'partial',
            'records_processed' => $recordsProcessed,
            'records_failed' => $recordsFailed,
            'error_message' => $errorMessage,
            'duration_seconds' => $durationSeconds,
            'details' => $details,
        ]);
    }
}