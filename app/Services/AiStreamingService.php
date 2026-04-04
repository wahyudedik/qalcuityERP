<?php

namespace App\Services;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * AiStreamingService
 * 
 * Menyediakan response streaming untuk AI chat agar UX lebih smooth.
 * User dapat melihat respons muncul secara bertahap (typewriter effect).
 */
class AiStreamingService
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Stream AI response dengan Server-Sent Events (SSE)
     * 
     * @param string $message User message
     * @param array $history Chat history
     * @param array $toolDeclarations Available tools
     * @param callable|null $onChunk Callback untuk setiap chunk (untuk testing)
     * @return StreamedResponse SSE response
     */
    public function streamResponse(
        string $message,
        array $history = [],
        array $toolDeclarations = [],
        ?callable $onChunk = null
    ): StreamedResponse {
        return response()->stream(function () use ($message, $history, $toolDeclarations, $onChunk) {
            try {
                // Send initial event
                $this->sendEvent('start', ['message' => 'Processing your request...']);

                // Call Gemini API
                $response = $this->gemini->chatWithTools(
                    message: $message,
                    history: $history,
                    toolDeclarations: $toolDeclarations,
                );

                $text = $response['text'] ?? '';
                $model = $response['model'] ?? 'unknown';
                $functionCalls = $response['function_calls'] ?? [];

                // If there are function calls, execute them
                if (!empty($functionCalls)) {
                    $this->sendEvent('function_calls', [
                        'calls' => $functionCalls,
                        'count' => count($functionCalls),
                    ]);

                    // Note: Function execution should be handled by the caller
                    // This is just to notify the client
                }

                // Stream text in chunks for typewriter effect
                if (!empty($text)) {
                    $words = preg_split('/\s+/', $text);
                    $chunkSize = max(1, intdiv(count($words), 20)); // Split into ~20 chunks
                    $chunks = array_chunk($words, $chunkSize);

                    foreach ($chunks as $index => $chunk) {
                        $chunkText = implode(' ', $chunk);

                        $this->sendEvent('chunk', [
                            'text' => $chunkText,
                            'progress' => round(($index + 1) / count($chunks) * 100, 2),
                            'is_final' => ($index === count($chunks) - 1),
                        ]);

                        // Flush and sleep for smooth streaming
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();

                        // Small delay for typewriter effect (adjust as needed)
                        usleep(50000); // 50ms

                        // Call callback if provided (for testing/logging)
                        if ($onChunk) {
                            $onChunk($chunkText, $index, count($chunks));
                        }
                    }
                }

                // Send final event
                $this->sendEvent('complete', [
                    'full_text' => $text,
                    'model' => $model,
                    'function_calls' => $functionCalls,
                ]);

            } catch (\Throwable $e) {
                Log::error('AiStreamingService: Streaming failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->sendEvent('error', [
                    'message' => 'Terjadi kesalahan saat memproses permintaan Anda.',
                    'details' => app()->isLocal() ? $e->getMessage() : null,
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Send SSE event
     */
    protected function sendEvent(string $event, array $data): void
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        echo "event: {$event}\n";
        echo "data: {$jsonData}\n\n";

        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
    }

    /**
     * Stream simple text response (without tools)
     */
    public function streamSimpleResponse(string $message, array $history = []): StreamedResponse
    {
        return response()->stream(function () use ($message, $history) {
            try {
                $this->sendEvent('start', ['message' => 'Processing...']);

                $response = $this->gemini->chat(
                    message: $message,
                    history: $history,
                );

                $text = $response['text'] ?? '';
                $model = $response['model'] ?? 'unknown';

                // Stream in chunks
                $chunks = str_split($text, 50); // 50 chars per chunk

                foreach ($chunks as $index => $chunk) {
                    $this->sendEvent('chunk', [
                        'text' => $chunk,
                        'progress' => round(($index + 1) / count($chunks) * 100, 2),
                        'is_final' => ($index === count($chunks) - 1),
                    ]);

                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                    usleep(30000); // 30ms
                }

                $this->sendEvent('complete', [
                    'full_text' => $text,
                    'model' => $model,
                ]);

            } catch (\Throwable $e) {
                Log::error('AiStreamingService: Simple streaming failed', [
                    'error' => $e->getMessage(),
                ]);

                $this->sendEvent('error', [
                    'message' => 'Terjadi kesalahan.',
                ]);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
        ]);
    }

    /**
     * Check if client supports streaming
     */
    public static function clientSupportsStreaming(): bool
    {
        $acceptHeader = request()->header('Accept', '');
        return str_contains($acceptHeader, 'text/event-stream');
    }
}
