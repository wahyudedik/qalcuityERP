<?php

namespace App\Services\AI;

use App\Models\ChatbotConversation;
use App\Models\ChatbotTrainingData;
use Illuminate\Support\Facades\Log;

class ChatbotTrainingService
{
    /**
     * Train chatbot from historical conversations
     */
    public function trainFromHistory(int $tenantId): array
    {
        try {
            // Get all helpful conversations
            $conversations = ChatbotConversation::where('tenant_id', $tenantId)
                ->where('was_helpful', true)
                ->get();

            $trainedCount = 0;

            foreach ($conversations as $conv) {
                // Extract training data
                $trainingData = $this->extractTrainingData($conv);

                // Save to training dataset
                ChatbotTrainingData::create([
                    'tenant_id' => $tenantId,
                    'category' => $trainingData['category'],
                    'question' => $conv->user_message,
                    'answer' => $conv->bot_response,
                    'context' => $conv->context,
                    'keywords' => $trainingData['keywords'],
                    'intents' => $trainingData['intents'],
                    'is_verified' => false,
                ]);

                $trainedCount++;
            }

            return [
                'success' => true,
                'conversations_processed' => $conversations->count(),
                'training_data_created' => $trainedCount,
            ];

        } catch (\Throwable $e) {
            Log::error('Chatbot training failed: '.$e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Add training data manually
     */
    public function addTrainingData(int $tenantId, string $category, string $question, string $answer, array $context = []): bool
    {
        try {
            ChatbotTrainingData::create([
                'tenant_id' => $tenantId,
                'category' => $category,
                'question' => $question,
                'answer' => $answer,
                'context' => $context,
                'keywords' => $this->extractKeywords($question),
                'intents' => $this->detectIntents($question),
                'is_verified' => true,
                'verified_by_user_id' => auth()->id(),
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to add training data: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Find best response for user question
     */
    public function findBestResponse(string $question, int $tenantId): array
    {
        // Search in verified training data
        $candidates = ChatbotTrainingData::where('tenant_id', $tenantId)
            ->where('is_verified', true)
            ->get();

        $bestMatch = null;
        $highestScore = 0;

        foreach ($candidates as $candidate) {
            $score = $this->calculateSimilarity($question, $candidate->question);

            if ($score > $highestScore) {
                $highestScore = $score;
                $bestMatch = $candidate;
            }
        }

        if ($bestMatch && $highestScore > 0.6) {
            // Found good match
            $bestMatch->increment('usage_count');

            return [
                'success' => true,
                'response' => $bestMatch->answer,
                'confidence' => $highestScore,
                'category' => $bestMatch->category,
                'source' => 'training_data',
            ];
        }

        // No good match found - use AI fallback
        return $this->generateAIFallback($question, $tenantId);
    }

    /**
     * Calculate similarity between two texts (cosine similarity)
     */
    protected function calculateSimilarity(string $text1, string $text2): float
    {
        $words1 = $this->tokenize($text1);
        $words2 = $this->tokenize($text2);

        // Simple Jaccard similarity
        $intersection = count(array_intersect($words1, $words2));
        $union = count(array_unique(array_merge($words1, $words2)));

        return $union > 0 ? $intersection / $union : 0;
    }

    /**
     * Tokenize text
     */
    protected function tokenize(string $text): array
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\w\s]/', '', $text);

        return explode(' ', $text);
    }

    /**
     * Extract keywords from text
     */
    protected function extractKeywords(string $text): array
    {
        $stopWords = ['yang', 'dan', 'atau', 'dengan', 'untuk', 'dari', 'pada', 'dalam'];
        $words = $this->tokenize($text);

        return array_values(array_filter($words, function ($word) use ($stopWords) {
            return ! in_array($word, $stopWords) && strlen($word) > 2;
        }));
    }

    /**
     * Detect intents from question
     */
    protected function detectIntents(string $question): array
    {
        $intents = [];

        if (preg_match('/harga|biaya|berapa/i', $question)) {
            $intents[] = 'price_inquiry';
        }

        if (preg_match('/stok|tersedia|ada/i', $question)) {
            $intents[] = 'stock_check';
        }

        if (preg_match('/cara|bagaimana|how/i', $question)) {
            $intents[] = 'how_to';
        }

        if (preg_match('/jam|buka|tutup/i', $question)) {
            $intents[] = 'business_hours';
        }

        return $intents;
    }

    /**
     * Extract training data from conversation
     */
    protected function extractTrainingData(ChatbotConversation $conv): array
    {
        return [
            'category' => $this->categorizeQuestion($conv->user_message),
            'keywords' => $this->extractKeywords($conv->user_message),
            'intents' => $this->detectIntents($conv->user_message),
        ];
    }

    /**
     * Categorize question
     */
    protected function categorizeQuestion(string $question): string
    {
        if (preg_match('/produk|barang|item/i', $question)) {
            return 'product';
        }

        if (preg_match('/invoice|faktur|pembayaran/i', $question)) {
            return 'billing';
        }

        if (preg_match('/stok|inventory|gudang/i', $question)) {
            return 'inventory';
        }

        if (preg_match('/laporan|report|statistik/i', $question)) {
            return 'reports';
        }

        return 'general';
    }

    /**
     * Generate AI fallback response
     */
    protected function generateAIFallback(string $question, int $tenantId): array
    {
        // This would call Gemini/OpenAI API
        // For now, return generic response
        return [
            'success' => true,
            'response' => 'Maaf, saya belum memiliki informasi spesifik tentang pertanyaan Anda. Silakan hubungi customer service kami untuk bantuan lebih lanjut.',
            'confidence' => 0.3,
            'category' => 'general',
            'source' => 'ai_fallback',
        ];
    }

    /**
     * Log conversation for future training
     */
    public function logConversation(int $tenantId, int $userId, string $userMessage, string $botResponse, array $context = []): int
    {
        $conversation = ChatbotConversation::create([
            'tenant_id' => $tenantId,
            'user_id' => $userId,
            'user_message' => $userMessage,
            'bot_response' => $botResponse,
            'context' => $context,
        ]);

        return $conversation->id;
    }

    /**
     * Record user feedback on bot response
     */
    public function recordFeedback(int $conversationId, bool $wasHelpful, string $notes = ''): bool
    {
        $conversation = ChatbotConversation::find($conversationId);

        if (! $conversation) {
            return false;
        }

        $conversation->update([
            'was_helpful' => $wasHelpful,
            'feedback_notes' => $notes,
        ]);

        // If not helpful and has notes, flag for review
        if (! $wasHelpful && $notes) {
            // Could trigger notification to admin
        }

        return true;
    }

    /**
     * Get training statistics
     */
    public function getTrainingStats(int $tenantId): array
    {
        $total = ChatbotTrainingData::where('tenant_id', $tenantId)->count();
        $verified = ChatbotTrainingData::where('tenant_id', $tenantId)
            ->where('is_verified', true)
            ->count();

        $byCategory = ChatbotTrainingData::where('tenant_id', $tenantId)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

        $avgEffectiveness = ChatbotTrainingData::where('tenant_id', $tenantId)
            ->whereNotNull('effectiveness_score')
            ->avg('effectiveness_score');

        $totalConversations = ChatbotConversation::where('tenant_id', $tenantId)->count();
        $helpfulConversations = ChatbotConversation::where('tenant_id', $tenantId)
            ->where('was_helpful', true)
            ->count();

        return [
            'total_training_data' => $total,
            'verified_data' => $verified,
            'verification_rate' => $total > 0 ? round(($verified / $total) * 100, 2) : 0,
            'by_category' => $byCategory,
            'average_effectiveness' => round($avgEffectiveness ?? 0, 4),
            'total_conversations' => $totalConversations,
            'helpful_conversations' => $helpfulConversations,
            'helpfulness_rate' => $totalConversations > 0 ? round(($helpfulConversations / $totalConversations) * 100, 2) : 0,
        ];
    }

    /**
     * Get low-confidence questions for review
     */
    public function getLowConfidenceQuestions(int $tenantId, int $limit = 20): array
    {
        return ChatbotConversation::where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNull('was_helpful')
                    ->orWhere('was_helpful', false);
            })
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Bulk import training data from CSV/JSON
     */
    public function bulkImport(int $tenantId, array $data): array
    {
        $imported = 0;
        $failed = 0;

        foreach ($data as $item) {
            try {
                ChatbotTrainingData::create([
                    'tenant_id' => $tenantId,
                    'category' => $item['category'] ?? 'general',
                    'question' => $item['question'],
                    'answer' => $item['answer'],
                    'keywords' => $this->extractKeywords($item['question']),
                    'intents' => $this->detectIntents($item['question']),
                    'is_verified' => $item['verified'] ?? false,
                ]);
                $imported++;
            } catch (\Throwable $e) {
                $failed++;
                Log::warning('Failed to import training data: '.$e->getMessage());
            }
        }

        return [
            'success' => true,
            'imported' => $imported,
            'failed' => $failed,
            'total' => count($data),
        ];
    }
}
