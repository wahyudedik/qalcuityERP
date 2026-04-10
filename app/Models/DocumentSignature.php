<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignature extends Model
{
    protected $fillable = [
        'document_id',
        'signer_id',
        'signature_type',
        'signature_hash',
        'certificate_serial',
        'signature_metadata',
        'ip_address',
        'user_agent',
        'signed_at',
    ];

    protected function casts(): array
    {
        return [
            'signature_metadata' => 'array',
            'signed_at' => 'datetime',
        ];
    }

    /**
     * Get the signed document
     */
    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the signer user
     */
    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }

    /**
     * Scope to get digital signatures
     */
    public function scopeDigital($query)
    {
        return $query->where('signature_type', 'digital');
    }

    /**
     * Scope to get electronic signatures
     */
    public function scopeElectronic($query)
    {
        return $query->where('signature_type', 'electronic');
    }

    /**
     * Scope to filter by signer
     */
    public function scopeBySigner($query, int $signerId)
    {
        return $query->where('signer_id', $signerId);
    }

    /**
     * Verify signature hash
     */
    public function verifyHash(string $content): bool
    {
        $computedHash = hash('sha256', $content);
        return $computedHash === $this->signature_hash;
    }

    /**
     * Check if signature is digital
     */
    public function isDigital(): bool
    {
        return $this->signature_type === 'digital';
    }

    /**
     * Check if signature has certificate
     */
    public function hasCertificate(): bool
    {
        return !empty($this->certificate_serial);
    }

    /**
     * Get signature metadata value
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->signature_metadata[$key] ?? $default;
    }
}
