<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HelpdeskTicket extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'ticket_number', 'subject', 'description',
        'customer_id', 'contact_name', 'contact_email', 'contact_phone',
        'priority', 'category', 'status', 'assigned_to', 'created_by',
        'sla_response_due', 'sla_resolve_due', 'first_responded_at', 'resolved_at',
        'sla_response_met', 'sla_resolve_met',
        'reference_type', 'reference_id', 'contract_id',
        'satisfaction_rating', 'tags',
    ];

    protected function casts(): array
    {
        return [
            'sla_response_due' => 'datetime',
            'sla_resolve_due' => 'datetime',
            'first_responded_at' => 'datetime',
            'resolved_at' => 'datetime',
            'sla_response_met' => 'boolean',
            'sla_resolve_met' => 'boolean',
            'satisfaction_rating' => 'decimal:1',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(HelpdeskReply::class, 'ticket_id');
    }

    public function isOverdue(): bool
    {
        if ($this->status === 'closed' || $this->status === 'resolved') {
            return false;
        }

        return $this->sla_resolve_due && $this->sla_resolve_due->isPast();
    }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;

        return 'TKT-'.date('Ym').'-'.str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
