<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LabSample extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'sample_number',
        'lab_order_id',
        'patient_id',
        'patient_visit_id',
        'collected_by',
        'collection_date',
        'sample_type',
        'container_type',
        'container_color',
        'volume',
        'collection_method',
        'collection_site',
        'requires_centrifuge',
        'is_stat',
        'status',
        'received_at',
        'rejected_reason',
        'notes',
    ];

    protected $casts = [
        'collection_date' => 'datetime',
        'received_at' => 'datetime',
        'volume' => 'integer',
        'requires_centrifuge' => 'boolean',
        'is_stat' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function ($sample) {
            if (empty($sample->sample_number)) {
                $sample->sample_number = static::generateSampleNumber();
            }
        });
    }

    /**
     * Generate unique sample number
     * Format: SAMPLE-YYYYMMDD-XXXX
     */
    public static function generateSampleNumber()
    {
        $date = now()->format('Ymd');
        $prefix = 'SAMPLE-' . $date;

        $lastSample = static::where('sample_number', 'like', $prefix . '%')
            ->orderBy('sample_number', 'desc')
            ->first();

        if ($lastSample) {
            $lastNumber = (int) substr($lastSample->sample_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $prefix . '-' . $newNumber;
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'collected' => 'Collected',
            'in_transit' => 'In Transit',
            'received' => 'Received',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Scope: By status
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Collected today
     */
    public function scopeToday($query)
    {
        return $query->whereDate('collection_date', today());
    }

    /**
     * Scope: STAT samples
     */
    public function scopeStat($query)
    {
        return $query->where('is_stat', true);
    }

    /**
     * Scope: Rejected samples
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Relation: Lab order
     */
    public function labOrder()
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    /**
     * Relation: Patient
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relation: Collected by user
     */
    public function collectedBy()
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    /**
     * Mark as received
     */
    public function markAsReceived()
    {
        $this->update([
            'status' => 'received',
            'received_at' => now(),
        ]);
    }

    /**
     * Mark as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark as completed
     */
    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    /**
     * Reject sample
     */
    public function reject($reason)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_reason' => $reason,
        ]);
    }

    /**
     * Get sample summary
     */
    public function getSummaryAttribute()
    {
        return [
            'sample_number' => $this->sample_number,
            'type' => $this->sample_type,
            'status' => $this->status_label,
            'collection_date' => $this->collection_date,
            'patient_name' => $this->patient?->full_name,
            'is_stat' => $this->is_stat,
        ];
    }
}
