<?php

namespace App\Services;

use App\Models\PestDetection;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PestDetectionService
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Analyze plant photo for pests/diseases using AI
     */
    public function analyzePhoto($imageFile, int $tenantId, ?int $cropCycleId = null): array
    {
        try {
            // Save image
            $path = $imageFile->store('pest-detections', 'public');
            $imageUrl = Storage::url($path);

            // Read and encode image
            $imageData = base64_encode(file_get_contents($imageFile->getRealPath()));
            $mimeType = $imageFile->getMimeType();

            // Call Gemini Vision API
            $analysis = $this->callGeminiVision($imageData, $mimeType);

            // Parse results
            $result = $this->parseAnalysisResult($analysis);

            // Save to database
            $detection = PestDetection::create([
                'tenant_id' => $tenantId,
                'crop_cycle_id' => $cropCycleId,
                'image_path' => $path,
                'pest_name' => $result['pest_name'],
                'disease_name' => $result['disease_name'],
                'confidence_score' => $result['confidence'],
                'severity' => $result['severity'],
                'pest_detected' => $result['pest_detected'],
                'disease_detected' => $result['disease_detected'],
                'treatment_recommendations' => $result['treatments'],
                'prevention_tips' => $result['prevention'],
                'ai_analysis' => $analysis,
                'status' => 'pending',
            ]);

            return [
                'success' => true,
                'detection_id' => $detection->id,
                'image_url' => $imageUrl,
                ...$result,
            ];

        } catch (\Throwable $e) {
            Log::error('PestDetectionService::analyzePhoto failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'Failed to analyze image',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Call Gemini Vision API for image analysis
     */
    protected function callGeminiVision(string $imageData, string $mimeType): string
    {
        $prompt = <<<'PROMPT'
Analyze this plant/leaf image for pests and diseases. Provide:

1. PEST DETECTION: Identify any visible pests (insects, mites, etc.)
2. DISEASE DETECTION: Identify any plant diseases (fungal, bacterial, viral)
3. SEVERITY: Rate as low/medium/high/critical
4. CONFIDENCE: Your confidence level (0-100%)
5. TREATMENT: Specific treatment recommendations
6. PREVENTION: Prevention tips for future

Format response as JSON:
{
  "pest_detected": true/false,
  "pest_name": "name or null",
  "disease_detected": true/false,
  "disease_name": "name or null",
  "confidence": 0-100,
  "severity": "low/medium/high/critical",
  "treatments": ["treatment 1", "treatment 2"],
  "prevention": ["tip 1", "tip 2"]
}
PROMPT;

        try {
            $response = $this->gemini->generateWithImage($prompt, $imageData, $mimeType);
            return $response['text'] ?? '';
        } catch (\Throwable $e) {
            Log::error('Gemini Vision API call failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Parse AI analysis result
     */
    protected function parseAnalysisResult(string $analysis): array
    {
        // Try to extract JSON from response
        preg_match('/\{.*\}/s', $analysis, $matches);

        if (!empty($matches)) {
            try {
                $data = json_decode($matches[0], true);

                return [
                    'pest_detected' => $data['pest_detected'] ?? false,
                    'pest_name' => $data['pest_name'] ?? null,
                    'disease_detected' => $data['disease_detected'] ?? false,
                    'disease_name' => $data['disease_name'] ?? null,
                    'confidence' => $data['confidence'] ?? 0,
                    'severity' => $data['severity'] ?? 'unknown',
                    'treatments' => $data['treatments'] ?? [],
                    'prevention' => $data['prevention'] ?? [],
                ];
            } catch (\Throwable $e) {
                Log::warning('Failed to parse AI result as JSON');
            }
        }

        // Fallback: simple text parsing
        return [
            'pest_detected' => stripos($analysis, 'pest') !== false,
            'pest_name' => null,
            'disease_detected' => stripos($analysis, 'disease') !== false,
            'disease_name' => null,
            'confidence' => 50,
            'severity' => 'medium',
            'treatments' => ['Consult with agricultural expert'],
            'prevention' => ['Regular monitoring recommended'],
        ];
    }

    /**
     * Get detection history
     */
    public function getHistory(int $tenantId, ?int $cropCycleId = null, int $limit = 50): array
    {
        $query = PestDetection::where('tenant_id', $tenantId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($cropCycleId) {
            $query->where('crop_cycle_id', $cropCycleId);
        }

        return $query->get()->toArray();
    }

    /**
     * Get statistics
     */
    public function getStatistics(int $tenantId): array
    {
        $total = PestDetection::where('tenant_id', $tenantId)->count();
        $pending = PestDetection::where('tenant_id', $tenantId)->where('status', 'pending')->count();
        $treated = PestDetection::where('tenant_id', $tenantId)->where('status', 'treated')->count();
        $resolved = PestDetection::where('tenant_id', $tenantId)->where('status', 'resolved')->count();

        $bySeverity = PestDetection::where('tenant_id', $tenantId)
            ->selectRaw('severity, COUNT(*) as count')
            ->groupBy('severity')
            ->pluck('count', 'severity')
            ->toArray();

        return [
            'total' => $total,
            'pending' => $pending,
            'treated' => $treated,
            'resolved' => $resolved,
            'by_severity' => $bySeverity,
        ];
    }
}
