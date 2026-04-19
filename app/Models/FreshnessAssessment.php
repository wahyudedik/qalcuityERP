<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FreshnessAssessment extends Model
{
use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'catch_log_id',
        'overall_score',
        'eye_clarity',
        'gill_color',
        'skin_firmness',
        'odor_score',
        'assessed_by_type',
        'assessor_id',
        'assessed_at',
        'notes',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'eye_clarity' => 'decimal:2',
        'gill_color' => 'decimal:2',
        'skin_firmness' => 'decimal:2',
        'odor_score' => 'decimal:2',
        'assessed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function catchLog()
    {
        return $this->belongsTo(CatchLog::class);
    }

    public function assessor()
    {
        return $this->belongsTo(Employee::class, 'assessor_id');
    }
}