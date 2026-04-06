<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'registration_id',
        'label_code',
        'version_number',
        'label_type',
        'design_file_path',
        'label_content',
        'barcode',
        'qr_code',
        'effective_date',
        'expiry_date',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'in_review' => 'In Review',
            'approved' => 'Approved',
            'active' => 'Active',
            'archived' => 'Archived',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    // Type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->label_type) {
            'primary' => 'Primary Label',
            'secondary' => 'Secondary Label',
            'insert' => 'Insert Label',
            'outer' => 'Outer Label',
            default => ucfirst($this->label_type)
        };
    }

    // Generate next label code
    public static function getNextLabelCode(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'LBL-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Check if label is currently active
    public function isCurrentlyActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expiry_date && $this->expiry_date->isPast()) {
            return false;
        }

        if ($this->effective_date && $this->effective_date->isFuture()) {
            return false;
        }

        return true;
    }

    // Approve label
    public function approve(int $userId, string $notes = ''): void
    {
        $this->status = 'approved';
        $this->approved_by = $userId;
        $this->approved_at = now();
        if ($notes) {
            $this->approval_notes = $notes;
        }
        $this->save();
    }

    // Activate label
    public function activate(): void
    {
        $this->status = 'active';
        if (!$this->effective_date) {
            $this->effective_date = now();
        }
        $this->save();
    }

    // Archive label
    public function archive(): void
    {
        $this->status = 'archived';
        $this->save();
    }

    // Check compliance status
    public function getComplianceStatusAttribute(): array
    {
        $checks = $this->complianceChecks;

        return [
            'total' => $checks->count(),
            'compliant' => $checks->where('is_compliant', true)->count(),
            'non_compliant' => $checks->where('is_compliant', false)->count(),
            'pending' => $checks->whereNull('is_compliant')->count(),
            'percentage' => $checks->count() > 0
                ? round(($checks->where('is_compliant', true)->count() / $checks->count()) * 100, 2)
                : 0
        ];
    }

    // Check if all compliance checks passed
    public function isFullyCompliant(): bool
    {
        $status = $this->compliance_status;
        return $status['non_compliant'] === 0 && $status['pending'] === 0 && $status['total'] > 0;
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(ProductRegistration::class, 'registration_id');
    }

    public function complianceChecks(): HasMany
    {
        return $this->hasMany(LabelComplianceCheck::class, 'label_id');
    }
}
