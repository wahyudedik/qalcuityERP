<?php

namespace App\Services\AI\Providers;

use App\Contracts\AiProvider;
use App\Exceptions\AllModelsUnavailableException;
use App\Services\AI\ModelSwitcher;
use Gemini\Client;
use Gemini\Data\Blob;
use Gemini\Data\Content;
use Gemini\Data\FunctionCall;
use Gemini\Data\FunctionDeclaration;
use Gemini\Data\FunctionResponse;
use Gemini\Data\Part;
use Gemini\Data\Schema;
use Gemini\Data\Tool;
use Gemini\Enums\DataType;
use Gemini\Enums\MimeType;
use Gemini\Enums\Role;
use Illuminate\Support\Facades\Log;

/**
 * GeminiProvider — implementasi AiProvider untuk Google Gemini.
 *
 * Mempertahankan seluruh logika dari GeminiService yang sudah ada,
 * termasuk function calling, vision, ModelSwitcher, dan system prompt.
 *
 * Requirements: 1.9, 6.1
 */
class GeminiProvider implements AiProvider
{
    protected Client $client;
    protected array $models;
    protected array $rateLimitCodes;
    protected string $activeModel;
    protected ?string $tenantContext = null;
    protected string $language = 'id';
    protected ModelSwitcher $switcher;

    public function __construct(?ModelSwitcher $switcher = null)
    {
        $apiKey = config('ai.providers.gemini.api_key') ?? config('gemini.api_key');

        if (empty($apiKey)) {
            $message = 'AI Assistant tidak dikonfigurasi. Silakan hubungi administrator untuk mengatur AI Service.';
            Log::error('GeminiProvider: ' . $message);
            throw new \RuntimeException($message, 500);
        }

        try {
            $this->client = \Gemini::factory()->withApiKey($apiKey)->make();
        } catch (\Throwable $e) {
            $message = 'Konfigurasi AI Assistant tidak valid. Silakan hubungi administrator untuk memeriksa pengaturan AI Service.';
            Log::error('GeminiProvider: ' . $message, ['error' => $e->getMessage()]);
            throw new \RuntimeException($message, 500);
        }

        $this->models = config('ai.providers.gemini.fallback_models') ?? config('gemini.fallback_models');
        $this->rateLimitCodes = config('gemini.rate_limit_codes', [429, 503, 500]);
        $this->activeModel = config('ai.providers.gemini.model') ?? config('gemini.model');

        if (empty($this->models)) {
            Log::warning('GeminiProvider: No fallback models configured. Using default model only.');
            $this->models = [$this->activeModel];
        }

        $this->switcher = $switcher ?? app(ModelSwitcher::class);
    }

    // ─── AiProvider Contract ──────────────────────────────────────

    /**
     * Kembalikan identifier unik provider.
     * Requirements: 1.6
     */
    public function getProviderName(): string
    {
        return 'gemini';
    }

    /**
     * Cek apakah provider siap menerima request.
     * Mengembalikan true jika API key terkonfigurasi.
     * Requirements: 1.5
     */
    public function isAvailable(): bool
    {
        $apiKey = config('ai.providers.gemini.api_key') ?? config('gemini.api_key');
        return !empty($apiKey);
    }

    /**
     * Set konteks bisnis tenant untuk system prompt.
     * Requirements: 1.7
     */
    public function withTenantContext(string $context): static
    {
        $this->tenantContext = $context;
        return $this;
    }

    /**
     * Set bahasa respons AI.
     * Requirements: 1.8
     */
    public function withLanguage(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Chat biasa dengan history.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.1
     */
    public function chat(string $prompt, array $history = [], array $options = []): array
    {
        $contents = $this->buildHistory($history);

        return $this->callWithFallback(function (string $model) use ($prompt, $contents) {
            $response = $this->client
                ->generativeModel($model)
                ->withSystemInstruction($this->getSystemInstruction())
                ->startChat(history: $contents)
                ->sendMessage($prompt);

            return $response->text();
        });
    }

    /**
     * One-shot generation tanpa history.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.2
     */
    public function generate(string $prompt, array $options = []): array
    {
        return $this->runWithFallback(function (string $model) use ($prompt) {
            $response = $this->client
                ->generativeModel($model)
                ->generateContent($prompt);

            return ['text' => $this->extractText($response), '_raw' => true];
        });
    }

    /**
     * Chat dengan file/gambar (multimodal).
     * $files = [['mime_type' => 'image/jpeg', 'data' => base64string], ...]
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.3
     */
    public function chatWithMedia(string $message, array $files, array $history = [], array $options = []): array
    {
        $toolDeclarations = $options['tools'] ?? [];
        $contents = $this->buildHistory($history);

        return $this->runWithFallback(function (string $model) use ($message, $files, $contents, $toolDeclarations) {
            $parts = [];

            foreach ($files as $file) {
                $mimeType = $this->resolveMimeType($file['mime_type']);
                $parts[] = new Part(
                    inlineData: new Blob(
                        mimeType: $mimeType,
                        data: $file['data'],
                    )
                );
            }

            $parts[] = new Part(text: $message);

            $userContent = new Content(parts: $parts, role: Role::USER);
            $allContents = [...$contents, $userContent];

            $modelBuilder = $this->client
                ->generativeModel($model)
                ->withSystemInstruction($this->getSystemInstruction());

            if (!empty($toolDeclarations)) {
                $modelBuilder = $modelBuilder->withTool($this->buildTool($toolDeclarations));
            }

            $response = $modelBuilder->generateContent(...$allContents);

            $functionCalls = [];
            $text = '';
            foreach ($response->candidates as $candidate) {
                foreach ($candidate->content->parts as $part) {
                    if ($part->functionCall !== null) {
                        $functionCalls[] = [
                            'name' => $part->functionCall->name,
                            'args' => (array) ($part->functionCall->args ?? []),
                        ];
                    }
                    if ($part->text !== null) {
                        $text .= $part->text;
                    }
                }
            }

            return ['text' => $text, 'function_calls' => $functionCalls, '_raw' => true];
        });
    }

    /**
     * Generate teks dari prompt + gambar (base64).
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.4
     */
    public function generateWithImage(string $prompt, string $imageData, string $mimeType): array
    {
        return $this->runWithFallback(function (string $model) use ($prompt, $imageData, $mimeType) {
            $resolvedMime = $this->resolveMimeType($mimeType);

            $imagePart = new Part(
                inlineData: new Blob(
                    mimeType: $resolvedMime,
                    data: $imageData,
                )
            );
            $textPart = new Part(text: $prompt);

            $userContent = new Content(parts: [$imagePart, $textPart], role: Role::USER);

            $response = $this->client
                ->generativeModel($model)
                ->withSystemInstruction($this->getSystemInstruction())
                ->generateContent($userContent);

            return ['text' => $this->extractText($response), '_raw' => true];
        });
    }

    // ─── Extended Methods (beyond AiProvider contract) ────────────

    /**
     * Chat dengan function calling tools.
     * Return: ['text' => string, 'model' => string, 'function_calls' => array]
     */
    public function chatWithTools(string $message, array $history, array $toolDeclarations): array
    {
        $contents = $this->buildHistory($history);
        $tool = $this->buildTool($toolDeclarations);

        return $this->callWithFallback(function (string $model) use ($message, $contents, $tool) {
            $modelBuilder = $this->client
                ->generativeModel($model)
                ->withSystemInstruction($this->getSystemInstruction())
                ->withTool($tool);

            if (empty($contents)) {
                $userContent = Content::parse(part: $message, role: Role::USER);
                $response = $modelBuilder->generateContent($userContent);
            } else {
                $response = $modelBuilder
                    ->startChat(history: $contents)
                    ->sendMessage($message);
            }

            $functionCalls = [];
            $text = '';

            foreach ($response->candidates as $candidate) {
                foreach ($candidate->content->parts as $part) {
                    if ($part->functionCall !== null) {
                        $functionCalls[] = [
                            'name' => $part->functionCall->name,
                            'args' => (array) ($part->functionCall->args ?? []),
                        ];
                    }
                    if ($part->text !== null) {
                        $text .= $part->text;
                    }
                }
            }

            return ['text' => $text, 'function_calls' => $functionCalls, '_raw' => true];
        });
    }

    /**
     * Kirim hasil eksekusi function kembali ke Gemini.
     * Return: ['text' => string, 'model' => string]
     */
    public function sendFunctionResults(
        string $originalMessage,
        array $history,
        array $toolDeclarations,
        array $functionResults
    ): array {
        $tool = $this->buildTool($toolDeclarations);

        $contents = $this->buildHistory($history);
        $contents[] = Content::parse(part: $originalMessage, role: Role::USER);

        $callParts = array_map(
            fn($r) => new Part(
                functionCall: new FunctionCall(
                    name: $r['name'],
                    args: $r['data']['_args'] ?? [],
                )
            ),
            $functionResults
        );
        $contents[] = new Content(parts: $callParts, role: Role::MODEL);

        $responseParts = array_map(
            fn($r) => new Part(
                functionResponse: new FunctionResponse(
                    name: $r['name'],
                    response: array_diff_key($r['data'], ['_args' => null]),
                )
            ),
            $functionResults
        );
        $contents[] = new Content(parts: $responseParts, role: Role::USER);

        return $this->runWithFallback(function (string $model) use ($contents, $tool) {
            $response = $this->client
                ->generativeModel($model)
                ->withSystemInstruction($this->getSystemInstruction())
                ->withTool($tool)
                ->generateContent(...$contents);

            return ['text' => $this->extractText($response), '_raw' => true];
        });
    }

    public function getActiveModel(): string
    {
        return $this->activeModel;
    }

    public function setModel(string $model): static
    {
        $this->activeModel = $model;
        return $this;
    }

    // ─── System Prompt ────────────────────────────────────────────

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

    protected function getSystemInstruction(): Content
    {
        $businessContext = $this->tenantContext
            ? "\n## KONTEKS BISNIS PENGGUNA:\n{$this->tenantContext}\n"
            : '';

        $languageInstruction = $this->buildLanguageInstruction();

        $systemPrompt = <<<PROMPT
Kamu adalah asisten ERP cerdas bernama "Qalcuity AI" untuk sistem manajemen bisnis berbasis SaaS.
Kamu membantu pengguna mengelola inventory, penjualan, pembelian, SDM, dan keuangan perusahaan.
Kamu juga dapat menganalisis gambar, foto, dan dokumen (PDF, CSV, teks) yang dikirim pengguna.
{$businessContext}
{$languageInstruction}
PROMPT;

        return Content::parse(
            part: $systemPrompt,
            role: Role::USER,
        );
    }

    // ─── Internal Helpers ─────────────────────────────────────────

    protected function extractText($response): string
    {
        $text = '';
        try {
            foreach ($response->candidates as $candidate) {
                foreach ($candidate->content->parts as $part) {
                    if ($part->text !== null) {
                        $text .= $part->text;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("GeminiProvider: failed to iterate response candidates: {$e->getMessage()}");
            try {
                $text = $response->text() ?? '';
            } catch (\Throwable $e) {
                Log::warning("GeminiProvider: response parsing failed: {$e->getMessage()}");
                $text = '';
            }
        }
        return $text;
    }

    protected function resolveMimeType(string $mimeType): MimeType
    {
        return match (strtolower($mimeType)) {
            'image/jpeg', 'image/jpg' => MimeType::IMAGE_JPEG,
            'image/png'               => MimeType::IMAGE_PNG,
            'image/webp'              => MimeType::IMAGE_WEBP,
            'image/heic'              => MimeType::IMAGE_HEIC,
            'image/heif'              => MimeType::IMAGE_HEIF,
            'application/pdf'         => MimeType::APPLICATION_PDF,
            'text/plain'              => MimeType::TEXT_PLAIN,
            'text/csv'                => MimeType::TEXT_CSV,
            'text/markdown'           => MimeType::TEXT_MARKDOWN,
            'text/html'               => MimeType::TEXT_HTML,
            'application/json'        => MimeType::APPLICATION_JSON,
            'video/mp4'               => MimeType::VIDEO_MP4,
            'audio/mpeg', 'audio/mp3' => MimeType::AUDIO_MP3,
            'audio/wav'               => MimeType::AUDIO_WAV,
            'audio/ogg'               => MimeType::AUDIO_OGG,
            default                   => MimeType::IMAGE_JPEG,
        };
    }

    protected function buildHistory(array $history): array
    {
        return array_values(array_map(
            fn($entry) => Content::parse(
                part: $entry['text'],
                role: $entry['role'] === 'user' ? Role::USER : Role::MODEL
            ),
            array_filter($history, fn($e) => !empty(trim($e['text'] ?? '')))
        ));
    }

    protected function buildTool(array $declarations): Tool
    {
        $functionDeclarations = array_map(function (array $def) {
            return new FunctionDeclaration(
                name: $def['name'],
                description: $def['description'],
                parameters: new Schema(
                    type: DataType::OBJECT,
                    properties: $this->buildProperties($def['parameters']['properties'] ?? []),
                    required: $def['parameters']['required'] ?? [],
                ),
            );
        }, $declarations);

        return new Tool(functionDeclarations: $functionDeclarations);
    }

    protected function buildProperties(array $properties): array
    {
        $result = [];
        foreach ($properties as $name => $prop) {
            $result[$name] = $this->buildSchema($prop);
        }
        return $result;
    }

    protected function buildSchema(array $prop): Schema
    {
        $type = match ($prop['type'] ?? 'string') {
            'integer' => DataType::INTEGER,
            'number'  => DataType::NUMBER,
            'boolean' => DataType::BOOLEAN,
            'array'   => DataType::ARRAY,
            'object'  => DataType::OBJECT,
            default   => DataType::STRING,
        };

        $args = [
            'type'        => $type,
            'description' => $prop['description'] ?? null,
        ];

        if ($type === DataType::ARRAY) {
            $itemsDef = $prop['items'] ?? ['type' => 'string'];
            $args['items'] = $this->buildSchema($itemsDef);
        }

        if ($type === DataType::OBJECT && !empty($prop['properties'])) {
            $args['properties'] = $this->buildProperties($prop['properties']);
            if (!empty($prop['required'])) {
                $args['required'] = $prop['required'];
            }
        }

        return new Schema(...$args);
    }

    protected function buildModelQueue(): array
    {
        $queue = [$this->activeModel];
        foreach ($this->models as $model) {
            if ($model !== $this->activeModel) {
                $queue[] = $model;
            }
        }
        return $queue;
    }

    // ─── Error Classification ─────────────────────────────────────

    private function classifyError(\Throwable $e): ?string
    {
        $code = $e->getCode();
        $message = strtolower($e->getMessage());

        if ($code === 429) {
            return 'rate_limit';
        }

        if ($code === 503) {
            return 'service_unavailable';
        }

        if (str_contains($message, 'quota') || str_contains(strtolower($e->getMessage()), 'resource_exhausted')) {
            return 'quota_exceeded';
        }

        return null;
    }

    protected function isRateLimitError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        if (in_array($e->getCode(), $this->rateLimitCodes)) {
            return true;
        }
        foreach (
            [
                'quota',
                'rate limit',
                'resource exhausted',
                '429',
                'too many requests',
                'high demand',
                'try again later',
                'overloaded',
                'capacity',
                'unavailable',
                'service unavailable',
                'temporarily',
                'please try again',
            ] as $kw
        ) {
            if (str_contains($message, $kw)) {
                return true;
            }
        }
        return false;
    }

    protected function isApiKeyError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());
        $code = $e->getCode();

        if (in_array($code, [401, 403])) {
            return true;
        }

        foreach (
            [
                'api key',
                'api_key',
                'apikey',
                'invalid key',
                'invalid api',
                'unauthorized',
                'forbidden',
                'permission denied',
                'authentication',
                'not authorized',
            ] as $kw
        ) {
            if (str_contains($message, $kw)) {
                return true;
            }
        }

        return false;
    }

    protected function isQuotaExceededError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        foreach (
            [
                'quota exceeded',
                'billing not enabled',
                'billing required',
                'payment required',
                'exceeded quota',
                'limit exceeded',
            ] as $kw
        ) {
            if (str_contains($message, $kw)) {
                return true;
            }
        }

        return false;
    }

    protected function getUserFriendlyError(\Throwable $e): string
    {
        $message = strtolower($e->getMessage());
        $code = $e->getCode();

        if ($code === 0 && str_contains($message, 'timed out')) {
            return 'Koneksi ke AI Assistant timeout. Silakan coba lagi.';
        }

        if (str_contains($message, 'connection') || str_contains($message, 'network')) {
            return 'Gagal terhubung ke server AI. Periksa koneksi internet Anda.';
        }

        return 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.';
    }

    // ─── Fallback Execution ───────────────────────────────────────

    /**
     * Execute an API call with automatic model fallback via ModelSwitcher.
     * Used by chat() and chatWithTools().
     */
    private function callWithFallback(callable $apiCall): array
    {
        $originalModel = $this->switcher->getActiveModel();
        $currentModel = $originalModel;
        $switched = false;

        while (true) {
            try {
                $result = $apiCall($currentModel);

                if (is_array($result) && isset($result['_raw'])) {
                    unset($result['_raw']);
                    $result['model'] = $currentModel;
                } else {
                    $result = ['text' => $result, 'model' => $currentModel];
                }

                if ($switched) {
                    $this->switcher->setActiveModel($currentModel);
                    $result['switched_model'] = true;
                }

                return $result;
            } catch (\Throwable $e) {
                if ($this->isApiKeyError($e)) {
                    Log::error("GeminiProvider: Invalid API key on [{$currentModel}]. Check GEMINI_API_KEY configuration.");
                    throw new \RuntimeException(
                        'Konfigurasi AI Assistant tidak valid. Silakan hubungi administrator untuk memeriksa pengaturan AI Service.',
                        401
                    );
                }

                $errorClass = $this->classifyError($e);

                if ($errorClass === null) {
                    Log::error("GeminiProvider error on [{$currentModel}]: " . $e->getMessage(), [
                        'model'      => $currentModel,
                        'error_code' => $e->getCode(),
                        'error_type' => get_class($e),
                    ]);
                    throw new \RuntimeException(
                        'Gagal terhubung ke AI Assistant. Error: ' . $this->getUserFriendlyError($e),
                        503
                    );
                }

                $this->switcher->markUnavailable($currentModel, $errorClass);

                $nextModel = null;
                try {
                    $nextModel = $this->switcher->nextAvailableModel($currentModel);
                } catch (AllModelsUnavailableException $ex) {
                    $this->dispatchSwitchLog($currentModel, 'none', $errorClass, $e->getMessage());
                    return [
                        'text'  => 'Layanan AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.',
                        'error' => true,
                    ];
                }

                $this->dispatchSwitchLog($currentModel, $nextModel, $errorClass, $e->getMessage());

                Log::warning("GeminiProvider: [{$currentModel}] {$errorClass}, switching to [{$nextModel}].");

                $currentModel = $nextModel;
                $switched = true;
            }
        }
    }

    /**
     * Execute an API call with simple model queue fallback.
     * Used by generate(), chatWithMedia(), generateWithImage(), sendFunctionResults().
     */
    protected function runWithFallback(callable $fn): array
    {
        $queue = $this->buildModelQueue();

        foreach ($queue as $model) {
            try {
                $result = $fn($model);

                if ($model !== $this->activeModel) {
                    Log::info("GeminiProvider: switched to model [{$model}]");
                }

                if (is_array($result) && isset($result['_raw'])) {
                    unset($result['_raw']);
                    $result['model'] = $model;
                    return $result;
                }

                return ['text' => $result, 'model' => $model];
            } catch (\Throwable $e) {
                if ($this->isApiKeyError($e)) {
                    Log::error("GeminiProvider: Invalid API key on [{$model}]. Check GEMINI_API_KEY configuration.");
                    throw new \RuntimeException(
                        'Konfigurasi AI Assistant tidak valid. Silakan hubungi administrator untuk memeriksa pengaturan AI Service.',
                        401
                    );
                }

                if ($this->isRateLimitError($e)) {
                    Log::warning("GeminiProvider: rate limit on [{$model}], trying next...");
                    continue;
                }

                if ($this->isQuotaExceededError($e)) {
                    Log::error("GeminiProvider: Quota exceeded on [{$model}]. Billing may need to be enabled.");
                    throw new \RuntimeException(
                        'Layanan AI sedang mengalami keterbatasan. Silakan coba beberapa saat lagi.',
                        429
                    );
                }

                Log::error("GeminiProvider error on [{$model}]: " . $e->getMessage(), [
                    'model'      => $model,
                    'error_code' => $e->getCode(),
                    'error_type' => get_class($e),
                ]);

                throw new \RuntimeException(
                    'Gagal terhubung ke AI Assistant. Error: ' . $this->getUserFriendlyError($e),
                    503
                );
            }
        }

        throw new \RuntimeException('Layanan AI sedang mengalami gangguan. Silakan coba beberapa saat lagi.');
    }

    /**
     * Dispatch LogModelSwitchJob if the job class exists.
     */
    private function dispatchSwitchLog(string $fromModel, string $toModel, string $reason, ?string $errorMessage): void
    {
        if (!class_exists(\App\Jobs\LogModelSwitchJob::class)) {
            return;
        }

        $tenantId = null;
        try {
            $tenantId = auth()->user()?->tenant_id;
        } catch (\Throwable) {
            // Not in an authenticated context
        }

        $requestContext = null;
        try {
            $requestContext = request()->route()?->getName();
        } catch (\Throwable) {
            // Not in an HTTP context
        }

        dispatch(new \App\Jobs\LogModelSwitchJob(
            fromModel: $fromModel,
            toModel: $toModel,
            reason: $reason,
            errorMessage: $errorMessage,
            requestContext: $requestContext,
            triggeredByTenantId: $tenantId,
        ));
    }
}
