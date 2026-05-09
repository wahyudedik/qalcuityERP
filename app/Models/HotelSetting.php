<?php

namespace App\Models;

use App\Traits\AuditsChanges;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelSetting extends Model
{
    use AuditsChanges;
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'check_in_time',
        'check_out_time',
        'currency',
        'tax_rate',
        'deposit_required',
        'default_deposit_amount',
        'overbooking_allowed',
        'auto_assign_room',
    ];

    protected function casts(): array
    {
        return [
            'tax_rate' => 'decimal:2',
            'default_deposit_amount' => 'decimal:2',
            'deposit_required' => 'boolean',
            'overbooking_allowed' => 'boolean',
            'auto_assign_room' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
