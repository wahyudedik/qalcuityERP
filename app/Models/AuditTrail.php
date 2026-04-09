<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTrail extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_number',
        'user_id',
        'user_name',
        'user_role',
        'ip_address',
        'user_agent',
        'action',
        'action_category',
        'model_type',
        'model_id',
        'record_identifier',
        'old_values',
        'new_values',
        'changed_fields',
        'access_reason',
        'department',
        'patient_id',
        'is_hipaa_relevant',
        'contains_phi',
        'data_classification',
        'is_suspicious',
        'risk_level',
        'notes',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'is_hipaa_relevant' => 'boolean',
        'contains_phi' => 'boolean',
        'is_suspicious' => 'boolean',
    ];

    /**
     * Scope: By user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: By action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: By category
     */
    public function scopeCategory($query, $category)
    {
        return $query->where('action_category', $category);
    }

    /**
     * Scope: HIPAA relevant
     */
    public function scopeHipaaRelevant($query)
    {
        return $query->where('is_hipaa_relevant', true);
    }

    /**
     * Scope: Contains PHI
     */
    public function scopeContainsPHI($query)
    {
        return $query->where('contains_phi', true);
    }

    /**
     * Scope: Suspicious activity
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope: By risk level
     */
    public function scopeRiskLevel($query, $level)
    {
        return $query->where('risk_level', $level);
    }

    /**
     * Scope: By patient
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope: Date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Search
     */
    public function scopeSearch($query, $searchTerm)
    {
        return $query->where(function ($q) use ($searchTerm) {
            $q->where('audit_number', 'like', "%{$searchTerm}%")
                ->orWhere('user_name', 'like', "%{$searchTerm}%")
                ->orWhere('record_identifier', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Get action label
     */
    public function getActionLabelAttribute()
    {
        return match ($this->action) {
            'view' => 'Viewed',
            'create' => 'Created',
            'update' => 'Updated',
            'delete' => 'Deleted',
            'export' => 'Exported',
            'print' => 'Printed',
            default => ucfirst($this->action),
        };
    }

    /**
     * Get risk level badge color
     */
    public function getRiskBadgeColorAttribute()
    {
        return match ($this->risk_level) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'critical' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if access was during business hours
     */
    public function isBusinessHours()
    {
        $hour = $this->created_at->hour;
        $dayOfWeek = $this->created_at->dayOfWeek;

        // Monday-Friday, 8 AM - 6 PM
        return $dayOfWeek >= 1 && $dayOfWeek <= 5 && $hour >= 8 && $hour < 18;
    }

    /**
     * Get formatted changes
     */
    public function getFormattedChangesAttribute()
    {
        if (!$this->changed_fields) {
            return [];
        }

        $changes = [];
        foreach ($this->changed_fields as $field) {
            $changes[$field] = [
                'old' => $this->old_values[$field] ?? null,
                'new' => $this->new_values[$field] ?? null,
            ];
        }

        return $changes;
    }

    /**
     * Log audit trail
     */
    public static function log(array $data): self
    {
        return self::create([
            'audit_number' => self::generateAuditNumber(),
            'user_id' => $data['user_id'] ?? auth()->id(),
            'user_name' => $data['user_name'] ?? auth()->user()?->name,
            'user_role' => $data['user_role'] ?? auth()->user()?->role,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
            'action' => $data['action'],
            'action_category' => $data['action_category'],
            'model_type' => $data['model_type'],
            'model_id' => $data['model_id'],
            'record_identifier' => $data['record_identifier'] ?? null,
            'old_values' => $data['old_values'] ?? null,
            'new_values' => $data['new_values'] ?? null,
            'changed_fields' => $data['changed_fields'] ?? null,
            'access_reason' => $data['access_reason'] ?? null,
            'department' => $data['department'] ?? null,
            'patient_id' => $data['patient_id'] ?? null,
            'is_hipaa_relevant' => $data['is_hipaa_relevant'] ?? true,
            'contains_phi' => $data['contains_phi'] ?? false,
            'data_classification' => $data['data_classification'] ?? 'confidential',
            'is_suspicious' => $data['is_suspicious'] ?? false,
            'risk_level' => $data['risk_level'] ?? 'low',
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * Generate audit number
     */
    protected static function generateAuditNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'AUDIT-' . $date;

        $last = self::where('audit_number', 'like', $prefix . '%')
            ->orderBy('audit_number', 'desc')
            ->first();

        return $prefix . '-' . str_pad(
            $last ? (int) substr($last->audit_number, -6) + 1 : 1,
            6,
            '0',
            STR_PAD_LEFT
        );
    }
}
