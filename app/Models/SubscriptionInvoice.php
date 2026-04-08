<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionInvoice extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'subscription_id', 'invoice_id', 'billing_date',
        'period_start', 'period_end', 'amount', 'discount', 'net_amount',
        'status', 'journal_entry_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'billing_date' => 'date',
            'period_start' => 'date',
            'period_end'   => 'date',
            'amount'       => 'decimal:2',
            'discount'     => 'decimal:2',
            'net_amount'   => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function subscription(): BelongsTo { return $this->belongsTo(CustomerSubscription::class, 'subscription_id'); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
}
