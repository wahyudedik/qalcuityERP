<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkShift extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id', 'name', 'color', 'start_time', 'end_time',
        'break_minutes', 'crosses_midnight', 'description', 'is_active',
    ];

    protected $casts = ['crosses_midnight' => 'boolean', 'is_active' => 'boolean'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class);
    }

    /** Durasi kerja bersih dalam menit (dikurangi istirahat) */
    public function workMinutes(): int
    {
        [$sh, $sm] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);
        $start = (int) $sh * 60 + (int) $sm;
        $end = (int) $eh * 60 + (int) $em;
        if ($this->crosses_midnight) {
            $end += 1440;
        }

        return max(0, $end - $start - $this->break_minutes);
    }

    /** Label jam e.g. "08:00 – 16:00" */
    public function timeLabel(): string
    {
        return substr($this->start_time, 0, 5).' – '.substr($this->end_time, 0, 5);
    }

    /** Hitung overtime dalam menit dari check_in/check_out aktual */
    public function calcOvertime(string $checkIn, string $checkOut): int
    {
        [$sh, $sm] = explode(':', $this->start_time);
        [$eh, $em] = explode(':', $this->end_time);
        [$oh, $om] = explode(':', substr($checkOut, 0, 5));
        $scheduledEnd = (int) $eh * 60 + (int) $em;
        $actualEnd = (int) $oh * 60 + (int) $om;
        if ($this->crosses_midnight && $actualEnd < $scheduledEnd) {
            $actualEnd += 1440;
        }

        return $actualEnd - $scheduledEnd; // positif = lembur, negatif = pulang cepat
    }
}
