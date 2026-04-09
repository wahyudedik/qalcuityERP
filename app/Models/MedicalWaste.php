<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalWaste extends Model
{
    use HasFactory;

    protected $fillable = [
        'waste_code',
        'waste_type',
        'category',
        'weight_kg',
        'volume_liters',
        'collection_point',
        'collected_by',
        'collected_at',
        'disposal_method',
        'disposal_location',
        'disposed_by',
        'disposed_at',
        'tracking_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'disposed_at' => 'datetime',
        'weight_kg' => 'decimal:2',
        'volume_liters' => 'decimal:2',
    ];

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }

    public function disposedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    public function disposalLogs(): HasMany
    {
        return $this->hasMany(WasteDisposalLog::class);
    }
}
