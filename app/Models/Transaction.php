<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use AuditsChanges;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'expense_category_id',
        'number',
        'type',
        'reference',
        'reference_type',
        'date',
        'amount',
        'payment_method',
        'account',
        'description',
        'attachment',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    /** Jurnal GL yang di-auto-post untuk transaksi ini */
    public function journalEntry(): HasOne
    {
        return $this->hasOne(JournalEntry::class, 'reference', 'number')
            ->where('reference_type', 'expense');
    }
}
