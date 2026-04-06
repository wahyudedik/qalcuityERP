<?php

namespace App\Services\AI;

use App\Models\SentimentAnalysis;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SentimentAnalysisService
{
    /**
     * Analyze sentiment of text
     */
    public function analyzeSentiment(string $text, string $sourceType, $sourceId = null, int $tenantId = null): array
    {
        try {
            // Call NLP API for sentiment analysis
            $result = $this->callNlpAPI($text);

            // Determine if requires attention
            $requiresAttention = $result['sentiment'] === 'negative' && $result['confidence'] > 0.7;

            // Generate response suggestion
            $responseSuggestion = $this->generateResponseSuggestion($result);

            // Save analysis
            $analysis = SentimentAnalysis::create([
                'tenant_id' => $tenantId ?? auth()->user()?->tenant_id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'content' => $text,
                'sentiment' => $result['sentiment'],
                'confidence' => $result['confidence'],
                'polarity' => $result['polarity'],
                'subjectivity' => $result['subjectivity'],
                'emotions' => $result['emotions'],
                'key_phrases' => $result['key_phrases'],
                'topics' => $result['topics'],
                'requires_attention' => $requiresAttention,
                'response_suggestion' => $responseSuggestion,
            ]);

            return [
                'success' => true,
                'analysis_id' => $analysis->id,
                'sentiment' => $result['sentiment'],
                'confidence' => $result['confidence'],
                'emotions' => $result['emotions'],
                'requires_attention' => $requiresAttention,
                'suggested_response' => $responseSuggestion,
            ];

        } catch (\Throwable $e) {
            Log::error('Sentiment analysis failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Call NLP API (Google Cloud NLP, AWS Comprehend, or Azure Text Analytics)
     */
    protected function callNlpAPI(string $text): array
    {
        // Try Google Cloud Natural Language API
        $googleKey = config('services.google.cloud_api_key');

        if ($googleKey) {
            return $this->googleCloudNLP($text, $googleKey);
        }

        // Fallback to mock
        return $this->mockNLPAnalysis($text);
    }

    /**
     * Google Cloud Natural Language API
     */
    protected function googleCloudNLP(string $text, string $apiKey): array
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("https://language.googleapis.com/v1/documents:analyzeSentiment?key={$apiKey}", [
                    'document' => [
                        'type' => 'PLAIN_TEXT',
                        'content' => $text,
                        'language' => 'id', // Indonesian
                    ],
                    'encodingType' => 'UTF8',
                ]);

        if ($response->successful()) {
            $sentiment = $response->json('documentSentiment');

            return [
                'sentiment' => $sentiment['score'] > 0.1 ? 'positive' : ($sentiment['score'] < -0.1 ? 'negative' : 'neutral'),
                'confidence' => abs($sentiment['score']),
                'polarity' => $sentiment['score'],
                'subjectivity' => $sentiment['magnitude'] ?? 0,
                'emotions' => $this->detectEmotions($text),
                'key_phrases' => [],
                'topics' => [],
            ];
        }

        return $this->mockNLPAnalysis($text);
    }

    /**
     * Mock NLP analysis for development
     */
    protected function mockNLPAnalysis(string $text): array
    {
        // Simple keyword-based analysis
        $positiveWords = ['bagus', 'baik', 'puas', 'senang', 'suka', 'mantap', 'oke', 'terima kasih'];
        $negativeWords = ['buruk', 'jelek', 'kecewa', 'marah', 'tidak puas', 'lambat', 'rusak', 'masalah'];

        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (stripos($text, $word) !== false) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (stripos($text, $word) !== false) {
                $negativeCount++;
            }
        }

        $total = $positiveCount + $negativeCount;

        if ($total === 0) {
            $sentiment = 'neutral';
            $polarity = 0;
        } elseif ($positiveCount > $negativeCount) {
            $sentiment = 'positive';
            $polarity = 0.5 + ($positiveCount * 0.1);
        } else {
            $sentiment = 'negative';
            $polarity = -0.5 - ($negativeCount * 0.1);
        }

        return [
            'sentiment' => $sentiment,
            'confidence' => min(0.9, 0.5 + ($total * 0.1)),
            'polarity' => max(-1, min(1, $polarity)),
            'subjectivity' => 0.6,
            'emotions' => $this->detectEmotions($text),
            'key_phrases' => $this->extractKeyPhrases($text),
            'topics' => $this->extractTopics($text),
        ];
    }

    /**
     * Detect emotions in text
     */
    protected function detectEmotions(string $text): array
    {
        $emotions = [
            'joy' => 0,
            'anger' => 0,
            'sadness' => 0,
            'fear' => 0,
            'surprise' => 0,
        ];

        // Simple emotion detection
        if (stripos($text, 'senang') !== false || stripos($text, 'puas') !== false) {
            $emotions['joy'] = 0.8;
        }

        if (stripos($text, 'marah') !== false || stripos($text, 'kecewa') !== false) {
            $emotions['anger'] = 0.7;
        }

        if (stripos($text, 'sedih') !== false || stripos($text, 'kecewa') !== false) {
            $emotions['sadness'] = 0.6;
        }

        return $emotions;
    }

    /**
     * Extract key phrases
     */
    protected function extractKeyPhrases(string $text): array
    {
        // Simple extraction - would use NLP in production
        $words = explode(' ', $text);
        return array_slice(array_unique($words), 0, 5);
    }

    /**
     * Extract topics
     */
    protected function extractTopics(string $text): array
    {
        $topics = [];

        if (stripos($text, 'produk') !== false || stripos($text, 'barang') !== false) {
            $topics[] = 'product';
        }

        if (stripos($text, 'layanan') !== false || stripos($text, 'service') !== false) {
            $topics[] = 'service';
        }

        if (stripos($text, 'harga') !== false || stripos($text, 'biaya') !== false) {
            $topics[] = 'pricing';
        }

        return $topics;
    }

    /**
     * Generate suggested response based on sentiment
     */
    protected function generateResponseSuggestion(array $result): string
    {
        if ($result['sentiment'] === 'negative') {
            return "Kami mohon maaf atas ketidaknyamanan ini. Tim kami akan segera menindaklanjuti masalah Anda. Mohon berikan detail lebih lanjut agar kami dapat membantu dengan lebih baik.";
        } elseif ($result['sentiment'] === 'positive') {
            return "Terima kasih atas feedback positif Anda! Kami senang mendengar kepuasan Anda. Jangan ragu untuk menghubungi kami jika membutuhkan bantuan lebih lanjut.";
        } else {
            return "Terima kasih atas feedback Anda. Kami menghargai masukan Anda untuk perbaikan layanan kami.";
        }
    }

    /**
     * Get pending analyses requiring attention
     */
    public function getPendingAnalyses(int $tenantId, int $limit = 20): array
    {
        return SentimentAnalysis::where('tenant_id', $tenantId)
            ->where('requires_attention', true)
            ->where('status', 'new')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Mark analysis as reviewed
     */
    public function markReviewed(int $analysisId, int $userId): bool
    {
        $analysis = SentimentAnalysis::find($analysisId);

        if (!$analysis) {
            return false;
        }

        $analysis->update([
            'status' => 'reviewed',
            'assigned_to_user_id' => $userId,
        ]);

        return true;
    }

    /**
     * Get sentiment statistics
     */
    public function getSentimentStats(int $tenantId, \Carbon\Carbon $startDate = null, \Carbon\Carbon $endDate = null): array
    {
        $query = SentimentAnalysis::where('tenant_id', $tenantId);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $total = $query->count();

        $bySentiment = $query->clone()
            ->selectRaw('sentiment, COUNT(*) as count')
            ->groupBy('sentiment')
            ->pluck('count', 'sentiment')
            ->toArray();

        $avgPolarity = $query->clone()->avg('polarity');
        $requiresAttention = $query->clone()->where('requires_attention', true)->count();

        $bySource = $query->clone()
            ->selectRaw('source_type, COUNT(*) as count')
            ->groupBy('source_type')
            ->pluck('count', 'source_type')
            ->toArray();

        return [
            'total_analyses' => $total,
            'by_sentiment' => $bySentiment,
            'positive_percentage' => $total > 0 ? round((($bySentiment['positive'] ?? 0) / $total) * 100, 2) : 0,
            'negative_percentage' => $total > 0 ? round((($bySentiment['negative'] ?? 0) / $total) * 100, 2) : 0,
            'neutral_percentage' => $total > 0 ? round((($bySentiment['neutral'] ?? 0) / $total) * 100, 2) : 0,
            'average_polarity' => round($avgPolarity ?? 0, 4),
            'requires_attention' => $requiresAttention,
            'by_source' => $bySource,
        ];
    }

    /**
     * Get sentiment trends over time
     */
    public function getSentimentTrends(int $tenantId, string $period = 'daily'): array
    {
        $format = match ($period) {
            'hourly' => '%Y-%m-%d %H:00',
            'daily' => '%Y-%m-%d',
            'weekly' => '%Y-%W',
            'monthly' => '%Y-%m',
            default => '%Y-%m-%d'
        };

        $trends = SentimentAnalysis::where('tenant_id', $tenantId)
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, sentiment, COUNT(*) as count")
            ->groupBy('period', 'sentiment')
            ->orderBy('period')
            ->get()
            ->groupBy('period')
            ->map(function ($group) {
                return $group->pluck('count', 'sentiment')->toArray();
            })
            ->toArray();

        return [
            'success' => true,
            'period' => $period,
            'trends' => $trends,
        ];
    }
}
