<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpiryReport extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'report_number',
        'report_type',
        'start_date',
        'end_date',
        'summary_data',
        'total_batches_monitored',
        'batches_expired',
        'batches_recalled',
        'total_loss_value',
        'generated_by',
        'file_path',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'summary_data' => 'array',
        'total_loss_value' => 'decimal:2',
    ];

    // Report type labels
    public function getTypeLabelAttribute(): string
    {
        return match ($this->report_type) {
            'monthly' => 'Monthly Report',
            'quarterly' => 'Quarterly Report',
            'annual' => 'Annual Report',
            'ad_hoc' => 'Ad-Hoc Report',
            default => ucfirst(str_replace('_', ' ', $this->report_type))
        };
    }

    // Generate next report number
    public static function getNextReportNumber(): string
    {
        $year = now()->format('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'EXP-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // Calculate expiry rate
    public function getExpiryRateAttribute(): float
    {
        if ($this->total_batches_monitored <= 0) {
            return 0;
        }
        return round(($this->batches_expired / $this->total_batches_monitored) * 100, 2);
    }

    // Calculate recall rate
    public function getRecallRateAttribute(): float
    {
        if ($this->total_batches_monitored <= 0) {
            return 0;
        }
        return round(($this->batches_recalled / $this->total_batches_monitored) * 100, 2);
    }

    // Scopes
    public function scopeMonthly($query)
    {
        return $query->where('report_type', 'monthly');
    }

    public function scopeQuarterly($query)
    {
        return $query->where('report_type', 'quarterly');
    }

    public function scopeAnnual($query)
    {
        return $query->where('report_type', 'annual');
    }

    // Relationships
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}