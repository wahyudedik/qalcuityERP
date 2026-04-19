<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WardRound extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'admission_id',
        'doctor_id',
        'round_date',
        'vital_signs',
        'assessment',
        'plan',
        'notes',
    ];

    protected $casts = [
        'round_date' => 'datetime',
        'vital_signs' => 'array',
    ];

    public function admission(): BelongsTo
    {
        return $this->belongsTo(Admission::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
