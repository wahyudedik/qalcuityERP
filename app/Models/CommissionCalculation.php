<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionCalculation extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'user_id', 'commission_rule_id', 'period',
        'total_sales', 'total_orders', 'commission_amount', 'bonus_amount',
        'total_payout', 'status', 'approved_by', 'journal_entry_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_sales'       => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'bonus_amount'      => 'decimal:2',
            'total_payout'      => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function commissionRule(): BelongsTo { return $this->belongsTo(CommissionRule::class); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
}
