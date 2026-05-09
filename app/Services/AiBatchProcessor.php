<?php

namespace App\Services;

use App\Jobs\ProcessAiBatch;
use Illuminate\Support\Facades\Log;

/**
 * AiBatchProcessor
 *
 * Menangani multiple AI requests dalam satu batch untuk mengurangi overhead
 * dan mengoptimalkan penggunaan API calls.
 */
class AiBatchProcessor
{
    protected GeminiService $gemini;

    protected AiResponseCacheService $cacheService;

    /**
     * Maximum batch size untuk menghindari timeout
     */
    protected const MAX_BATCH_SIZE = 10;

    public function __construct(
        GeminiService $gemini,
        AiResponseCacheService $cacheService
    ) {
        $this->gemini = $gemini;
        $this->cacheService = $cacheService;
    }

    /**
     * Process multiple messages in batch
     *
     * @param  array  $messages  Array of ['tenant_id', 'user_id', 'message', 'history']
     * @return array Array of responses
     */
    public function processBatch(array $messages): array
    {
        if (empty($messages)) {
            return [];
        }

        // Limit batch size
        if (count($messages) > self::MAX_BATCH_SIZE) {
            Log::warning('AiBatchProcessor: Batch size exceeded limit, splitting into chunks');

            return $this->processInChunks($messages);
        }

        $responses = [];
        $apiCallsNeeded = [];

        // Step 1: Check cache for each message
        foreach ($messages as $index => $msgData) {
            $cacheKey = $this->cacheService->generateCacheKey(
                $msgData['tenant_id'],
                $msgData['user_id'],
                $msgData['message']
            );

            $cached = $this->cacheService->get($cacheKey);

            if ($cached !== null) {
                $responses[$index] = $cached;
            } else {
                $apiCallsNeeded[$index] = $msgData;
            }
        }

        // Step 2: Process non-cached messages via API
        if (! empty($apiCallsNeeded)) {
            $apiResponses = $this->processApiCalls($apiCallsNeeded);

            foreach ($apiResponses as $index => $response) {
                $responses[$index] = $response;

                // Cache the response
                $msgData = $apiCallsNeeded[$index];
                $cacheKey = $this->cacheService->generateCacheKey(
                    $msgData['tenant_id'],
                    $msgData['user_id'],
                    $msgData['message']
                );
                $this->cacheService->put($cacheKey, $response);
            }
        }

        // Sort responses by original index
        ksort($responses);

        Log::info('AiBatchProcessor: Processed batch', [
            'total' => count($messages),
            'cached' => count($responses) - count($apiCallsNeeded),
            'api_calls' => count($apiCallsNeeded),
        ]);

        return array_values($responses);
    }

    /**
     * Process messages that need API calls
     */
    protected function processApiCalls(array $messages): array
    {
        $responses = [];

        foreach ($messages as $index => $msgData) {
            try {
                // Inject tenant context
                if (isset($msgData['tenant_context'])) {
                    $this->gemini->withTenantContext($msgData['tenant_context']);
                }

                // Set language
                $language = $msgData['language'] ?? 'id';
                $this->gemini->withLanguage($language);

                // Call Gemini API
                $response = $this->gemini->chat(
                    message: $msgData['message'],
                    history: $msgData['history'] ?? []
                );

                $responses[$index] = $response;

            } catch (\Throwable $e) {
                Log::error('AiBatchProcessor: API call failed for message index '.$index, [
                    'error' => $e->getMessage(),
                    'message' => substr($msgData['message'], 0, 100),
                ]);

                $responses[$index] = [
                    'text' => 'Maaf, terjadi kesalahan saat memproses permintaan Anda. Silakan coba lagi.',
                    'model' => 'error',
                    'function_calls' => [],
                ];
            }
        }

        return $responses;
    }

    /**
     * Split large batches into smaller chunks
     */
    protected function processInChunks(array $messages): array
    {
        $chunks = array_chunk($messages, self::MAX_BATCH_SIZE, true);
        $allResponses = [];

        foreach ($chunks as $chunk) {
            $chunkResponses = $this->processBatch($chunk);
            $allResponses = array_merge($allResponses, $chunkResponses);
        }

        return $allResponses;
    }

    /**
     * Async batch processing via queue
     * Berguna untuk operasi background seperti generate recommendations untuk banyak tenant
     */
    public function dispatchBatch(array $messages, string $queue = 'ai'): void
    {
        // Split into chunks if needed
        $chunks = array_chunk($messages, self::MAX_BATCH_SIZE, true);

        foreach ($chunks as $chunkIndex => $chunk) {
            // Dispatch job untuk setiap chunk
            ProcessAiBatch::dispatch($chunk, $chunkIndex)
                ->onQueue($queue)
                ->delay(now()->addSeconds($chunkIndex * 5)); // Stagger jobs
        }

        Log::info('AiBatchProcessor: Dispatched batch jobs', [
            'total_messages' => count($messages),
            'total_chunks' => count($chunks),
        ]);
    }

    /**
     * Get batch processing statistics
     */
    public function getStats(): array
    {
        return [
            'max_batch_size' => self::MAX_BATCH_SIZE,
            'cache_driver' => config('cache.default'),
        ];
    }
}
