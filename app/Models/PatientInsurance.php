<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'insurance_provider',
        'insurance_type',
        'policy_number',
        'group_number',
        'member_id',
        'plan_name',
        'plan_class',
        'coverage_limit',
        'deductible',
        'copay_percentage',
        'covered_services',
        'excluded_services',
        'effective_date',
        'expiry_date',
        'is_active',
        'is_primary',
        'employer_name',
        'employer_contact',
        'group_admin_name',
        'group_admin_contact',
        'total_claims',
        'total_claimed_amount',
        'total_approved_amount',
        'last_claim_date',
        'insurance_card_path',
        'policy_document_path',
        'notes',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'expiry_date' => 'date',
        'last_claim_date' => 'date',
        'coverage_limit' => 'decimal:2',
        'deductible' => 'decimal:2',
        'copay_percentage' => 'decimal:2',
        'total_claimed_amount' => 'decimal:2',
        'total_approved_amount' => 'decimal:2',
        'covered_services' => 'array',
        'excluded_services' => 'array',
        'is_active' => 'boolean',
        'is_primary' => 'boolean',
    ];

    /**
     * Check if insurance is currently valid
     */
    public function isValid()
    {
        $now = now();
        return $this->is_active &&
            $this->effective_date <= $now &&
            $this->expiry_date >= $now;
    }

    /**
     * Get insurance status
     */
    public function getInsuranceStatusAttribute()
    {
        $now = now();

        if (!$this->is_active) {
            return 'inactive';
        }

        if ($this->effective_date > $now) {
            return 'not_yet_active';
        }

        if ($this->expiry_date < $now) {
            return 'expired';
        }

        return 'active';
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute()
    {
        return now()->diffInDays($this->expiry_date, false);
    }

    /**
     * Get insurance type label
     */
    public function getInsuranceTypeLabelAttribute()
    {
        $labels = [
            'national' => 'Asuransi Nasional (BPJS)',
            'private' => 'Asuransi Swasta',
            'corporate' => 'Asuransi Perusahaan',
            'self_pay' => 'Bayar Mandiri',
        ];

        return $labels[$this->insurance_type] ?? $this->insurance_type;
    }

    /**
     * Check if service is covered
     */
    public function isServiceCovered($service)
    {
        if (empty($this->covered_services)) {
            return true; // Assume all covered if not specified
        }

        return in_array($service, $this->covered_services);
    }

    /**
     * Check if service is excluded
     */
    public function isServiceExcluded($service)
    {
        if (empty($this->excluded_services)) {
            return false; // Assume none excluded if not specified
        }

        return in_array($service, $this->excluded_services);
    }

    /**
     * Calculate patient copay amount
     */
    public function calculatePatientCopay($totalAmount)
    {
        $deductible = $this->deductible ?? 0;
        $copayPercentage = $this->copay_percentage ?? 0;

        // First apply deductible
        $amountAfterDeductible = max(0, $totalAmount - $deductible);

        // Then apply copay percentage
        $patientCopay = $amountAfterDeductible * ($copayPercentage / 100);

        return round($patientCopay + $deductible, 2);
    }

    /**
     * Calculate insurance coverage amount
     */
    public function calculateInsuranceCoverage($totalAmount)
    {
        $patientCopay = $this->calculatePatientCopay($totalAmount);
        $insuranceCoverage = $totalAmount - $patientCopay;

        // Check against coverage limit
        if ($this->coverage_limit && $insuranceCoverage > $this->coverage_limit) {
            $insuranceCoverage = $this->coverage_limit;
        }

        return round($insuranceCoverage, 2);
    }

    /**
     * Increment claims statistics
     */
    public function recordClaim($claimAmount)
    {
        $this->increment('total_claims');
        $this->increment('total_claimed_amount', $claimAmount);
        $this->update(['last_claim_date' => now()]);
    }

    /**
     * Scope: Active insurances only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Valid (active and within date range)
     */
    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where('effective_date', '<=', now())
            ->where('expiry_date', '>=', now());
    }

    /**
     * Scope: Expiring soon (within specified days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('is_active', true)
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Scope: Primary insurance only
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope: By insurance provider
     */
    public function scopeByProvider($query, $provider)
    {
        return $query->where('insurance_provider', $provider);
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Deactivate insurance
     */
    public function deactivate()
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Set as primary insurance
     */
    public function setAsPrimary()
    {
        // Remove primary status from other insurances for this patient
        PatientInsurance::where('patient_id', $this->patient_id)
            ->where('id', '!=', $this->id)
            ->update(['is_primary' => false]);

        $this->update(['is_primary' => true]);
    }

    /**
     * Get insurance summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'provider' => $this->insurance_provider,
            'policy_number' => $this->policy_number,
            'plan_name' => $this->plan_name,
            'status' => $this->insurance_status,
            'valid_until' => $this->expiry_date->format('Y-m-d'),
            'days_until_expiry' => $this->days_until_expiry,
            'is_primary' => $this->is_primary,
        ];
    }
}
