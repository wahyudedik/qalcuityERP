<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataRequest extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'request_type',
        'details',
        'status',
        'rejection_reason',
        'processed_by_user_id',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';

    const STATUS_APPROVED = 'approved';

    const STATUS_REJECTED = 'rejected';

    const STATUS_COMPLETED = 'completed';

    const TYPE_ACCESS = 'access';

    const TYPE_ERASURE = 'erasure';

    const TYPE_RECTIFICATION = 'rectification';

    const TYPE_PORTABILITY = 'portability';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }
}
