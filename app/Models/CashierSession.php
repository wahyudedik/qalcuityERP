<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashierSession extends Model
{
    use AuditsChanges;
    use BelongsToTenant;

    // Status constants — harus sesuai dengan ENUM di migration
    const STATUS_OPEN = 'open';

    const STATUS_CLOSED = 'closed';

    const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
    ];

    protected $fillable = [
        'tenant_id',
        'user_id',
        'warehouse_id',
        'register_name',
        'status',
        'opening_balance',
        'opened_at',
        'closing_balance',
        'expected_balance',
        'balance_difference',
        'closed_at',
        'closed_by',
        'total_transactions',
        'total_sales',
        'total_cash',
        'total_card',
        'total_qris',
        'total_transfer',
        'total_discount',
        'total_tax',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'opening_balance' => 'decimal:2',
            'closing_balance' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'balance_difference' => 'decimal:2',
            'total_sales' => 'decimal:2',
            'total_cash' => 'decimal:2',
            'total_card' => 'decimal:2',
            'total_qris' => 'decimal:2',
            'total_transfer' => 'decimal:2',
            'total_discount' => 'decimal:2',
            'total_tax' => 'decimal:2',
            'total_transactions' => 'integer',
        ];
    }

    // ── Relasi ──────────────────────────────────────────────────────────────

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /** Semua transaksi POS yang terjadi dalam sesi ini */
    public function transactions(): HasMany
    {
        return $this->hasMany(SalesOrder::class, 'cashier_session_id');
    }

    // ── Helper methods ───────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    /**
     * Hitung rekap transaksi dari database secara real-time.
     * Digunakan saat menutup sesi untuk mendapatkan angka akurat.
     */
    public function calculateRecap(): array
    {
        $transactions = $this->transactions()
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_transactions,
                COALESCE(SUM(total), 0) as total_sales,
                COALESCE(SUM(CASE WHEN payment_method = "cash" THEN total ELSE 0 END), 0) as total_cash,
                COALESCE(SUM(CASE WHEN payment_method IN ("card", "credit") THEN total ELSE 0 END), 0) as total_card,
                COALESCE(SUM(CASE WHEN payment_method = "qris" THEN total ELSE 0 END), 0) as total_qris,
                COALESCE(SUM(CASE WHEN payment_method IN ("transfer", "bank_transfer") THEN total ELSE 0 END), 0) as total_transfer,
                COALESCE(SUM(discount), 0) as total_discount,
                COALESCE(SUM(tax), 0) as total_tax
            ')
            ->first();

        $totalCash = (float) ($transactions->total_cash ?? 0);
        $expectedBalance = (float) $this->opening_balance + $totalCash;

        return [
            'total_transactions' => (int) ($transactions->total_transactions ?? 0),
            'total_sales' => (float) ($transactions->total_sales ?? 0),
            'total_cash' => $totalCash,
            'total_card' => (float) ($transactions->total_card ?? 0),
            'total_qris' => (float) ($transactions->total_qris ?? 0),
            'total_transfer' => (float) ($transactions->total_transfer ?? 0),
            'total_discount' => (float) ($transactions->total_discount ?? 0),
            'total_tax' => (float) ($transactions->total_tax ?? 0),
            'expected_balance' => $expectedBalance,
        ];
    }

    /**
     * Scope: hanya sesi yang sedang terbuka
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    /**
     * Scope: hanya sesi yang sudah ditutup
     */
    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }
}
