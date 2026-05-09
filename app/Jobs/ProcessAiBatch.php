<?php

namespace App\Jobs;

use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessAiBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $timeout = 180;

    public function __construct(
        public readonly array $messages,
        public readonly int $chunkIndex = 0
    ) {
        $this->queue = 'ai';
    }

    public function handle(GeminiService $gemini): void
    {
        Log::info('ProcessAiBatch: Processing chunk', [
            'chunk_index' => $this->chunkIndex,
            'message_count' => count($this->messages),
        ]);

        foreach ($this->messages as $index => $msgData) {
            try {
                // Inject tenant context
                if (isset($msgData['tenant_context'])) {
                    $gemini->withTenantContext($msgData['tenant_context']);
                }

                // Set language
                $language = $msgData['language'] ?? 'id';
                $gemini->withLanguage($language);

                // Call Gemini API
                $response = $gemini->chat(
                    message: $msgData['message'],
                    history: $msgData['history'] ?? []
                );

                // Here you would typically store the response or trigger events
                // For now, just log success
                Log::info('ProcessAiBatch: Message processed', [
                    'chunk_index' => $this->chunkIndex,
                    'message_index' => $index,
                    'model' => $response['model'] ?? 'unknown',
                ]);

            } catch (\Throwable $e) {
                Log::error('ProcessAiBatch: Failed to process message', [
                    'chunk_index' => $this->chunkIndex,
                    'message_index' => $index,
                    'error' => $e->getMessage(),
                ]);

                // Continue with next message instead of failing entire batch
                continue;
            }
        }

        Log::info('ProcessAiBatch: Chunk completed', [
            'chunk_index' => $this->chunkIndex,
        ]);
    }
}
