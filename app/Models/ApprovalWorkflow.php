<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ApprovalWorkflow extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'name', 'model_type', 'min_amount', 'max_amount',
        'approver_roles', 'is_active',
    ];

    protected $casts = [
        'approver_roles' => 'array',
        'min_amount'     => 'float',
        'max_amount'     => 'float',
        'is_active'      => 'boolean',
    ];

    public function requests() { return $this->hasMany(ApprovalRequest::class, 'workflow_id'); }

    public function appliesToAmount(float $amount): bool
    {
        if ($amount < $this->min_amount) return false;
        if ($this->max_amount && $amount > $this->max_amount) return false;
        return true;
    }
}
