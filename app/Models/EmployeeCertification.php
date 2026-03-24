<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeCertification extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'name', 'issuer', 'certificate_number',
        'issued_date', 'expiry_date', 'status', 'file_path', 'notes',
    ];

    protected function casts(): array
    {
        return ['issued_date' => 'date', 'expiry_date' => 'date'];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }

    /** Hari tersisa hingga expired (null jika tidak ada expiry) */
    public function daysUntilExpiry(): ?int
    {
        if (!$this->expiry_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    /** Warna badge berdasarkan sisa hari */
    public function expiryBadgeClass(): string
    {
        $days = $this->daysUntilExpiry();
        if ($days === null) return 'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400';
        if ($days < 0)   return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400';
        if ($days <= 30) return 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400';
        if ($days <= 90) return 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400';
        return 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400';
    }

    /** Auto-update status based on expiry */
    public function syncStatus(): void
    {
        if ($this->expiry_date && $this->expiry_date->isPast() && $this->status === 'active') {
            $this->update(['status' => 'expired']);
        }
    }
}
