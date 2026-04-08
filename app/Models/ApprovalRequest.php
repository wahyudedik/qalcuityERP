<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;

class ApprovalRequest extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'workflow_id', 'requested_by', 'approved_by',
        'model_type', 'model_id', 'status', 'amount',
        'notes', 'rejection_reason', 'responded_at',
    ];

    protected $casts = ['responded_at' => 'datetime', 'amount' => 'float'];

    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function approver()  { return $this->belongsTo(User::class, 'approved_by'); }
    public function workflow()  { return $this->belongsTo(ApprovalWorkflow::class); }
    public function subject()   { return $this->morphTo('model'); }
}
