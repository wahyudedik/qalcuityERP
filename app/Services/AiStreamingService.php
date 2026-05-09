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
 *
 * BUG-AI-005 FIX: Added comprehensive error handling for:
 * - Client disconnect detection
 * - Partial response recovery
 * - Proper cleanup on error
 * - Graceful degradation
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
     * @param  string  $message  User message
     * @param  array  $history  Chat history
     * @param  array  $toolDeclarations  Available tools
     * @param  callable|null  $onChunk  Callback untuk setiap chunk (untuk testing)
     * @return StreamedResponse SSE response
     */
    public function streamResponse(
        string $message,
        array $history = [],
        array $toolDeclarations = [],
        ?callable $onChunk = null,
        ?callable $onComplete = null
    ): StreamedResponse {
        // Track accumulated text for error recovery
        $accumulatedText = '';
        $model = 'unknown';
        $functionCalls = [];

        return response()->stream(function () use ($message, $history, $toolDeclarations, $onChunk, $onComplete, &$accumulatedText, &$model, &$functionCalls) {
            try {
                // BUG-AI-005 FIX: Check if client disconnected before starting
                if (connection_aborted()) {
                    Log::warning('AiStreamingService: Client disconnected before streaming started');

                    return;
                }

                // Send initial event
                $this->sendEvent('start', ['message' => 'Processing your request...']);

                // BUG-AI-005 FIX: Check connection after initial event
                if (connection_aborted()) {
                    Log::warning('AiStreamingService: Client disconnected after start event');

                    return;
                }

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
                if (! empty($functionCalls)) {
                    // BUG-AI-005 FIX: Check connection before sending function calls
                    if (! connection_aborted()) {
                        $this->sendEvent('function_calls', [
                            'calls' => $functionCalls,
                            'count' => count($functionCalls),
                        ]);
                    }
                }

                // Stream text in chunks for typewriter effect
                if (! empty($text)) {
                    $words = preg_split('/\s+/', $text);
                    $chunkSize = max(1, intdiv(count($words), 20)); // Split into ~20 chunks
                    $chunks = array_chunk($words, $chunkSize);

                    foreach ($chunks as $index => $chunk) {
                        // BUG-AI-005 FIX: Check if client disconnected before each chunk
                        if (connection_aborted()) {
                            Log::warning('AiStreamingService: Client disconnected during streaming', [
                                'chunks_sent' => $index,
                                'chunks_total' => count($chunks),
                                'accumulated_text_length' => strlen($accumulatedText),
                            ]);

                            // Send partial complete event with accumulated text
                            $this->sendEvent('partial', [
                                'full_text' => $accumulatedText,
                                'model' => $model,
                                'function_calls' => $functionCalls,
                                'disconnected' => true,
                                'message' => 'Connection lost. Partial response saved.',
                            ]);

                            return;
                        }

                        $chunkText = implode(' ', $chunk);
                        $accumulatedText .= ($accumulatedText ? ' ' : '').$chunkText;

                        $this->sendEvent('chunk', [
                            'text' => $chunkText,
                            'progress' => round(($index + 1) / count($chunks) * 100, 2),
                            'is_final' => ($index === count($chunks) - 1),
                        ]);

                        // Flush and sleep for smooth streaming
                        try {
                            if (ob_get_level() > 0) {
                                ob_flush();
                            }
                            flush();
                        } catch (\Throwable $e) {
                            // BUG-AI-005 FIX: Flush failed, client likely disconnected
                            Log::warning('AiStreamingService: Flush failed, client may have disconnected', [
                                'error' => $e->getMessage(),
                            ]);

                            return;
                        }

                        // Small delay for typewriter effect (adjust as needed)
                        usleep(50000); // 50ms

                        // Call callback if provided (for testing/logging)
                        if ($onChunk) {
                            $onChunk($chunkText, $index, count($chunks));
                        }
                    }
                }

                // BUG-AI-005 FIX: Final connection check before complete event
                if (! connection_aborted()) {
                    // Send final event
                    $this->sendEvent('complete', [
                        'full_text' => $text,
                        'model' => $model,
                        'function_calls' => $functionCalls,
                    ]);

                    // Invoke onComplete callback to persist message to history
                    if ($onComplete) {
                        $onComplete($text, $model);
                    }
                }

            } catch (\Throwable $e) {
                Log::error('AiStreamingService: Streaming failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'accumulated_text_length' => strlen($accumulatedText),
                ]);

                // BUG-AI-005 FIX: Send error event with accumulated text for recovery
                if (! connection_aborted()) {
                    $this->sendEvent('error', [
                        'message' => 'Terjadi kesalahan saat memproses permintaan Anda.',
                        'details' => app()->isLocal() ? $e->getMessage() : null,
                        'accumulated_text' => $accumulatedText, // Allow partial recovery
                        'model' => $model,
                        'function_calls' => $functionCalls,
                    ]);
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no', // Disable nginx buffering
            'Connection' => 'keep-alive',
            // BUG-AI-005 FIX: Add headers for better connection handling
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    /**
     * Send SSE event
     *
     * BUG-AI-005 FIX: Added connection check and error handling
     */
    protected function sendEvent(string $event, array $data): void
    {
        try {
            $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($jsonData === false) {
                Log::error('AiStreamingService: Failed to encode JSON for event', [
                    'event' => $event,
                    'json_error' => json_last_error_msg(),
                ]);

                return;
            }

            echo "event: {$event}\n";
            echo "data: {$jsonData}\n\n";

            if (ob_get_level() > 0) {
                ob_flush();
            }
            flush();
        } catch (\Throwable $e) {
            // BUG-AI-005 FIX: Log send event failures
            Log::warning('AiStreamingService: Failed to send event', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Stream simple text response (without tools)
     *
     * BUG-AI-005 FIX: Added disconnect detection and error handling
     */
    public function streamSimpleResponse(string $message, array $history = []): StreamedResponse
    {
        $accumulatedText = '';
        $model = 'unknown';

        return response()->stream(function () use ($message, $history, &$accumulatedText, &$model) {
            try {
                // BUG-AI-005 FIX: Check connection before starting
                if (connection_aborted()) {
                    Log::warning('AiStreamingService: Client disconnected before simple streaming started');

                    return;
                }

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
                    // BUG-AI-005 FIX: Check connection before each chunk
                    if (connection_aborted()) {
                        Log::warning('AiStreamingService: Client disconnected during simple streaming', [
                            'chunks_sent' => $index,
                            'chunks_total' => count($chunks),
                        ]);

                        // Send partial complete
                        $this->sendEvent('partial', [
                            'full_text' => $accumulatedText,
                            'model' => $model,
                            'disconnected' => true,
                        ]);

                        return;
                    }

                    $accumulatedText .= $chunk;

                    $this->sendEvent('chunk', [
                        'text' => $chunk,
                        'progress' => round(($index + 1) / count($chunks) * 100, 2),
                        'is_final' => ($index === count($chunks) - 1),
                    ]);

                    try {
                        if (ob_get_level() > 0) {
                            ob_flush();
                        }
                        flush();
                    } catch (\Throwable $e) {
                        Log::warning('AiStreamingService: Flush failed in simple streaming', [
                            'error' => $e->getMessage(),
                        ]);

                        return;
                    }

                    usleep(30000); // 30ms
                }

                // BUG-AI-005 FIX: Final connection check
                if (! connection_aborted()) {
                    $this->sendEvent('complete', [
                        'full_text' => $text,
                        'model' => $model,
                    ]);
                }

            } catch (\Throwable $e) {
                Log::error('AiStreamingService: Simple streaming failed', [
                    'error' => $e->getMessage(),
                    'accumulated_text_length' => strlen($accumulatedText),
                ]);

                // BUG-AI-005 FIX: Send error with accumulated text
                if (! connection_aborted()) {
                    $this->sendEvent('error', [
                        'message' => 'Terjadi kesalahan.',
                        'accumulated_text' => $accumulatedText,
                        'model' => $model,
                    ]);
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection' => 'keep-alive',
            'X-Content-Type-Options' => 'nosniff',
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
