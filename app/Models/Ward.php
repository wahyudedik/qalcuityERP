<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ward extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ward_code',
        'ward_name',
        'ward_type',
        'floor',
        'capacity',
        'occupied_beds',
        'facilities',
        'description',
        'head_nurse_id',
        'supervisor_doctor_id',
        'status',
        'is_restricted',
        'has_nurse_station',
        'phone_extension',
        'email',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'occupied_beds' => 'integer',
        'floor' => 'integer',
        'facilities' => 'array',
        'is_restricted' => 'boolean',
        'has_nurse_station' => 'boolean',
    ];

    /**
     * Get occupancy rate
     */
    public function getOccupancyRateAttribute()
    {
        if ($this->capacity == 0) {
            return 0;
        }

        return round(($this->occupied_beds / $this->capacity) * 100, 2);
    }

    /**
     * Get available beds count
     */
    public function getAvailableBedsAttribute()
    {
        return $this->capacity - $this->occupied_beds;
    }

    /**
     * Check if ward is full
     */
    public function isFull()
    {
        return $this->occupied_beds >= $this->capacity;
    }

    /**
     * Scope: Active wards only
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: By ward type
     */
    public function scopeType($query, $type)
    {
        return $query->where('ward_type', $type);
    }

    /**
     * Scope: Has available beds
     */
    public function scopeHasAvailableBeds($query)
    {
        return $query->whereColumn('occupied_beds', '<', 'capacity');
    }

    /**
     * Relation: Beds
     */
    public function beds()
    {
        return $this->hasMany(Bed::class);
    }

    /**
     * Relation: Available beds
     */
    public function availableBeds()
    {
        return $this->hasMany(Bed::class)->where('status', 'available');
    }

    /**
     * Relation: Head nurse
     */
    public function headNurse()
    {
        return $this->belongsTo(User::class, 'head_nurse_id');
    }

    /**
     * Relation: Supervisor doctor
     */
    public function supervisorDoctor()
    {
        return $this->belongsTo(Doctor::class, 'supervisor_doctor_id');
    }

    /**
     * Relation: Active admissions
     */
    public function activeAdmissions()
    {
        return $this->hasMany(Admission::class)->where('status', 'active');
    }

    /**
     * Get ward statistics
     */
    public function getStatisticsAttribute()
    {
        return [
            'total_beds' => $this->capacity,
            'occupied_beds' => $this->occupied_beds,
            'available_beds' => $this->available_beds,
            'occupancy_rate' => $this->occupancy_rate,
            'is_full' => $this->isFull(),
        ];
    }
}
