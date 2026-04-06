<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductRegistration extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'formula_id',
        'registration_number',
        'product_name',
        'product_category',
        'registration_type',
        'status',
        'submission_date',
        'approval_date',
        'expiry_date',
        'notified_by',
        'notes',
        'submitted_by',
    ];

    protected $casts = [
        'submission_date' => 'date',
        'approval_date' => 'date',
        'expiry_date' => 'date',
    ];

    // Status labels
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'expired' => 'Expired',
            default => 'Pending'
        };
    }

    // Check if registration is active
    public function isActive(): bool
    {
        return $this->status === 'approved' && (!$this->expiry_date || $this->expiry_date->isFuture());
    }

    // Check if expired
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    // Check if expiring soon (within 90 days)
    public function isExpiringSoon(): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isFuture() && $this->expiry_date->diffInDays(now()) <= 90;
    }

    // Get days until expiry
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expiry_date) {
            return null;
        }
        return $this->expiry_date->diffInDays(now(), false);
    }

    // Submit registration
    public function submit(int $userId): void
    {
        $this->status = 'submitted';
        $this->submission_date = now();
        $this->submitted_by = $userId;
        $this->save();
    }

    // Approve registration
    public function approve(string $notifiedBy = '', string $approvalNumber = ''): void
    {
        $this->status = 'approved';
        $this->approval_date = now();
        if ($notifiedBy) {
            $this->notified_by = $notifiedBy;
        }
        if ($approvalNumber) {
            $this->registration_number = $approvalNumber;
        }
        $this->save();
    }

    // Reject registration
    public function reject(string $notes = ''): void
    {
        $this->status = 'rejected';
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n\n" : '') . 'Rejection: ' . $notes;
        }
        $this->save();
    }

    // Mark as expired
    public function markExpired(): void
    {
        $this->status = 'expired';
        $this->save();
    }

    // Check formula for restricted ingredients
    public function checkIngredientCompliance(): array
    {
        if (!$this->formula_id) {
            return ['compliant' => true, 'issues' => []];
        }

        $formula = $this->formula;
        $restrictions = IngredientRestriction::where('tenant_id', $this->tenant_id)->get();
        $issues = [];

        foreach ($formula->ingredients as $ingredient) {
            $restriction = $restrictions->firstWhere('ingredient_name', $ingredient->inci_name);

            if ($restriction) {
                if ($restriction->restriction_type === 'banned') {
                    $issues[] = [
                        'ingredient' => $ingredient->inci_name,
                        'issue' => 'Banned ingredient',
                        'severity' => 'critical'
                    ];
                } elseif ($restriction->restriction_type === 'restricted' && $ingredient->percentage) {
                    if ($ingredient->percentage > $restriction->max_limit) {
                        $issues[] = [
                            'ingredient' => $ingredient->inci_name,
                            'issue' => "Exceeds maximum limit ({$restriction->max_limit}%)",
                            'severity' => 'high'
                        ];
                    }
                }
            }
        }

        return [
            'compliant' => empty($issues),
            'issues' => $issues
        ];
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExpiringSoon($query, $days = 90)
    {
        return $query->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'approved')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now());
    }

    // Relationships
    public function formula(): BelongsTo
    {
        return $this->belongsTo(CosmeticFormula::class, 'formula_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(RegistrationDocument::class, 'registration_id');
    }

    public function safetyDataSheets(): HasMany
    {
        return $this->hasMany(SafetyDataSheet::class, 'registration_id');
    }
}
