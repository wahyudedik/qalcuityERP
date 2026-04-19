<?php

namespace App\Models;

use App\Traits\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatSession extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id', 'user_id', 'title', 'total_tokens', 'last_model', 'is_active', 'metadata',
        'session_type', 'active_plan', 'execution_status', 'erp_context_snapshot', 'is_cancelled',
    ];

    protected function casts(): array
    {
        return [
            'is_active'            => 'boolean',
            'metadata'             => 'array',
            'active_plan'          => 'array',
            'erp_context_snapshot' => 'array',
            'is_cancelled'         => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AgentAuditLog::class, 'session_id');
    }

    // Ambil history dalam format yang siap dikirim ke Gemini
    // BUG-AI-001 FIX: Added database-level limit to prevent memory exhaustion
    public function getHistory(int $limit = 20): array
    {
        // BUG-AI-001 FIX: Use database LIMIT instead of loading all messages
        return $this->messages()
            ->orderByDesc('id')
            ->limit($limit) // Database-level limit
            ->get(['role', 'content'])
            ->reverse() // Reverse to get chronological order
            ->map(fn($m) => ['role' => $m->role, 'text' => $m->content])
            ->values()
            ->toArray();
    }
}
