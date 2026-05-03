<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CrmLead extends Model
{
    use BelongsToTenant;

    // Stage constants for type safety
    const STAGE_NEW = 'new';
    const STAGE_CONTACTED = 'contacted';
    const STAGE_QUALIFIED = 'qualified';
    const STAGE_PROPOSAL = 'proposal';
    const STAGE_NEGOTIATION = 'negotiation';
    const STAGE_WON = 'won';
    const STAGE_LOST = 'lost';
    const STAGE_CONVERTED = 'converted';

    const STAGES = [
        self::STAGE_NEW,
        self::STAGE_CONTACTED,
        self::STAGE_QUALIFIED,
        self::STAGE_PROPOSAL,
        self::STAGE_NEGOTIATION,
        self::STAGE_WON,
        self::STAGE_LOST,
        self::STAGE_CONVERTED,
    ];

    const ACTIVE_STAGES = [
        self::STAGE_NEW,
        self::STAGE_CONTACTED,
        self::STAGE_QUALIFIED,
        self::STAGE_PROPOSAL,
        self::STAGE_NEGOTIATION,
    ];

    // Source constants
    const SOURCE_REFERRAL = 'referral';
    const SOURCE_WEBSITE = 'website';
    const SOURCE_COLD_CALL = 'cold_call';
    const SOURCE_SOCIAL_MEDIA = 'social_media';
    const SOURCE_EXHIBITION = 'exhibition';

    const SOURCES = [
        self::SOURCE_REFERRAL,
        self::SOURCE_WEBSITE,
        self::SOURCE_COLD_CALL,
        self::SOURCE_SOCIAL_MEDIA,
        self::SOURCE_EXHIBITION,
    ];

    protected $fillable = [
        'tenant_id',
        'assigned_to',
        'name',
        'company',
        'phone',
        'email',
        'source',
        'stage',
        'estimated_value',
        'product_interest',
        'expected_close_date',
        'probability',
        'notes',
        'last_contact_at',
        'converted_to_customer_id',
        'address',
    ];

    protected $casts = [
        'expected_close_date' => 'date',
        'last_contact_at' => 'datetime',
        'estimated_value' => 'float',
        'probability' => 'integer',
    ];

    public function activities(): HasMany
    {
        return $this->hasMany(CrmActivity::class, 'lead_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function convertedCustomer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'converted_to_customer_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if lead is in an active pipeline stage.
     */
    public function isActive(): bool
    {
        return in_array($this->stage, self::ACTIVE_STAGES);
    }

    /**
     * Check if lead has been converted to a customer.
     */
    public function isConverted(): bool
    {
        return $this->converted_to_customer_id !== null;
    }
}
