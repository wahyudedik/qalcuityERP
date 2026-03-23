<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\AccountingPeriod;

class JournalEntry extends Model
{
    protected $fillable = [
        'tenant_id', 'period_id', 'user_id', 'number', 'date', 'description',
        'reference', 'reference_type', 'reference_id',
        'currency_code', 'currency_rate', 'status',
        'reversed_by', 'posted_by', 'posted_at',
        'is_recurring', 'recurring_journal_id',
    ];

    protected $casts = [
        'date'          => 'date',
        'posted_at'     => 'datetime',
        'currency_rate' => 'float',
        'is_recurring'  => 'boolean',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function period(): BelongsTo { return $this->belongsTo(AccountingPeriod::class, 'period_id'); }
    public function lines(): HasMany { return $this->hasMany(JournalEntryLine::class); }
    public function postedBy(): BelongsTo { return $this->belongsTo(User::class, 'posted_by'); }

    /** Total debit (harus = total credit untuk balanced entry) */
    public function totalDebit(): float { return (float) $this->lines()->sum('debit'); }
    public function totalCredit(): float { return (float) $this->lines()->sum('credit'); }
    public function isBalanced(): bool { return abs($this->totalDebit() - $this->totalCredit()) < 0.01; }

    /** Post jurnal — ubah status ke posted */
    public function post(int $userId): void
    {
        if (! $this->isBalanced()) {
            throw new \RuntimeException('Jurnal tidak balance: debit ≠ credit.');
        }
        $this->update([
            'status'    => 'posted',
            'posted_by' => $userId,
            'posted_at' => now(),
        ]);
    }

    /** Buat jurnal pembalik (reversal) */
    public function reverse(int $userId, string $date): self
    {
        $reversal = self::create([
            'tenant_id'    => $this->tenant_id,
            'period_id'    => AccountingPeriod::findForDate($this->tenant_id, $date)?->id,
            'user_id'      => $userId,
            'number'       => self::generateNumber($this->tenant_id, 'JRV'),
            'date'         => $date,
            'description'  => 'Pembalik: ' . $this->description,
            'reference'    => $this->number,
            'reference_type' => 'reversal',
            'reference_id'   => $this->id,
            'currency_code'  => $this->currency_code,
            'currency_rate'  => $this->currency_rate,
            'status'         => 'draft',
        ]);

        foreach ($this->lines as $line) {
            $reversal->lines()->create([
                'account_id'  => $line->account_id,
                'debit'       => $line->credit,  // swap
                'credit'      => $line->debit,   // swap
                'description' => $line->description,
            ]);
        }

        $this->update(['reversed_by' => $reversal->id, 'status' => 'reversed']);

        return $reversal;
    }

    /** Generate nomor jurnal otomatis via DocumentNumberService */
    public static function generateNumber(int $tenantId, string $prefix = 'JE'): string
    {
        $docType = match ($prefix) {
            'JRV'   => 'jrv',
            'AUTO'  => 'je',
            default => 'je',
        };

        return app(\App\Services\DocumentNumberService::class)->generate($tenantId, $docType, $prefix);
    }
}
