<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentAuditLog extends Model
{
    // NO SoftDeletes - audit log tidak boleh dihapus oleh user biasa (Requirement 9.4)
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'session_id',
        'action_name',
        'action_type',
        'parameters',
        'result',
        'status',
        'error_message',
        'is_undoable',
        'undoable_until',
    ];

    protected function casts(): array
    {
        return [
            'parameters'    => 'array',
            'result'        => 'array',
            'is_undoable'   => 'boolean',
            'undoable_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }
}
