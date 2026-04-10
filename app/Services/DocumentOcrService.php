<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Document OCR Service
 * 
 * Extracts text from documents using OCR (Tesseract or cloud-based OCR APIs).
 */
class DocumentOcrService
{
    protected string $ocrProvider;
    protected string $apiKey;

    public function __construct()
    {
        $this->ocrProvider = config('services.ocr.provider', 'tesseract');
        $this->apiKey = config('services.ocr.api_key', '');
    }

    /**
     * Process OCR for a document
     */
    public function processDocument(Document $document): bool
    {
        try {
            // Get file content
            $fileContent = Storage::get($document->file_path);

            // Extract text based on provider
            $extractedText = match ($this->ocrProvider) {
                'tesseract' => $this->processWithTesseract($fileContent, $document->file_type),
                'google_vision' => $this->processWithGoogleVision($fileContent),
                'aws_textract' => $this->processWithAwsTextract($fileContent),
                default => throw new \Exception("Unsupported OCR provider: {$this->ocrProvider}"),
            };

            // Update document with OCR text
            $document->markOcrComplete($extractedText);

            Log::info("OCR completed for document {$document->id}", [
                'document_id' => $document->id,
                'text_length' => strlen($extractedText),
                'provider' => $this->ocrProvider,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("OCR failed for document {$document->id}", [
                'error' => $e->getMessage(),
                'provider' => $this->ocrProvider,
            ]);

            return false;
        }
    }

    /**
     * Process OCR with Tesseract (local)
     */
    protected function processWithTesseract(string $fileContent, string $fileType): string
    {
        // Save to temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'ocr_');
        file_put_contents($tempFile, $fileContent);

        // Execute Tesseract
        $language = config('services.ocr.language', 'eng');
        $command = "tesseract {$tempFile} stdout -l {$language} 2>&1";
        $output = shell_exec($command);

        // Clean up
        unlink($tempFile);

        return trim($output ?? '');
    }

    /**
     * Process OCR with Google Vision API
     */
    protected function processWithGoogleVision(string $fileContent): string
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://vision.googleapis.com/v1/images:annotate?key={$this->apiKey}", [
                    'requests' => [
                        [
                            'image' => [
                                'content' => base64_encode($fileContent),
                            ],
                            'features' => [
                                ['type' => 'TEXT_DETECTION'],
                            ],
                        ],
                    ],
                ]);

        if ($response->successful()) {
            $data = $response->json();
            return $data['responses'][0]['fullTextAnnotation']['text'] ?? '';
        }

        throw new \Exception('Google Vision API request failed');
    }

    /**
     * Process OCR with AWS Textract
     */
    protected function processWithAwsTextract(string $fileContent): string
    {
        // AWS Textract implementation
        // Requires AWS SDK for PHP
        // This is a simplified example

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-amz-json-1.1',
            'X-Amz-Target' => 'Textract.DetectDocumentText',
        ])->post("https://textract.{$this->getAwsRegion()}.amazonaws.com/", [
                    'Document' => [
                        'Bytes' => base64_encode($fileContent),
                    ],
                ]);

        if ($response->successful()) {
            $data = $response->json();
            return $this->extractTextFromTextractResponse($data);
        }

        throw new \Exception('AWS Textract request failed');
    }

    /**
     * Search documents by OCR text
     */
    public function searchByOcrContent(int $tenantId, string $searchTerm, int $limit = 20): array
    {
        $documents = Document::where('tenant_id', $tenantId)
            ->where('has_ocr', true)
            ->where('ocr_text', 'like', "%{$searchTerm}%")
            ->select('id', 'title', 'file_name', 'category', 'created_at')
            ->limit($limit)
            ->get()
            ->map(function ($document) use ($searchTerm) {
                // Highlight search term in OCR text
                $highlightedText = $this->highlightText($document->ocr_text, $searchTerm);

                return [
                    'id' => $document->id,
                    'title' => $document->title,
                    'file_name' => $document->file_name,
                    'category' => $document->category,
                    'created_at' => $document->created_at->format('d M Y'),
                    'snippet' => $this->getSnippet($highlightedText, $searchTerm),
                ];
            });

        return [
            'total' => Document::where('tenant_id', $tenantId)
                ->where('has_ocr', true)
                ->where('ocr_text', 'like', "%{$searchTerm}%")
                ->count(),
            'documents' => $documents,
        ];
    }

    /**
     * Get OCR statistics
     */
    public function getOcrStatistics(int $tenantId): array
    {
        $totalDocuments = Document::where('tenant_id', $tenantId)->count();
        $documentsWithOcr = Document::where('tenant_id', $tenantId)->where('has_ocr', true)->count();
        $pendingOcr = Document::where('tenant_id', $tenantId)
            ->where('has_ocr', false)
            ->whereIn('file_type', ['pdf', 'image/jpeg', 'image/png', 'tiff'])
            ->count();

        return [
            'total_documents' => $totalDocuments,
            'documents_with_ocr' => $documentsWithOcr,
            'pending_ocr' => $pendingOcr,
            'ocr_coverage' => $totalDocuments > 0 ? round(($documentsWithOcr / $totalDocuments) * 100, 2) : 0,
            'total_text_extracted' => Document::where('tenant_id', $tenantId)
                ->where('has_ocr', true)
                ->sum(\Illuminate\Support\Str::length('ocr_text')),
        ];
    }

    /**
     * Process documents in batch
     */
    public function processBatch(int $tenantId, int $limit = 50): array
    {
        $documents = Document::where('tenant_id', $tenantId)
            ->where('has_ocr', false)
            ->whereIn('file_type', ['pdf', 'image/jpeg', 'image/png', 'tiff'])
            ->limit($limit)
            ->get();

        $results = [
            'total' => $documents->count(),
            'success' => 0,
            'failed' => 0,
        ];

        foreach ($documents as $document) {
            if ($this->processDocument($document)) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Highlight search term in text
     */
    protected function highlightText(string $text, string $searchTerm): string
    {
        return str_ireplace(
            $searchTerm,
            "<mark>{$searchTerm}</mark>",
            $text
        );
    }

    /**
     * Get snippet around search term
     */
    protected function getSnippet(string $text, string $searchTerm, int $maxLength = 200): string
    {
        $position = stripos($text, $searchTerm);

        if ($position === false) {
            return substr($text, 0, $maxLength);
        }

        $start = max(0, $position - 50);
        $length = min($maxLength, strlen($text) - $start);

        return substr($text, $start, $length);
    }

    /**
     * Get AWS region from config
     */
    protected function getAwsRegion(): string
    {
        return config('services.aws.region', 'us-east-1');
    }

    /**
     * Extract text from Textract response
     */
    protected function extractTextFromTextractResponse(array $data): string
    {
        $text = '';

        if (isset($data['Blocks'])) {
            foreach ($data['Blocks'] as $block) {
                if ($block['BlockType'] === 'LINE' && isset($block['Text'])) {
                    $text .= $block['Text'] . "\n";
                }
            }
        }

        return trim($text);
    }
}
