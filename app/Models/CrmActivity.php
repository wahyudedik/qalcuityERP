<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmActivity extends Model
{
    use BelongsToTenant;

    // Activity type constants
    const TYPE_CALL = 'call';
    const TYPE_EMAIL = 'email';
    const TYPE_MEETING = 'meeting';
    const TYPE_WHATSAPP = 'whatsapp';
    const TYPE_DEMO = 'demo';
    const TYPE_PROPOSAL = 'proposal';

    const TYPES = [
        self::TYPE_CALL,
        self::TYPE_EMAIL,
        self::TYPE_MEETING,
        self::TYPE_WHATSAPP,
        self::TYPE_DEMO,
        self::TYPE_PROPOSAL,
    ];

    // Outcome constants
    const OUTCOME_INTERESTED = 'interested';
    const OUTCOME_NOT_INTERESTED = 'not_interested';
    const OUTCOME_FOLLOW_UP = 'follow_up';
    const OUTCOME_CLOSED = 'closed';

    const OUTCOMES = [
        self::OUTCOME_INTERESTED,
        self::OUTCOME_NOT_INTERESTED,
        self::OUTCOME_FOLLOW_UP,
        self::OUTCOME_CLOSED,
    ];

    protected $fillable = [
        'tenant_id',
        'lead_id',
        'user_id',
        'type',
        'description',
        'outcome',
        'next_follow_up',
    ];

    protected $casts = [
        'next_follow_up' => 'date',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(CrmLead::class, 'lead_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
