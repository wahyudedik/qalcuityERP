<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

class Document extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'uploaded_by',
        'title',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'related_type',
        'related_id',
        'description',
        'tags',
        'version',
        'parent_id',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'expires_at',
        'archived_at',
        'storage_provider',
        'storage_bucket',
        'ocr_text',
        'has_ocr',
        'digital_signature',
        'is_signed',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'file_size' => 'integer',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
            'archived_at' => 'datetime',
            'has_ocr' => 'boolean',
            'is_signed' => 'boolean',
            'signed_at' => 'datetime',
        ];
    }

    /**
     * Get the user who uploaded the document
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the related model (polymorphic)
     */
    public function related()
    {
        return $this->morphTo();
    }

    /**
     * Get the user who approved the document
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the parent document (for versioning)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    /**
     * Get all child versions
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->latestFirst();
    }

    /**
     * Get all approval requests
     */
    public function approvalRequests(): HasMany
    {
        return $this->hasMany(DocumentApprovalRequest::class);
    }

    /**
     * Get all signatures
     */
    public function signatures(): MorphMany
    {
        return $this->morphMany(DocumentSignature::class, 'signable');
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / 1048576, 1).' MB';
    }

    /**
     * Check if document is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if document is pending approval
     */
    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    /**
     * Check if document is archived
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived' || $this->archived_at !== null;
    }

    /**
     * Check if document is signed
     */
    public function isSigned(): bool
    {
        return $this->is_signed;
    }

    /**
     * Check if document has OCR text
     */
    public function hasOcr(): bool
    {
        return $this->has_ocr;
    }

    /**
     * Approve the document
     */
    public function approve(int $userId, string $notes = ''): bool
    {
        return $this->update([
            'status' => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject the document
     */
    public function reject(string $notes = ''): bool
    {
        return $this->update([
            'status' => 'rejected',
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Archive the document
     */
    public function archive(): bool
    {
        return $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
    }

    /**
     * Mark document as having OCR
     */
    public function markOcrComplete(string $ocrText): bool
    {
        return $this->update([
            'has_ocr' => true,
            'ocr_text' => $ocrText,
        ]);
    }

    /**
     * Mark document as signed
     */
    public function markSigned(string $signatureHash): bool
    {
        return $this->update([
            'is_signed' => true,
            'digital_signature' => $signatureHash,
            'signed_at' => now(),
        ]);
    }

    /**
     * Create a new version
     */
    public function createVersion(array $data): DocumentVersion
    {
        $newVersion = $this->version + 1;

        $version = $this->versions()->create([
            'version' => $newVersion,
            'file_name' => $data['file_name'] ?? $this->file_name,
            'file_path' => $data['file_path'] ?? $this->file_path,
            'file_size' => $data['file_size'] ?? $this->file_size,
            'changed_by' => $data['changed_by'] ?? Auth::id(),
            'change_summary' => $data['change_summary'] ?? '',
        ]);

        // Update current document
        $this->update([
            'version' => $newVersion,
            'file_name' => $data['file_name'] ?? $this->file_name,
            'file_path' => $data['file_path'] ?? $this->file_path,
            'file_size' => $data['file_size'] ?? $this->file_size,
        ]);

        return $version;
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry(): ?int
    {
        if (! $this->expires_at) {
            return null;
        }

        return now()->diffInDays($this->expires_at, false);
    }

    /**
     * Scope to get documents expiring soon
     */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<', now()->addDays($days));
    }

    /**
     * Scope to get expired documents
     */
    public function scopeExpired($query)
    {
        return $query->whereNotNull('expires_at')
            ->where('expires_at', '<', now());
    }

    /**
     * Scope to get documents pending approval
     */
    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    /**
     * Scope to get approved documents
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope to get signed documents
     */
    public function scopeSigned($query)
    {
        return $query->where('is_signed', true);
    }

    /**
     * Scope to get documents with OCR
     */
    public function scopeWithOcr($query)
    {
        return $query->where('has_ocr', true);
    }

    /**
     * Scope to search in OCR text
     */
    public function scopeSearchOcr($query, string $searchTerm)
    {
        return $query->where('has_ocr', true)
            ->where('ocr_text', 'like', "%{$searchTerm}%");
    }
}
