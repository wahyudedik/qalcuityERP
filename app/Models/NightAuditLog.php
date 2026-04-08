<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NightAuditLog extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'audit_batch_id',
        'operation',
        'description',
        'status',
        'details',
        'performed_by',
        'performed_at',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'performed_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function auditBatch(): BelongsTo
    {
        return $this->belongsTo(NightAuditBatch::class, 'audit_batch_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Log a successful operation
     */
    public static function logSuccess(string $operation, string $description, int $userId, ?int $batchId = null, array $details = []): self
    {
        return static::create([
            'tenant_id' => auth()->user()->current_tenant_id ?? 1,
            'audit_batch_id' => $batchId,
            'operation' => $operation,
            'description' => $description,
            'status' => 'success',
            'details' => $details,
            'performed_by' => $userId,
            'performed_at' => now(),
        ]);
    }

    /**
     * Log a failed operation
     */
    public static function logFailure(string $operation, string $description, int $userId, ?int $batchId = null, array $details = []): self
    {
        return static::create([
            'tenant_id' => auth()->user()->current_tenant_id ?? 1,
            'audit_batch_id' => $batchId,
            'operation' => $operation,
            'description' => $description,
            'status' => 'failed',
            'details' => $details,
            'performed_by' => $userId,
            'performed_at' => now(),
        ]);
    }

    /**
     * Log a warning
     */
    public static function logWarning(string $operation, string $description, int $userId, ?int $batchId = null, array $details = []): self
    {
        return static::create([
            'tenant_id' => auth()->user()->current_tenant_id ?? 1,
            'audit_batch_id' => $batchId,
            'operation' => $operation,
            'description' => $description,
            'status' => 'warning',
            'details' => $details,
            'performed_by' => $userId,
            'performed_at' => now(),
        ]);
    }
}
