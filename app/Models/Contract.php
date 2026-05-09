<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'contract_number', 'title', 'template_id',
        'customer_id', 'supplier_id', 'party_type', 'category',
        'start_date', 'end_date', 'value', 'currency_code',
        'billing_cycle', 'billing_amount', 'next_billing_date',
        'auto_renew', 'renewal_days_before', 'status',
        'sla_response_hours', 'sla_resolution_hours', 'sla_uptime_pct', 'sla_terms',
        'terms', 'notes', 'signed_by', 'signed_at', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_billing_date' => 'date',
            'signed_at' => 'datetime',
            'value' => 'decimal:2',
            'billing_amount' => 'decimal:2',
            'sla_uptime_pct' => 'decimal:2',
            'auto_renew' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(ContractTemplate::class, 'template_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function signedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }

    public function billings(): HasMany
    {
        return $this->hasMany(ContractBilling::class);
    }

    public function slaLogs(): HasMany
    {
        return $this->hasMany(ContractSlaLog::class);
    }

    public function partyName(): string
    {
        return $this->party_type === 'customer'
            ? ($this->customer->name ?? '-')
            : ($this->supplier->name ?? '-');
    }

    public function isExpiringSoon(int $days = 30): bool
    {
        return $this->status === 'active' && $this->end_date->isBetween(now(), now()->addDays($days));
    }

    public function daysRemaining(): int
    {
        return max(0, (int) now()->diffInDays($this->end_date, false));
    }

    public function slaComplianceRate(): ?float
    {
        $logs = $this->slaLogs()->whereNotNull('sla_met')->get();
        if ($logs->isEmpty()) {
            return null;
        }

        return round($logs->where('sla_met', true)->count() / $logs->count() * 100, 1);
    }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;

        return 'CTR-'.date('Ym').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
