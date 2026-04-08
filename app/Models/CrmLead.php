<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class CrmLead extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'assigned_to', 'name', 'company', 'phone', 'email',
        'source', 'stage', 'estimated_value', 'product_interest',
        'expected_close_date', 'probability', 'notes', 'last_contact_at',
        'converted_to_customer_id',
    ];

    protected $casts = ['expected_close_date' => 'date', 'last_contact_at' => 'datetime', 'estimated_value' => 'float'];

    public function activities() { return $this->hasMany(CrmActivity::class, 'lead_id'); }
    public function assignedUser() { return $this->belongsTo(User::class, 'assigned_to'); }
}
