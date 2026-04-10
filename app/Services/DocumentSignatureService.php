<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Document Signature Service
 * 
 * Handles digital and electronic document signing with hash verification.
 */
class DocumentSignatureService
{
    /**
     * Sign a document
     */
    public function signDocument(Document $document, string $signatureType = 'electronic', array $metadata = []): DocumentSignature
    {
        // Get document content for hashing
        $documentContent = Storage::get($document->file_path);

        // Generate signature hash
        $signatureHash = $this->generateSignatureHash($documentContent, $document->id);

        // Create signature record
        $signature = DocumentSignature::create([
            'document_id' => $document->id,
            'signer_id' => Auth::id(),
            'signature_type' => $signatureType,
            'signature_hash' => $signatureHash,
            'certificate_serial' => $metadata['certificate_serial'] ?? null,
            'signature_metadata' => array_merge([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'signed_at' => now()->toISOString(),
            ], $metadata),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'signed_at' => now(),
        ]);

        // Update document
        $document->markSigned($signatureHash);

        return $signature;
    }

    /**
     * Verify document signature
     */
    public function verifySignature(DocumentSignature $signature): bool
    {
        $document = $signature->document;
        $documentContent = Storage::get($document->file_path);

        // Recompute hash
        $computedHash = $this->generateSignatureHash($documentContent, $document->id);

        return $computedHash === $signature->signature_hash;
    }

    /**
     * Verify all signatures for a document
     */
    public function verifyAllSignatures(Document $document): array
    {
        $signatures = $document->signatures;
        $results = [];

        foreach ($signatures as $signature) {
            $results[] = [
                'signature_id' => $signature->id,
                'signer' => $signature->signer?->name ?? 'Unknown',
                'signature_type' => $signature->signature_type,
                'signed_at' => $signature->signed_at->format('d M Y H:i'),
                'is_valid' => $this->verifySignature($signature),
            ];
        }

        return $results;
    }

    /**
     * Generate signature hash
     */
    protected function generateSignatureHash(string $content, int $documentId): string
    {
        $data = $content . $documentId . now()->timestamp;
        return hash('sha256', $data);
    }

    /**
     * Get signing certificate info
     */
    public function getCertificateInfo(DocumentSignature $signature): ?array
    {
        if (!$signature->hasCertificate()) {
            return null;
        }

        return [
            'serial' => $signature->certificate_serial,
            'metadata' => $signature->signature_metadata,
        ];
    }

    /**
     * Check if document is fully signed
     */
    public function isFullySigned(Document $document): bool
    {
        return $document->is_signed && $document->signatures()->count() > 0;
    }

    /**
     * Get document signature history
     */
    public function getSignatureHistory(Document $document): array
    {
        $signatures = DocumentSignature::with('signer:id,name,email')
            ->where('document_id', $document->id)
            ->orderBy('signed_at', 'asc')
            ->get();

        return [
            'is_signed' => $document->is_signed,
            'signature_count' => $signatures->count(),
            'signatures' => $signatures->map(function ($signature) {
                return [
                    'id' => $signature->id,
                    'signer' => $signature->signer?->name ?? 'Unknown',
                    'signature_type' => ucfirst($signature->signature_type),
                    'has_certificate' => $signature->hasCertificate(),
                    'signed_at' => $signature->signed_at->format('d M Y H:i'),
                    'ip_address' => $signature->ip_address,
                ];
            }),
        ];
    }

    /**
     * Bulk sign documents
     */
    public function bulkSignDocuments(array $documentIds, string $signatureType = 'electronic'): array
    {
        $results = [
            'total' => count($documentIds),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($documentIds as $documentId) {
            try {
                $document = Document::findOrFail($documentId);

                if ($document->is_signed) {
                    $results['failed']++;
                    $results['errors'][] = "Document {$documentId} already signed";
                    continue;
                }

                $this->signDocument($document, $signatureType);
                $results['success']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Document {$documentId}: {$e->getMessage()}";
            }
        }

        return $results;
    }

    /**
     * Get signature statistics
     */
    public function getSignatureStatistics(int $tenantId): array
    {
        $totalDocuments = Document::where('tenant_id', $tenantId)->count();
        $signedDocuments = Document::where('tenant_id', $tenantId)->where('is_signed', true)->count();
        $totalSignatures = DocumentSignature::whereHas('document', function ($query) use ($tenantId) {
            $query->where('tenant_id', $tenantId);
        })->count();

        return [
            'total_documents' => $totalDocuments,
            'signed_documents' => $signedDocuments,
            'unsigned_documents' => $totalDocuments - $signedDocuments,
            'signature_rate' => $totalDocuments > 0 ? round(($signedDocuments / $totalDocuments) * 100, 2) : 0,
            'total_signatures' => $totalSignatures,
            'avg_signatures_per_doc' => $signedDocuments > 0 ? round($totalSignatures / $signedDocuments, 2) : 0,
        ];
    }
}
