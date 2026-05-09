<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaCertificate extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'coa_number',
        'issue_date',
        'expiry_date',
        'test_results',
        'conclusion',
        'issued_by',
        'approved_by',
        'status',
    ];

    protected $casts = [
        'test_results' => 'array',
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'issued' => 'Issued',
            'approved' => 'Approved',
            'revoked' => 'Revoked',
            default => 'Draft'
        };
    }

    // Check if COA is valid
    public function isValid(): bool
    {
        return $this->status === 'approved' && (! $this->expiry_date || $this->expiry_date->isFuture());
    }

    // Check if expired
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Issue COA
    public function issue(int $userId): void
    {
        $this->status = 'issued';
        $this->issued_by = $userId;
        $this->save();
    }

    // Approve COA
    public function approve(int $userId): void
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->save();
    }

    // Revoke COA
    public function revoke(string $reason = ''): void
    {
        $this->status = 'revoked';
        $this->conclusion = ($this->conclusion ? $this->conclusion."\n\n" : '').'Revoked: '.$reason;
        $this->save();
    }

    // Add test result to COA
    public function addTestResult(QCTestResult $testResult): void
    {
        $results = $this->test_results ?? [];
        $results[] = $testResult->toArray();
        $this->test_results = $results;
        $this->save();
    }

    // Get all pass rate
    public function getPassRateAttribute(): float
    {
        $results = $this->test_results ?? [];
        if (empty($results)) {
            return 0;
        }

        $passed = collect($results)->where('result', 'pass')->count();

        return round(($passed / count($results)) * 100, 2);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'approved')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(CosmeticBatchRecord::class, 'batch_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Generate next COA number
    public static function getNextCoaNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;

        return 'COA-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Generate COA from batch
    public static function generateFromBatch(int $batchId, int $userId): self
    {
        $batch = CosmeticBatchRecord::findOrFail($batchId);
        $tests = QCTestResult::where('batch_id', $batchId)
            ->where('status', 'approved')
            ->get();

        $coa = self::create([
            'tenant_id' => $batch->tenant_id,
            'batch_id' => $batchId,
            'coa_number' => self::getNextCoaNumber(),
            'issue_date' => now(),
            'test_results' => $tests->toArray(),
            'conclusion' => 'All tests completed and approved.',
            'issued_by' => $userId,
            'status' => 'issued',
        ]);

        return $coa;
    }
}
