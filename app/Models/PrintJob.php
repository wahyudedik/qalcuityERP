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

    // Status constants for printing module workflow
    const STATUS_QUEUED = 'queued';
    const STATUS_PREPRESS = 'prepress';
    const STATUS_PLATEMAKING = 'platemaking';
    const STATUS_ON_PRESS = 'on_press';
    const STATUS_FINISHING = 'finishing';
    const STATUS_QUALITY_CHECK = 'quality_check';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    // Status constants for POS print queue
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_FAILED = 'failed';

    const STATUSES = [
        self::STATUS_QUEUED,
        self::STATUS_PREPRESS,
        self::STATUS_PLATEMAKING,
        self::STATUS_ON_PRESS,
        self::STATUS_FINISHING,
        self::STATUS_QUALITY_CHECK,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_PENDING,
        self::STATUS_PROCESSING,
        self::STATUS_FAILED,
    ];

    const PRIORITIES = ['low', 'normal', 'high', 'urgent'];

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
        'notes',
        // POS print queue fields
        'job_type',
        'reference_id',
        'reference_number',
        'printer_type',
        'printer_destination',
        'print_data',
        'error_message',
        'retry_count',
        'processed_at',
    ];

    protected $casts = [
        'specifications' => 'array',
        'print_data' => 'array',
        'proof_approved' => 'boolean',
        'proof_approved_at' => 'datetime',
        'due_date' => 'date',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'processed_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'quoted_price' => 'decimal:2',
        'paper_size_width' => 'decimal:2',
        'paper_size_height' => 'decimal:2',
        'retry_count' => 'integer',
    ];

    // ==========================================
    // Relationships
    // ==========================================

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

    // ==========================================
    // Accessors
    // ==========================================

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
            'pending' => 'yellow',
            'processing' => 'blue',
            'failed' => 'red',
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

    // ==========================================
    // Scopes
    // ==========================================

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled', 'failed']);
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

    // ==========================================
    // POS Print Queue Methods
    // ==========================================

    /**
     * Mark job as processing
     */
    public function markAsProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Check if job can be retried
     */
    public function canRetry(): bool
    {
        $maxRetries = config('pos_printer.queue.retry_attempts', 3);
        return $this->retry_count < $maxRetries && in_array($this->status, ['failed', 'pending']);
    }

    /**
     * Retry the job
     */
    public function retry(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'retry_count' => ($this->retry_count ?? 0) + 1,
            'error_message' => null,
        ]);
    }

    /**
     * Cancel the job
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }
}
