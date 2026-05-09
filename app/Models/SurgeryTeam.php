<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurgeryTeam extends Model
{
    use HasFactory;

    protected $fillable = [
        'surgery_id',
        'staff_id',
        'staff_type',
        'role',
        'check_in_time',
        'check_out_time',
        'notes',
        'performance_notes',
        'performance_rating',
    ];

    /**
     * Relation: Surgery Schedule
     */
    public function surgery()
    {
        return $this->belongsTo(SurgerySchedule::class, 'surgery_id');
    }
}
