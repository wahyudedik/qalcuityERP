<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffiliateAuditLog extends Model
{
    protected $fillable = [
        'affiliate_id', 'event', 'severity', 'description',
        'metadata', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliate::class);
    }

    public static function log(int $affiliateId, string $event, string $description, string $severity = 'info', array $metadata = []): void
    {
        self::create([
            'affiliate_id' => $affiliateId,
            'event' => $event,
            'severity' => $severity,
            'description' => $description,
            'metadata' => $metadata ?: null,
            'ip_address' => request()->ip(),
        ]);
    }
}
