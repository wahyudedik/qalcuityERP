<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApprovalRequest extends Model
{
    protected $fillable = [
        'document_id',
        'workflow_id',
        'step_number',
        'approver_id',
        'approver_role',
        'status',
        'comments',
        'actioned_at',
    ];

    protected function casts(): array
    {
        return [
            'step_number' => 'integer',
            'actioned_at' => 'datetime',
        ];
    }

    /**
     * Get the document being approved
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the workflow
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(DocumentApprovalWorkflow::class);
    }

    /**
     * Get the approver user
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope to get pending approvals
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get approved requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get rejected requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope to get approvals for a specific user
     */
    public function scopeForApprover($query, int $userId)
    {
        return $query->where('approver_id', $userId);
    }

    /**
     * Check if approval is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if approval is completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['approved', 'rejected', 'skipped']);
    }

    /**
     * Approve the request
     */
    public function approve(int $approverId, string $comments = ''): bool
    {
        return $this->update([
            'status' => 'approved',
            'approver_id' => $approverId,
            'comments' => $comments,
            'actioned_at' => now(),
        ]);
    }

    /**
     * Reject the request
     */
    public function reject(int $approverId, string $comments = ''): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approver_id' => $approverId,
            'comments' => $comments,
            'actioned_at' => now(),
        ]);
    }
}
