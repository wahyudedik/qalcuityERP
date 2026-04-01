<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LivestockHealthRecord extends Model
{
    protected $fillable = [
        'livestock_herd_id', 'tenant_id', 'user_id', 'date', 'type',
        'condition', 'affected_count', 'death_count', 'symptoms',
        'medication', 'medication_cost', 'administered_by',
        'severity', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return ['date' => 'date', 'medication_cost' => 'decimal:2'];
    }

    public const TYPE_LABELS = [
        'illness'     => '🤒 Penyakit',
        'treatment'   => '💊 Pengobatan',
        'observation' => '👁️ Observasi',
        'quarantine'  => '🔒 Karantina',
        'recovery'    => '✅ Sembuh',
    ];

    public const SEVERITY_COLORS = [
        'low' => 'gray', 'medium' => 'amber', 'high' => 'orange', 'critical' => 'red',
    ];

    public function herd(): BelongsTo { return $this->belongsTo(LivestockHerd::class, 'livestock_herd_id'); }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function typeLabel(): string { return self::TYPE_LABELS[$this->type] ?? $this->type; }
    public function severityColor(): string { return self::SEVERITY_COLORS[$this->severity] ?? 'gray'; }
}
