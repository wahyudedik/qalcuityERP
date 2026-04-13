<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MinistryReport extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'report_type',
        'reporting_period',
        'report_data',
        'status',
        'submitted_at',
        'submitted_by',
        'approved_at',
        'approved_by',
        'notes',
        'external_reference',
    ];

    protected $casts = [
        'report_data' => 'array',
        'reporting_period' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
