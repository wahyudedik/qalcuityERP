<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintingPlate extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'print_job_id',
        'plate_number',
        'color_channel',
        'plate_type',
        'size',
        'screen_lpi',
        'status',
        'usage_count',
        'max_usage',
        'created_at_plate',
        'mounted_at',
        'dismounted_at',
        'cleaned_at',
        'created_by',
        'notes'
    ];

    protected $casts = [
        'created_at_plate' => 'datetime',
        'mounted_at' => 'datetime',
        'dismounted_at' => 'datetime',
        'cleaned_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function printJob()
    {
        return $this->belongsTo(PrintJob::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'mounted' => 'blue',
            'in_use' => 'purple',
            'cleaned' => 'yellow',
            'retired' => 'gray',
            default => 'gray'
        };
    }

    public function getUsagePercentageAttribute(): float
    {
        return $this->max_usage > 0 ? ($this->usage_count / $this->max_usage) * 100 : 0;
    }
}
