<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComplianceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_type',
        'title',
        'reporting_period',
        'report_data',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'submitted_at',
        'notes',
    ];

    protected $casts = [
        'report_data' => 'array',
        'reporting_period' => 'date',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
        'submitted_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
