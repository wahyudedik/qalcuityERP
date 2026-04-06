<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrintEstimate extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'print_job_id',
        'estimate_number',
        'customer_id',
        'product_type',
        'quantity',
        'paper_type',
        'paper_size_width',
        'paper_size_height',
        'colors_front',
        'colors_back',
        'finishing_options',
        'paper_cost',
        'plate_cost',
        'ink_cost',
        'labor_cost',
        'machine_cost',
        'finishing_cost',
        'overhead_cost',
        'total_cost',
        'markup_percentage',
        'quoted_price',
        'profit_margin',
        'status',
        'valid_until',
        'cost_breakdown',
        'terms_and_conditions',
        'notes',
        'created_by',
        'approved_by',
        'approved_at'
    ];

    protected $casts = [
        'cost_breakdown' => 'array',
        'valid_until' => 'date',
        'approved_at' => 'datetime',
        'paper_size_width' => 'decimal:2',
        'paper_size_height' => 'decimal:2',
        'paper_cost' => 'decimal:2',
        'plate_cost' => 'decimal:2',
        'ink_cost' => 'decimal:2',
        'labor_cost' => 'decimal:2',
        'machine_cost' => 'decimal:2',
        'finishing_cost' => 'decimal:2',
        'overhead_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'profit_margin' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function printJob()
    {
        return $this->belongsTo(PrintJob::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'sent' => 'blue',
            'accepted' => 'green',
            'rejected' => 'red',
            'expired' => 'orange',
            default => 'gray'
        };
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->valid_until && $this->valid_until->isPast();
    }
}
