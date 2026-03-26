<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConsignmentSalesReport extends Model
{
    protected $fillable = [
        'tenant_id', 'number', 'partner_id', 'consignment_shipment_id',
        'period_start', 'period_end', 'total_sales', 'commission_pct',
        'commission_amount', 'net_receivable', 'status',
        'journal_entry_id', 'user_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'period_start'     => 'date',
            'period_end'       => 'date',
            'total_sales'      => 'decimal:2',
            'commission_pct'   => 'decimal:2',
            'commission_amount'=> 'decimal:2',
            'net_receivable'   => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function partner(): BelongsTo { return $this->belongsTo(ConsignmentPartner::class, 'partner_id'); }
    public function shipment(): BelongsTo { return $this->belongsTo(ConsignmentShipment::class, 'consignment_shipment_id'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function settlements(): HasMany { return $this->hasMany(ConsignmentSettlement::class, 'sales_report_id'); }

    public function totalSettled(): float
    {
        return (float) $this->settlements()->sum('amount');
    }

    public function remainingBalance(): float
    {
        return (float) $this->net_receivable - $this->totalSettled();
    }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return 'CSR-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
