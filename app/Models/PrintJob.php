<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrintJob extends Model
{
    use BelongsToTenant;
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'job_number',
        'customer_id',
        'job_name',
        'description',
        'product_type',
        'status',
        'priority',
        'due_date',
        'quantity',
        'paper_type',
        'paper_size_width',
        'paper_size_height',
        'colors_front',
        'colors_back',
        'finishing_type',
        'specifications',
        'file_path',
        'proof_path',
        'proof_approved',
        'proof_approved_at',
        'approved_by',
        'estimated_cost',
        'actual_cost',
        'quoted_price',
        'started_at',
        'completed_at',
        'assigned_operator',
        'special_instructions',
        'notes'
    ];

    protected $casts = [
        'specifications' => 'array',
        'proof_approved' => 'boolean',
        'proof_approved_at' => 'datetime',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'paper_size_width' => 'decimal:2',
        'paper_size_height' => 'decimal:2',
    ];

    // Relationships
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedOperator()
    {
        return $this->belongsTo(User::class, 'assigned_operator');
    }

    public function prepressWorkflows()
    {
        return $this->hasMany(PrepressWorkflow::class);
    }

    public function plates()
    {
        return $this->hasMany(PrintingPlate::class);
    }

    public function pressRuns()
    {
        return $this->hasMany(PressRun::class);
    }

    public function finishingOperations()
    {
        return $this->hasMany(FinishingOperation::class);
    }

    public function estimates()
    {
        return $this->hasMany(PrintEstimate::class);
    }

    public function webOrders()
    {
        return $this->hasMany(WebToPrintOrder::class);
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'queued' => 'gray',
            'prepress' => 'blue',
            'platemaking' => 'indigo',
            'on_press' => 'purple',
            'finishing' => 'orange',
            'quality_check' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match ($this->priority) {
            'low' => 'gray',
            'normal' => 'blue',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'blue'
        };
    }

    public function getProgressPercentageAttribute(): float
    {
        $stages = ['queued', 'prepress', 'platemaking', 'on_press', 'finishing', 'quality_check', 'completed'];
        $currentIndex = array_search($this->status, $stages);
        return $currentIndex !== false ? ($currentIndex / (count($stages) - 1)) * 100 : 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->whereNotIn('status', ['completed', 'cancelled']);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }
}
