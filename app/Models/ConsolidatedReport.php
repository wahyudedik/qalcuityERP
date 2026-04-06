<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsolidatedReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_group_id',
        'report_type',
        'period_start',
        'period_end',
        'currency',
        'report_data',
        'elimination_entries',
        'subsidiary_contributions',
        'status',
        'prepared_by_user_id',
        'approved_by_user_id',
        'approved_at',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'report_data' => 'array',
        'elimination_entries' => 'array',
        'subsidiary_contributions' => 'array',
        'approved_at' => 'datetime',
    ];

    public function companyGroup()
    {
        return $this->belongsTo(CompanyGroup::class);
    }
    public function preparedBy()
    {
        return $this->belongsTo(User::class, 'prepared_by_user_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
    public function eliminations()
    {
        return $this->hasMany(EliminationEntry::class);
    }
}
