<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_code',
        'queue_name',
        'queue_type',
        'location',
        'prefix_number',
        'prefix_format',
        'max_queue_per_day',
        'service_time',
        'start_time',
        'end_time',
        'working_days',
        'show_on_display',
        'play_sound',
        'sound_file',
        'call_repeat',
        'is_active',
    ];

    protected $casts = [
        'prefix_number' => 'integer',
        'max_queue_per_day' => 'integer',
        'service_time' => 'integer',
        'working_days' => 'array',
        'show_on_display' => 'boolean',
        'play_sound' => 'boolean',
        'call_repeat' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get default working days
     */
    public function getDefaultWorkingDaysAttribute()
    {
        return $this->working_days ?? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    }

    /**
     * Check if queue is open now
     */
    public function isOpenNow()
    {
        $now = now();
        $currentDay = strtolower($now->format('l'));
        $currentTime = $now->format('H:i:s');

        if (! in_array($currentDay, $this->default_working_days)) {
            return false;
        }

        return $currentTime >= $this->start_time && $currentTime <= $this->end_time;
    }

    /**
     * Get next queue number
     */
    public function getNextQueueNumber()
    {
        $today = now()->format('Ymd');
        $prefix = $this->queue_code.'-'.$today.'-';

        $lastVisit = OutpatientVisit::where('queue_setting_id', $this->id)
            ->whereDate('visit_date', today())
            ->orderBy('visit_number', 'desc')
            ->first();

        if ($lastVisit) {
            $lastNumber = (int) substr($lastVisit->visit_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix.$newNumber;
    }

    /**
     * Scope: Active queues only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: By queue type
     */
    public function scopeType($query, $type)
    {
        return $query->where('queue_type', $type);
    }

    /**
     * Relation: Today's visits
     */
    public function todayVisits()
    {
        return $this->hasMany(OutpatientVisit::class)->whereDate('visit_date', today());
    }

    /**
     * Relation: Waiting queue
     */
    public function waitingQueue()
    {
        return $this->hasMany(OutpatientVisit::class)
            ->whereIn('status', ['registered', 'waiting']);
    }
}
