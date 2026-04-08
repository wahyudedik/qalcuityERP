<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabelComplianceCheck extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'label_id',
        'check_name',
        'check_category',
        'requirement',
        'is_compliant',
        'findings',
        'checked_by',
        'checked_at',
        'remarks',
    ];

    protected $casts = [
        'is_compliant' => 'boolean',
        'checked_at' => 'datetime',
    ];

    // Category labels
    public function getCategoryLabelAttribute(): string
    {
        return match ($this->check_category) {
            'mandatory' => 'Mandatory',
            'optional' => 'Optional',
            'regulatory' => 'Regulatory',
            default => ucfirst($this->check_category)
        };
    }

    // Compliance status
    public function getComplianceStatusLabelAttribute(): ?string
    {
        if ($this->is_compliant === null) {
            return 'Not Checked';
        }
        return $this->is_compliant ? 'Compliant' : 'Non-Compliant';
    }

    // Mark as compliant
    public function markCompliant(int $userId, string $findings = '', string $remarks = ''): void
    {
        $this->is_compliant = true;
        $this->checked_by = $userId;
        $this->checked_at = now();
        if ($findings) {
            $this->findings = $findings;
        }
        if ($remarks) {
            $this->remarks = $remarks;
        }
        $this->save();
    }

    // Mark as non-compliant
    public function markNonCompliant(int $userId, string $findings = '', string $remarks = ''): void
    {
        $this->is_compliant = false;
        $this->checked_by = $userId;
        $this->checked_at = now();
        $this->findings = $findings;
        if ($remarks) {
            $this->remarks = $remarks;
        }
        $this->save();
    }

    // Reset check
    public function reset(): void
    {
        $this->is_compliant = null;
        $this->findings = null;
        $this->checked_by = null;
        $this->checked_at = null;
        $this->remarks = null;
        $this->save();
    }

    // Scopes
    public function scopeCompliant($query)
    {
        return $query->where('is_compliant', true);
    }

    public function scopeNonCompliant($query)
    {
        return $query->where('is_compliant', false);
    }

    public function scopePending($query)
    {
        return $query->whereNull('is_compliant');
    }

    public function scopeMandatory($query)
    {
        return $query->where('check_category', 'mandatory');
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function label(): BelongsTo
    {
        return $this->belongsTo(LabelVersion::class, 'label_id');
    }
}
