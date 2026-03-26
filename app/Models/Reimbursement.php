<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reimbursement extends Model
{
    protected $fillable = [
        'tenant_id', 'number', 'employee_id', 'requested_by',
        'category', 'description', 'expense_date', 'amount',
        'receipt_image', 'status', 'approved_by', 'approved_at',
        'reject_reason', 'paid_by', 'paid_at', 'payment_method',
        'payment_reference', 'journal_entry_id', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
            'amount'       => 'decimal:2',
            'approved_at'  => 'datetime',
            'paid_at'      => 'datetime',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function payer(): BelongsTo { return $this->belongsTo(User::class, 'paid_by'); }
    public function journalEntry(): BelongsTo { return $this->belongsTo(JournalEntry::class); }

    public static function generateNumber(int $tenantId): string
    {
        $count = self::where('tenant_id', $tenantId)->count() + 1;
        return 'RMB-' . date('Ym') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    public function categoryLabel(): string
    {
        return match($this->category) {
            'transport' => 'Transportasi',
            'meal'      => 'Makan & Minum',
            'medical'   => 'Kesehatan',
            'office'    => 'Perlengkapan Kantor',
            'travel'    => 'Perjalanan Dinas',
            'training'  => 'Pelatihan',
            'other'     => 'Lainnya',
            default     => ucfirst($this->category),
        };
    }
}
