<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QCTestResult extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'batch_id',
        'template_id',
        'test_code',
        'test_name',
        'test_category',
        'sample_id',
        'parameters',
        'result',
        'observations',
        'recommendations',
        'tested_by',
        'approved_by',
        'test_date',
        'approved_at',
        'status',
    ];

    protected $casts = [
        'parameters' => 'array',
        'test_date' => 'datetime',
        'approved_at' => 'datetime',
    ];

    // Result labels
    public function getResultLabelAttribute(): string
    {
        return match ($this->result) {
            'pass' => 'Passed',
            'fail' => 'Failed',
            'inconclusive' => 'Inconclusive',
            default => 'Pending'
        };
    }

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'completed' => 'Completed',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Draft'
        };
    }

    // Category labels
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->test_category) {
            'microbial' => 'Microbial',
            'heavy_metal' => 'Heavy Metal',
            'preservative' => 'Preservative',
            'patch_test' => 'Patch Test',
            'physical' => 'Physical',
            'chemical' => 'Chemical',
            default => ucfirst(str_replace('_', ' ', $this->test_category))
        };
    }

    // Check if test passed
    public function isPassed(): bool
    {
        return $this->result === 'pass';
    }

    // Check if test failed
    public function isFailed(): bool
    {
        return $this->result === 'fail';
    }

    // Check if approved
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    // Complete test
    public function complete(string $result, array $parameters = [], string $observations = ''): void
    {
        $this->result = $result;
        $this->parameters = $parameters ?: $this->parameters;
        $this->observations = $observations ?: $this->observations;
        $this->status = 'completed';
        $this->tested_by = $this->tested_by ?? auth()->id();
        $this->save();
    }

    // Approve test
    public function approve(int $userId): void
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        $this->save();
    }

    // Reject test
    public function reject(int $userId, string $reason = ''): void
    {
        $this->status = 'rejected';
        $this->approved_by = $userId;
        $this->approved_at = now();
        if ($reason) {
            $this->observations = ($this->observations ? $this->observations . "\n\n" : '') . 'Rejection Reason: ' . $reason;
        }
        $this->save();
    }

    // Generate COA from this test
    public function generateCOA(): ?CoaCertificate
    {
        if (!$this->batch_id || !$this->isApproved()) {
            return null;
        }

        return CoaCertificate::firstOrCreate(
            ['batch_id' => $this->batch_id, 'status' => 'draft'],
            [
                'tenant_id' => $this->tenant_id,
                'coa_number' => CoaCertificate::getNextCoaNumber(),
                'issue_date' => now(),
                'test_results' => [$this->toArray()],
            ]
        );
    }

    // Create OOS if failed
    public function createOOS(string $description, string $severity = 'medium'): OosInvestigation
    {
        return OosInvestigation::create([
            'tenant_id' => $this->tenant_id,
            'test_result_id' => $this->id,
            'batch_id' => $this->batch_id,
            'oos_number' => OosInvestigation::getNextOosNumber(),
            'oos_type' => 'laboratory',
            'description' => $description,
            'severity' => $severity,
            'discovery_date' => now(),
        ]);
    }

    // Scopes
    public function scopePassed($query)
    {
        return $query->where('result', 'pass');
    }

    public function scopeFailed($query)
    {
        return $query->where('result', 'fail');
    }

    public function scopeCategory($query, $category)
    {
        return $query->where('test_category', $category);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'draft');
    }

    // Relationships
    public function batch(): BelongsTo
    {
        return $this->belongsTo(CosmeticBatchRecord::class, 'batch_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(QCTestTemplate::class, 'template_id');
    }

    public function tester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function oosInvestigations(): HasMany
    {
        return $this->hasMany(OosInvestigation::class, 'test_result_id');
    }

    // Generate next test code
    public static function getNextTestCode(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'QC-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
