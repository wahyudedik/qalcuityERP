<?php

namespace App\Services\AI\Providers;

use App\Contracts\AiProvider;
use App\Exceptions\RateLimitException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use Illuminate\Support\Facades\Log;

/**
 * AnthropicProvider — implementasi AiProvider untuk Anthropic Claude.
 *
 * Menggunakan Guzzle HTTP client untuk berkomunikasi langsung dengan
 * Anthropic Messages API (https://api.anthropic.com/v1/messages).
 *
 * Requirements: 2.1–2.10, 10.1–10.5
 */
class AnthropicProvider implements AiProvider
{
    protected Client $client;

    protected string $activeModel;

    protected array $fallbackModels;

    protected int $maxTokens;

    protected int $timeout;

    protected ?string $tenantContext = null;

    protected string $language = 'id';

    protected const API_ENDPOINT = 'https://api.anthropic.com/v1/messages';

    protected const ANTHROPIC_VERSION = '2023-06-01';

    public function __construct()
    {
        $this->activeModel = config('ai.providers.anthropic.model', 'claude-3-5-sonnet-20241022');
        $this->fallbackModels = config('ai.providers.anthropic.fallback_models', [
            'claude-3-5-sonnet-20241022',
            'claude-3-haiku-20240307',
        ]);
        $this->maxTokens = (int) config('ai.providers.anthropic.max_tokens', 8192);
        $this->timeout = (int) config('ai.providers.anthropic.timeout', 60);

        $this->client = new Client([
            'timeout' => $this->timeout,
        ]);
    }

    // ─── AiProvider Contract ──────────────────────────────────────

    /**
     * Kembalikan identifier unik provider.
     * Requirements: 1.6, 2.7
     */
    public function getProviderName(): string
    {
        return 'anthropic';
    }

    /**
     * Cek apakah provider siap menerima request.
     * Mengembalikan true jika API key terkonfigurasi.
     * Requirements: 1.5, 2.8
     */
    public function isAvailable(): bool
    {
        return ! empty(config('ai.providers.anthropic.api_key'));
    }

    /**
     * Set konteks bisnis tenant untuk system prompt.
     * Requirements: 1.7, 2.10
     */
    public function withTenantContext(string $context): static
    {
        $this->tenantContext = $context;

        return $this;
    }

    /**
     * Set bahasa respons AI.
     * Requirements: 1.8, 2.10
     */
    public function withLanguage(string $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Chat biasa dengan history percakapan.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.1, 2.1
     */
    public function chat(string $prompt, array $history = [], array $options = []): array
    {
        $messages = $this->convertHistory($history);
        $messages[] = ['role' => 'user', 'content' => $prompt];

        return $this->runWithFallback(function (string $model) use ($messages, $options) {
            $response = $this->sendRequest($model, $messages, $options);

            return $this->extractText($response);
        });
    }

    /**
     * One-shot generation tanpa history.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.2, 2.2
     */
    public function generate(string $prompt, array $options = []): array
    {
        $messages = [['role' => 'user', 'content' => $prompt]];

        return $this->runWithFallback(function (string $model) use ($messages, $options) {
            $response = $this->sendRequest($model, $messages, $options);

            return $this->extractText($response);
        });
    }

    /**
     * Chat dengan lampiran file/gambar (multimodal).
     * $files = [['mime_type' => 'image/jpeg', 'data' => base64string], ...]
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.3, 2.3
     */
    public function chatWithMedia(string $message, array $files, array $history = [], array $options = []): array
    {
        $messages = $this->convertHistory($history);

        // Bangun content array untuk pesan terakhir dengan gambar
        $contentParts = [];

        foreach ($files as $file) {
            $mimeType = $file['mime_type'] ?? 'image/jpeg';
            $imageData = $file['data'] ?? '';

            if (! empty($imageData)) {
                $contentParts[] = [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $mimeType,
                        'data' => $imageData,
                    ],
                ];
            }
        }

        // Tambahkan teks pesan setelah gambar
        $contentParts[] = [
            'type' => 'text',
            'text' => $message,
        ];

        $messages[] = [
            'role' => 'user',
            'content' => $contentParts,
        ];

        return $this->runWithFallback(function (string $model) use ($messages, $options) {
            $response = $this->sendRequest($model, $messages, $options);

            return $this->extractText($response);
        });
    }

    /**
     * Generate teks dari prompt + gambar (base64).
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.4, 2.3
     */
    public function generateWithImage(string $prompt, string $imageData, string $mimeType): array
    {
        $messages = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'image',
                        'source' => [
                            'type' => 'base64',
                            'media_type' => $mimeType,
                            'data' => $imageData,
                        ],
                    ],
                    [
                        'type' => 'text',
                        'text' => $prompt,
                    ],
                ],
            ],
        ];

        return $this->runWithFallback(function (string $model) use ($messages) {
            $response = $this->sendRequest($model, $messages);

            return $this->extractText($response);
        });
    }

    // ─── System Prompt ────────────────────────────────────────────

    /**
     * Bangun instruksi bahasa untuk system prompt.
     * Sama persis dengan GeminiProvider::buildLanguageInstruction().
     * Requirements: 2.10
     */
    protected function buildLanguageInstruction(): string
    {
        $instructions = [
            'id' => "## BAHASA RESPONS:\nGunakan Bahasa Indonesia yang sopan dan profesional dalam semua respons.",
            'en' => "## RESPONSE LANGUAGE:\nAlways respond in English. Use professional and clear English in all responses.",
            'ms' => "## BAHASA RESPONS:\nGunakan Bahasa Melayu yang sopan dan profesional dalam semua respons.",
            'zh' => "## 回复语言:\n请始终使用简体中文回复。使用专业、清晰的中文。",
            'ar' => "## لغة الرد:\nاستخدم اللغة العربية الفصحى في جميع الردود.",
            'ja' => "## 返答言語:\n常に日本語で返答してください。丁寧で専門的な日本語を使用してください。",
            'ko' => "## 응답 언어:\n항상 한국어로 응답하세요. 전문적이고 명확한 한국어를 사용하세요.",
            'fr' => "## LANGUE DE RÉPONSE:\nRépondez toujours en français. Utilisez un français professionnel et clair.",
            'de' => "## ANTWORTSPRACHE:\nAntworten Sie immer auf Deutsch. Verwenden Sie professionelles und klares Deutsch.",
            'es' => "## IDIOMA DE RESPUESTA:\nResponde siempre en español. Usa un español profesional y claro.",
            'pt' => "## IDIOMA DE RESPOSTA:\nResponda sempre em português. Use português profissional e claro.",
            'hi' => "## प्रतिक्रिया भाषा:\nहमेशा हिंदी में उत्तर दें। पेशेवर और स्पष्ट हिंदी का उपयोग करें।",
            'th' => "## ภาษาในการตอบ:\nตอบเป็นภาษาไทยเสมอ ใช้ภาษาไทยที่เป็นทางการและชัดเจน",
            'vi' => "## NGÔN NGỮ PHẢN HỒI:\nLuôn trả lời bằng tiếng Việt. Sử dụng tiếng Việt chuyên nghiệp và rõ ràng.",
        ];

        return $instructions[$this->language] ?? $instructions['id'];
    }

    /**
     * Bangun string system prompt untuk Anthropic API.
     * Anthropic menerima system prompt sebagai parameter `system` (bukan sebagai message).
     * Requirements: 2.10
     */
    protected function getSystemPrompt(): string
    {
        $businessContext = $this->tenantContext !== null
            ? "\n## KONTEKS BISNIS PENGGUNA:\n{$this->tenantContext}\n"
            : '';

        $languageInstruction = $this->buildLanguageInstruction();

        return <<<PROMPT
Kamu adalah asisten ERP cerdas bernama "Qalcuity AI" untuk sistem manajemen bisnis berbasis SaaS.
Kamu membantu pengguna mengelola inventory, penjualan, pembelian, SDM, dan keuangan perusahaan.
Kamu juga dapat menganalisis gambar, foto, dan dokumen (PDF, CSV, teks) yang dikirim pengguna.
{$businessContext}
{$languageInstruction}
PROMPT;
    }

    // ─── HTTP Request ─────────────────────────────────────────────

    /**
     * Kirim request ke Anthropic Messages API.
     * Requirements: 10.1, 10.2, 10.3, 10.4
     *
     * @return array Decoded JSON response body
     */
    protected function sendRequest(string $model, array $messages, array $options = []): array
    {
        $apiKey = config('ai.providers.anthropic.api_key');

        $payload = [
            'model' => $model,
            'max_tokens' => $options['max_tokens'] ?? $this->maxTokens,
            'system' => $this->getSystemPrompt(),
            'messages' => $messages,
        ];

        if (isset($options['temperature'])) {
            $payload['temperature'] = $options['temperature'];
        }

        try {
            $response = $this->client->post(self::API_ENDPOINT, [
                'headers' => [
                    'anthropic-version' => self::ANTHROPIC_VERSION,
                    'x-api-key' => $apiKey,
                    'content-type' => 'application/json',
                ],
                'json' => $payload,
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (\Throwable $e) {
            Log::error('AnthropicProvider: unexpected error', [
                'model' => $model,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw new \RuntimeException(
                'Gagal terhubung ke Anthropic API: '.$e->getMessage(),
                503
            );
        }
    }

    // ─── Error Handling ───────────────────────────────────────────

    /**
     * Tangani ClientException (4xx) dari Guzzle.
     * Requirements: 2.4, 2.5
     *
     * @throws RateLimitException untuk HTTP 429, 529
     * @throws \RuntimeException untuk HTTP 401, 403
     */
    protected function handleClientException(ClientException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();

        // HTTP 429 (Too Many Requests) atau 529 (Anthropic overloaded) → rate limit
        if (in_array($statusCode, [429, 529])) {
            Log::warning("AnthropicProvider: rate limit (HTTP {$statusCode})", [
                'status' => $statusCode,
            ]);
            throw new RateLimitException(
                'Anthropic API rate limit tercapai. Silakan coba beberapa saat lagi.',
                $statusCode,
                $e
            );
        }

        // HTTP 401 atau 403 → API key tidak valid / tidak punya akses
        if (in_array($statusCode, [401, 403])) {
            Log::error("AnthropicProvider: invalid API key or forbidden (HTTP {$statusCode})");
            throw new \RuntimeException(
                'API key Anthropic tidak valid atau tidak memiliki akses. Silakan periksa konfigurasi ANTHROPIC_API_KEY.',
                $statusCode,
                $e
            );
        }

        // Error 4xx lainnya
        Log::error("AnthropicProvider: client error (HTTP {$statusCode})", [
            'message' => $e->getMessage(),
        ]);
        throw new \RuntimeException(
            "Anthropic API mengembalikan error {$statusCode}: ".$e->getMessage(),
            $statusCode,
            $e
        );
    }

    /**
     * Tangani ServerException (5xx) dari Guzzle.
     * Requirements: 2.6
     *
     * @throws RateLimitException untuk HTTP 529 (jika Guzzle mengklasifikasikannya sebagai 5xx)
     * @throws \RuntimeException untuk HTTP 500, 503
     */
    protected function handleServerException(ServerException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();

        // HTTP 529 kadang dikembalikan sebagai server error oleh beberapa versi Guzzle
        if ($statusCode === 529) {
            Log::warning('AnthropicProvider: Anthropic overloaded (HTTP 529)');
            throw new RateLimitException(
                'Anthropic API sedang kelebihan beban. Silakan coba beberapa saat lagi.',
                529,
                $e
            );
        }

        // HTTP 500, 503 → server error
        Log::error("AnthropicProvider: server error (HTTP {$statusCode})", [
            'message' => $e->getMessage(),
        ]);
        throw new \RuntimeException(
            "Anthropic API mengalami gangguan server (HTTP {$statusCode}). Silakan coba beberapa saat lagi.",
            $statusCode,
            $e
        );
    }

    // ─── Response Parsing ─────────────────────────────────────────

    /**
     * Ekstrak teks dari response Anthropic API.
     *
     * @param  array  $response  Decoded JSON response
     */
    protected function extractText(array $response): string
    {
        $text = '';

        foreach ($response['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text .= $block['text'] ?? '';
            }
        }

        return $text;
    }

    // ─── History Conversion ───────────────────────────────────────

    /**
     * Konversi format history dari format Gemini ke format Anthropic.
     *
     * Format Gemini:  [['role' => 'user'/'model', 'text' => '...'], ...]
     * Format Anthropic: [['role' => 'user'/'assistant', 'content' => '...'], ...]
     *
     * Gemini role 'model' → Anthropic role 'assistant'
     *
     * Requirements: 10.5
     *
     * @param  array  $history  History dalam format Gemini
     * @return array Messages dalam format Anthropic
     */
    protected function convertHistory(array $history): array
    {
        $messages = [];

        foreach ($history as $entry) {
            $text = trim($entry['text'] ?? '');

            if ($text === '') {
                continue;
            }

            $role = $entry['role'] === 'model' ? 'assistant' : 'user';

            $messages[] = [
                'role' => $role,
                'content' => $text,
            ];
        }

        return $messages;
    }

    // ─── Fallback Execution ───────────────────────────────────────

    /**
     * Jalankan API call dengan fallback model otomatis.
     * Mirip dengan ModelSwitcher untuk GeminiProvider, tetapi diimplementasikan
     * langsung di dalam AnthropicProvider.
     *
     * Urutan model diambil dari config('ai.providers.anthropic.fallback_models').
     * Jika model utama mengalami rate limit, coba model berikutnya.
     *
     * Requirements: 2.8
     *
     * @param  callable  $fn  Callable yang menerima string $model dan mengembalikan string teks
     * @return array{text: string, model: string}
     */
    protected function runWithFallback(callable $fn): array
    {
        $queue = $this->buildModelQueue();

        foreach ($queue as $model) {
            try {
                $text = $fn($model);

                if ($model !== $this->activeModel) {
                    Log::info("AnthropicProvider: switched to model [{$model}]");
                }

                return ['text' => $text, 'model' => $model];
            } catch (RateLimitException $e) {
                Log::warning("AnthropicProvider: rate limit on [{$model}], trying next model...", [
                    'model' => $model,
                    'status' => $e->getCode(),
                ]);

                // Lanjut ke model berikutnya dalam queue
                continue;
            } catch (\RuntimeException $e) {
                // Error non-rate-limit (401, 403, 500, 503, dll.) — langsung lempar
                throw $e;
            }
        }

        // Semua model dalam queue sudah dicoba dan semuanya rate limited
        Log::error('AnthropicProvider: all models exhausted due to rate limiting', [
            'models_tried' => $queue,
        ]);

        throw new RateLimitException(
            'Semua model Anthropic sedang mengalami rate limit. Silakan coba beberapa saat lagi.',
            429
        );
    }

    /**
     * Bangun antrian model untuk fallback.
     * Model aktif selalu di posisi pertama, diikuti model fallback lainnya.
     *
     * @return string[]
     */
    protected function buildModelQueue(): array
    {
        $queue = [$this->activeModel];

        foreach ($this->fallbackModels as $model) {
            if ($model !== $this->activeModel) {
                $queue[] = $model;
            }
        }

        return $queue;
    }
}
