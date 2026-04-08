<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsignmentSettlement extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'sales_report_id', 'settlement_date', 'amount',
        'payment_method', 'reference', 'journal_entry_id', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return ['settlement_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function salesReport(): BelongsTo { return $this->belongsTo(ConsignmentSalesReport::class, 'sales_report_id'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
