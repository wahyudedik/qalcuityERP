<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalStaffSchedule extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'doctor_id',
        'schedule_date',
        'start_time',
        'end_time',
        'slot_duration',
        'max_appointments',
        'booked_appointments',
        'location',
        'location_details',
        'status',
        'is_available',
        'allow_overbooking',
        'schedule_type',
        'block_reason',
        'block_notes',
        'no_show_count',
        'utilization_rate',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_available' => 'boolean',
        'allow_overbooking' => 'boolean',
        'utilization_rate' => 'decimal:2',
    ];

    /**
     * Get schedule duration in minutes
     */
    public function getDurationInMinutesAttribute()
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $start->diffInMinutes($end);
    }

    /**
     * Get available slots
     */
    public function getAvailableSlotsAttribute()
    {
        if ($this->max_appointments == 0) {
            return -1; // Unlimited
        }

        return max(0, $this->max_appointments - $this->booked_appointments);
    }

    /**
     * Check if schedule is full
     */
    public function isFull()
    {
        if ($this->max_appointments == 0) {
            return false; // Unlimited
        }

        return $this->booked_appointments >= $this->max_appointments;
    }

    /**
     * Check if can book appointment
     */
    public function canBook()
    {
        return $this->is_available &&
            $this->status === 'available' &&
            (! $this->isFull() || $this->allow_overbooking);
    }

    /**
     * Book a slot
     */
    public function bookSlot()
    {
        $this->increment('booked_appointments');

        // Update utilization rate
        if ($this->max_appointments > 0) {
            $utilization = ($this->booked_appointments / $this->max_appointments) * 100;
            $this->update(['utilization_rate' => round($utilization, 2)]);
        }

        // Update status if full
        if ($this->isFull() && ! $this->allow_overbooking) {
            $this->update(['status' => 'booked']);
        }
    }

    /**
     * Cancel a booking
     */
    public function cancelBooking()
    {
        $this->decrement('booked_appointments');

        // Recalculate utilization rate
        if ($this->max_appointments > 0) {
            $utilization = ($this->booked_appointments / $this->max_appointments) * 100;
            $this->update(['utilization_rate' => round($utilization, 2)]);
        }

        // Update status back to available
        if ($this->booked_appointments < $this->max_appointments) {
            $this->update(['status' => 'available']);
        }
    }

    /**
     * Block schedule
     */
    public function block($reason = null, $notes = null)
    {
        $this->update([
            'status' => 'blocked',
            'is_available' => false,
            'block_reason' => $reason,
            'block_notes' => $notes,
        ]);
    }

    /**
     * Unblock schedule
     */
    public function unblock()
    {
        $this->update([
            'status' => 'available',
            'is_available' => true,
            'block_reason' => null,
            'block_notes' => null,
        ]);
    }

    /**
     * Get available time slots for booking
     */
    public function getAvailableTimeSlotsAttribute()
    {
        $slots = [];
        $currentTime = Carbon::parse($this->start_time);
        $endTime = Carbon::parse($this->end_time);

        while ($currentTime < $endTime) {
            $slots[] = $currentTime->format('H:i');
            $currentTime->addMinutes($this->slot_duration);
        }

        return $slots;
    }

    /**
     * Scope: Schedules for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('schedule_date', $date);
    }

    /**
     * Scope: Available schedules only
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('status', 'available');
    }

    /**
     * Scope: Future schedules
     */
    public function scopeFuture($query)
    {
        return $query->whereDate('schedule_date', '>=', today());
    }

    /**
     * Scope: Past schedules
     */
    public function scopePast($query)
    {
        return $query->whereDate('schedule_date', '<', today());
    }

    /**
     * Scope: By schedule type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('schedule_type', $type);
    }

    /**
     * Scope: Has available slots
     */
    public function scopeHasAvailableSlots($query)
    {
        return $query->where(function ($q) {
            $q->where('max_appointments', 0) // Unlimited
                ->orWhereColumn('booked_appointments', '<', 'max_appointments');
        });
    }

    /**
     * Relation: Doctor
     */
    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Relation: Appointments
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'schedule_id');
    }

    /**
     * Get schedule summary
     */
    public function getSummaryAttribute()
    {
        return [
            'id' => $this->id,
            'date' => $this->schedule_date->format('Y-m-d'),
            'time' => $this->start_time.' - '.$this->end_time,
            'location' => $this->location,
            'type' => $this->schedule_type,
            'status' => $this->status,
            'available_slots' => $this->available_slots,
            'booked' => $this->booked_appointments,
            'max' => $this->max_appointments,
        ];
    }
}
