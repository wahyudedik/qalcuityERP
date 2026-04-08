<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FiscalYear extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'name', 'start_date', 'end_date',
        'status', 'closed_by', 'closed_at', 'locked_by', 'locked_at', 'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'closed_at'  => 'datetime',
        'locked_at'  => 'datetime',
    ];

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function closedBy(): BelongsTo { return $this->belongsTo(User::class, 'closed_by'); }
    public function lockedBy(): BelongsTo { return $this->belongsTo(User::class, 'locked_by'); }
    public function periods(): HasMany { return $this->hasMany(AccountingPeriod::class); }

    public function isOpen(): bool   { return $this->status === 'open'; }
    public function isClosed(): bool { return $this->status === 'closed'; }
    public function isLocked(): bool { return $this->status === 'locked'; }

    /** Apakah tanggal tertentu jatuh dalam tahun fiskal ini */
    public function containsDate(string $date): bool
    {
        return $this->start_date->lte($date) && $this->end_date->gte($date);
    }

    /** Cari fiscal year aktif untuk tanggal tertentu */
    public static function findForDate(int $tenantId, string $date): ?self
    {
        return self::where('tenant_id', $tenantId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
    }

    /** Cek apakah tanggal ini terkunci (period atau fiscal year) */
    public static function isDateLocked(int $tenantId, string $date): bool
    {
        // Cek fiscal year lock
        $fy = self::findForDate($tenantId, $date);
        if ($fy && $fy->isLocked()) return true;

        // Cek accounting period lock
        $period = AccountingPeriod::where('tenant_id', $tenantId)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->whereIn('status', ['locked', 'closed'])
            ->exists();

        return $period;
    }
}
