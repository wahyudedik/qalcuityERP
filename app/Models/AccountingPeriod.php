<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountingPeriod extends Model
{
    use BelongsToTenant;

    const STATUS_OPEN   = 'open';
    const STATUS_CLOSED = 'closed';
    const STATUS_LOCKED = 'locked';

    const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_CLOSED,
        self::STATUS_LOCKED,
    ];

    protected $fillable = [
        'tenant_id', 'name', 'start_date', 'end_date',
        'status', 'closed_by', 'closed_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'closed_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function journalEntries(): HasMany { return $this->hasMany(JournalEntry::class, 'period_id'); }

    public function isOpen(): bool { return $this->status === 'open'; }
    public function isLocked(): bool { return $this->status === 'locked'; }

    /** Cari period yang aktif untuk tanggal tertentu */
    public static function findForDate(int $tenantId, string $date): ?self
    {
        return self::where('tenant_id', $tenantId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->where('status', 'open')
            ->first();
    }
}
