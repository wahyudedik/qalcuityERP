<?php

namespace App\Services;

use Gemini\Client;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected Client $client;
    protected array $models;
    protected array $rateLimitCodes;
    protected string $activeModel;

    public function __construct()
    {
        $this->client         = \Gemini::build(config('gemini.api_key'));
        $this->models         = config('gemini.fallback_models');
        $this->rateLimitCodes = config('gemini.rate_limit_codes', [429, 503, 500]);
        $this->activeModel    = config('gemini.model');
    }

    /**
     * Chat dengan history, auto-switch model jika rate limit.
     *
     * @param  string  $message
     * @param  array   $history  [['role' => 'user'|'model', 'text' => '...']]
     * @return array   ['text' => string, 'model' => string]
     */
    public function chat(string $message, array $history = []): array
    {
        $contents = $this->buildHistory($history);

        return $this->runWithFallback(function (string $model) use ($message, $contents) {
            $response = $this->client
                ->generativeModel($model)
                ->startChat(history: $contents)
                ->sendMessage($message);

            return $response->text();
        });
    }

    /**
     * One-shot generation tanpa history, auto-switch model jika rate limit.
     *
     * @return array ['text' => string, 'model' => string]
     */
    public function generate(string $prompt): array
    {
        return $this->runWithFallback(function (string $model) use ($prompt) {
            $response = $this->client
                ->generativeModel($model)
                ->generateContent($prompt);

            return $response->text();
        });
    }

    /**
     * Jalankan callable dengan fallback otomatis ke model berikutnya
     * jika terjadi rate limit atau error token.
     */
    protected function runWithFallback(callable $fn): array
    {
        // Susun urutan: mulai dari activeModel, lalu sisanya
        $queue = $this->buildModelQueue();

        foreach ($queue as $model) {
            try {
                $text = $fn($model);

                if ($model !== $this->activeModel) {
                    Log::info("GeminiService: switched to model [{$model}]");
                }

                return ['text' => $text, 'model' => $model];

            } catch (\Throwable $e) {
                if ($this->isRateLimitError($e)) {
                    Log::warning("GeminiService: rate limit on [{$model}], trying next...");
                    continue;
                }

                // Error lain langsung lempar
                throw $e;
            }
        }

        throw new \RuntimeException('All Gemini models are rate-limited or unavailable.');
    }

    /**
     * Susun antrian model: activeModel di depan, sisanya mengikuti urutan config.
     */
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

    /**
     * Konversi history array ke format Content Gemini.
     */
    protected function buildHistory(array $history): array
    {
        return array_map(fn($entry) => Content::parse(
            part: $entry['text'],
            role: $entry['role'] === 'user' ? Role::USER : Role::MODEL
        ), $history);
    }

    /**
     * Deteksi apakah exception adalah rate limit / quota error.
     */
    protected function isRateLimitError(\Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        // Cek HTTP status code jika tersedia
        if (method_exists($e, 'getCode') && in_array($e->getCode(), $this->rateLimitCodes)) {
            return true;
        }

        // Cek keyword umum dari Gemini API error
        $keywords = ['quota', 'rate limit', 'resource exhausted', '429', 'too many requests'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kembalikan model yang sedang aktif.
     */
    public function getActiveModel(): string
    {
        return $this->activeModel;
    }

    /**
     * Override model aktif secara manual.
     */
    public function setModel(string $model): static
    {
        $this->activeModel = $model;
        return $this;
    }
}
