<?php

namespace App\Services\AI;

use App\Models\ImageRecognitionResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageRecognitionService
{
    /**
     * Analyze image for product detection
     */
    public function detectProducts(string $imagePath, int $tenantId, int $userId): array
    {
        return $this->analyzeImage($imagePath, $tenantId, $userId, 'product_detection');
    }

    /**
     * Analyze image for damage assessment
     */
    public function assessDamage(string $imagePath, int $tenantId, int $userId): array
    {
        return $this->analyzeImage($imagePath, $tenantId, $userId, 'damage_assessment');
    }

    /**
     * OCR - Extract text from image (receipts, documents)
     */
    public function extractText(string $imagePath, int $tenantId, int $userId): array
    {
        return $this->analyzeImage($imagePath, $tenantId, $userId, 'ocr');
    }

    /**
     * Core image analysis method
     */
    protected function analyzeImage(string $imagePath, int $tenantId, int $userId, string $type): array
    {
        try {
            // Step 1: Send to AI vision API
            $analysisResult = $this->callVisionAPI($imagePath, $type);

            if (! $analysisResult['success']) {
                return $analysisResult;
            }

            // Step 2: Save result
            $result = ImageRecognitionResult::create([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'image_path' => $imagePath,
                'recognition_type' => $type,
                'detected_objects' => $analysisResult['objects'] ?? [],
                'labels' => $analysisResult['labels'] ?? [],
                'confidence_score' => $analysisResult['confidence'] ?? 0,
                'metadata' => $analysisResult['metadata'] ?? [],
                'description' => $analysisResult['description'] ?? null,
            ]);

            return [
                'success' => true,
                'result_id' => $result->id,
                'objects' => $analysisResult['objects'] ?? [],
                'labels' => $analysisResult['labels'] ?? [],
                'confidence' => $analysisResult['confidence'] ?? 0,
                'description' => $analysisResult['description'] ?? null,
            ];

        } catch (\Throwable $e) {
            Log::error('Image recognition failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call Vision API (Google Cloud Vision, AWS Rekognition, or Azure Computer Vision)
     */
    protected function callVisionAPI(string $imagePath, string $type): array
    {
        // Try Google Cloud Vision API first
        $googleKey = config('services.google.cloud_api_key');

        if ($googleKey) {
            return $this->googleCloudVision($imagePath, $type, $googleKey);
        }

        // Fallback to AWS Rekognition
        $awsKey = config('services.aws.access_key_id');

        if ($awsKey) {
            return $this->awsRekognition($imagePath, $type);
        }

        // Development mock
        return $this->mockVisionAPI($imagePath, $type);
    }

    /**
     * Google Cloud Vision API
     */
    protected function googleCloudVision(string $imagePath, string $type, string $apiKey): array
    {
        $imageContent = base64_encode(Storage::get($imagePath));

        $features = match ($type) {
            'product_detection' => ['LABEL_DETECTION', 'OBJECT_LOCALIZATION'],
            'damage_assessment' => ['LABEL_DETECTION', 'IMAGE_PROPERTIES'],
            'ocr' => ['TEXT_DETECTION', 'DOCUMENT_TEXT_DETECTION'],
            default => ['LABEL_DETECTION']
        };

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://vision.googleapis.com/v1/images:annotate?key={$apiKey}", [
            'requests' => [
                [
                    'image' => [
                        'content' => $imageContent,
                    ],
                    'features' => array_map(fn ($f) => ['type' => $f], $features),
                ],
            ],
        ]);

        if ($response->successful()) {
            $results = $response->json('responses.0');

            return $this->parseGoogleVisionResponse($results, $type);
        }

        return ['success' => false, 'error' => 'API request failed'];
    }

    /**
     * Parse Google Vision API response
     */
    protected function parseGoogleVisionResponse(array $response, string $type): array
    {
        $objects = [];
        $labels = [];
        $confidence = 0;

        // Extract labels
        if (isset($response['labelAnnotations'])) {
            foreach ($response['labelAnnotations'] as $label) {
                $labels[] = [
                    'name' => $label['description'],
                    'confidence' => $label['score'],
                ];

                if ($label['score'] > $confidence) {
                    $confidence = $label['score'];
                }
            }
        }

        // Extract objects (for product detection)
        if (isset($response['localizedObjectAnnotations'])) {
            foreach ($response['localizedObjectAnnotations'] as $obj) {
                $objects[] = [
                    'name' => $obj['name'],
                    'confidence' => $obj['score'],
                    'bounding_box' => $obj['boundingPoly']['normalizedVertices'] ?? null,
                ];
            }
        }

        // Extract text (for OCR)
        $description = null;
        if (isset($response['fullTextAnnotation'])) {
            $description = $response['fullTextAnnotation']['text'];
        }

        return [
            'success' => true,
            'objects' => $objects,
            'labels' => $labels,
            'confidence' => $confidence,
            'description' => $description,
            'metadata' => $response,
        ];
    }

    /**
     * AWS Rekognition (alternative)
     */
    protected function awsRekognition(string $imagePath, string $type): array
    {
        // Implementation would use AWS SDK
        return $this->mockVisionAPI($imagePath, $type);
    }

    /**
     * Mock vision API for development
     */
    protected function mockVisionAPI(string $imagePath, string $type): array
    {
        return match ($type) {
            'product_detection' => [
                'success' => true,
                'objects' => [
                    ['name' => 'Bottle', 'confidence' => 0.95],
                    ['name' => 'Label', 'confidence' => 0.88],
                ],
                'labels' => [
                    ['name' => 'Product', 'confidence' => 0.97],
                    ['name' => 'Beverage', 'confidence' => 0.92],
                ],
                'confidence' => 0.95,
                'description' => 'Product bottle with label detected',
            ],

            'damage_assessment' => [
                'success' => true,
                'objects' => [
                    ['name' => 'Scratch', 'confidence' => 0.82],
                    ['name' => 'Dent', 'confidence' => 0.75],
                ],
                'labels' => [
                    ['name' => 'Damaged', 'confidence' => 0.85],
                    ['name' => 'Wear', 'confidence' => 0.78],
                ],
                'confidence' => 0.85,
                'description' => 'Minor surface damage detected: scratches and dents',
                'metadata' => [
                    'damage_severity' => 'minor',
                    'affected_area_percentage' => 15,
                    'repair_recommended' => true,
                ],
            ],

            'ocr' => [
                'success' => true,
                'objects' => [],
                'labels' => [['name' => 'Document', 'confidence' => 0.99]],
                'confidence' => 0.99,
                'description' => "INVOICE\nNo: INV-2026-001\nDate: 2026-04-06\nTotal: Rp 1.500.000",
            ],

            default => ['success' => false, 'error' => 'Unknown type']
        };
    }

    /**
     * Verify recognition result (user confirmation)
     */
    public function verifyResult(int $resultId, bool $verified = true): bool
    {
        $result = ImageRecognitionResult::find($resultId);

        if (! $result) {
            return false;
        }

        $result->update(['verified' => $verified]);

        return true;
    }

    /**
     * Get recognition history
     */
    public function getRecognitionHistory(int $tenantId, ?string $type = null, int $limit = 20): array
    {
        $query = ImageRecognitionResult::where('tenant_id', $tenantId);

        if ($type) {
            $query->where('recognition_type', $type);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get recognition statistics
     */
    public function getRecognitionStats(int $tenantId): array
    {
        $total = ImageRecognitionResult::where('tenant_id', $tenantId)->count();
        $verified = ImageRecognitionResult::where('tenant_id', $tenantId)
            ->where('verified', true)
            ->count();

        $byType = ImageRecognitionResult::where('tenant_id', $tenantId)
            ->selectRaw('recognition_type, COUNT(*) as count, AVG(confidence_score) as avg_confidence')
            ->groupBy('recognition_type')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->recognition_type => [
                    'count' => $item->count,
                    'avg_confidence' => round($item->avg_confidence, 4),
                ],
            ])
            ->toArray();

        return [
            'total_analyses' => $total,
            'verified_results' => $verified,
            'verification_rate' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
            'by_type' => $byType,
        ];
    }

    /**
     * Batch process multiple images
     */
    public function batchProcess(array $imagePaths, string $type, int $tenantId, int $userId): array
    {
        $results = [];
        $successCount = 0;
        $failCount = 0;

        foreach ($imagePaths as $imagePath) {
            $result = $this->analyzeImage($imagePath, $tenantId, $userId, $type);

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }

            $results[] = $result;
        }

        return [
            'success' => true,
            'total' => count($imagePaths),
            'succeeded' => $successCount,
            'failed' => $failCount,
            'results' => $results,
        ];
    }
}
