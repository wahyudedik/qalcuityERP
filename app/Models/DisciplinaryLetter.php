<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisciplinaryLetter extends Model
{
    protected $fillable = [
        'tenant_id', 'employee_id', 'level', 'letter_number', 'issued_date',
        'valid_until', 'violation_type', 'violation_description', 'corrective_action',
        'consequences', 'status', 'acknowledged_at', 'employee_response',
        'issued_by', 'witnessed_by', 'source', 'ai_context',
    ];

    protected function casts(): array
    {
        return [
            'issued_date'     => 'date',
            'valid_until'     => 'date',
            'acknowledged_at' => 'datetime',
            'ai_context'      => 'array',
        ];
    }

    public function employee(): BelongsTo  { return $this->belongsTo(Employee::class); }
    public function issuer(): BelongsTo    { return $this->belongsTo(User::class, 'issued_by'); }
    public function witness(): BelongsTo   { return $this->belongsTo(User::class, 'witnessed_by'); }

    /** Label tampilan level */
    public function levelLabel(): string
    {
        return match($this->level) {
            'sp1'         => 'SP I',
            'sp2'         => 'SP II',
            'sp3'         => 'SP III',
            'memo'        => 'Memo Peringatan',
            'termination' => 'PHK',
            default       => strtoupper($this->level),
        };
    }

    /** Warna badge level */
    public function levelColor(): string
    {
        return match($this->level) {
            'sp1'         => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400',
            'sp2'         => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
            'sp3'         => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
            'memo'        => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
            'termination' => 'bg-gray-900 text-white dark:bg-white/20 dark:text-white',
            default       => 'bg-gray-100 text-gray-600',
        };
    }

    /** Apakah SP masih aktif/berlaku */
    public function isActive(): bool
    {
        if ($this->status === 'expired') return false;
        if ($this->valid_until && $this->valid_until->isPast()) return false;
        return $this->status === 'issued' || $this->status === 'acknowledged';
    }

    /** Auto-generate nomor surat */
    public static function generateNumber(int $tenantId, string $level): string
    {
        $count = static::where('tenant_id', $tenantId)
            ->whereYear('issued_date', now()->year)
            ->count() + 1;
        $roman = ['sp1'=>'I','sp2'=>'II','sp3'=>'III','memo'=>'M','termination'=>'PHK'][$level] ?? 'X';
        return sprintf('SP-%s/%s/%04d', $roman, now()->format('Y'), $count);
    }
}
