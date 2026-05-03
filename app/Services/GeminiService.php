<?php

namespace App\Services;

use App\Services\AI\AiProviderRouter;
use App\Services\AI\Providers\GeminiProvider;

/**
 * GeminiService — thin wrapper atas AiProviderRouter untuk backward compatibility.
 *
 * Semua method yang ada di AiProvider contract didelegasikan ke AiProviderRouter.
 * Method Gemini-specific (chatWithTools, sendFunctionResults, getActiveModel, setModel)
 * didelegasikan langsung ke GeminiProvider.
 *
 * Kode yang sudah ada yang menggunakan GeminiService tidak perlu diubah.
 *
 * Requirements: 6.1–6.7
 */
class GeminiService
{
    public function __construct(
        private readonly AiProviderRouter $router,
        private readonly GeminiProvider $geminiProvider,
    ) {}

    // ─── AiProvider Contract Methods (delegasi ke AiProviderRouter) ───────────

    /**
     * Chat biasa dengan history percakapan.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 6.2, 6.3, 8.1
     */
    public function chat(string $message, array $history = [], array $options = [], ?string $useCase = null): array
    {
        return $this->router->chat($message, $history, $options, $useCase);
    }

    /**
     * One-shot generation tanpa history.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 6.2, 6.3, 8.1
     */
    public function generate(string $prompt, array $options = [], ?string $useCase = null): array
    {
        return $this->router->generate($prompt, $options, $useCase);
    }

    /**
     * Chat dengan file/gambar (multimodal).
     * $files = [['mime_type' => 'image/jpeg', 'data' => base64string], ...]
     * Return: ['text' => string, 'model' => string]
     *
     * Catatan: parameter ke-4 tetap bernama $toolDeclarations untuk backward compatibility
     * dengan kode yang sudah ada yang menggunakan named argument `toolDeclarations:`.
     * Nilai ini diteruskan ke router sebagai $options['tools'].
     *
     * Requirements: 6.2, 6.3, 8.1
     */
    public function chatWithMedia(string $message, array $files, array $history = [], array $toolDeclarations = [], ?string $useCase = null): array
    {
        $options = !empty($toolDeclarations) ? ['tools' => $toolDeclarations] : [];
        return $this->router->chatWithMedia($message, $files, $history, $options, $useCase);
    }

    /**
     * Generate teks dari prompt + gambar (base64).
     * Return: ['text' => string, 'model' => string]
     * Requirements: 6.2, 6.3, 8.1
     */
    public function generateWithImage(string $prompt, string $imageData, string $mimeType, ?string $useCase = null): array
    {
        return $this->router->generateWithImage($prompt, $imageData, $mimeType, $useCase);
    }

    /**
     * Set konteks bisnis tenant untuk system prompt. Fluent interface.
     * Dipropagasi ke router dan geminiProvider.
     * Requirements: 6.2, 6.3
     */
    public function withTenantContext(string $context): static
    {
        $this->router->withTenantContext($context);
        $this->geminiProvider->withTenantContext($context);
        return $this;
    }

    /**
     * Set bahasa respons AI. Fluent interface.
     * Dipropagasi ke router dan geminiProvider.
     * Requirements: 6.2, 6.3
     */
    public function withLanguage(string $language): static
    {
        $this->router->withLanguage($language);
        $this->geminiProvider->withLanguage($language);
        return $this;
    }

    // ─── Gemini-Specific Methods (delegasi ke GeminiProvider) ─────────────────

    /**
     * Chat dengan function calling tools.
     * Return: ['text' => string, 'model' => string, 'function_calls' => array]
     *
     * Method ini Gemini-specific — tidak ada di AiProvider contract.
     * Didelegasikan langsung ke GeminiProvider untuk backward compatibility.
     */
    public function chatWithTools(string $message, array $history, array $toolDeclarations): array
    {
        return $this->geminiProvider->chatWithTools($message, $history, $toolDeclarations);
    }

    /**
     * Kirim hasil eksekusi function kembali ke Gemini.
     * Return: ['text' => string, 'model' => string]
     *
     * Method ini Gemini-specific — tidak ada di AiProvider contract.
     * Didelegasikan langsung ke GeminiProvider untuk backward compatibility.
     */
    public function sendFunctionResults(
        string $originalMessage,
        array $history,
        array $toolDeclarations,
        array $functionResults
    ): array {
        return $this->geminiProvider->sendFunctionResults(
            $originalMessage,
            $history,
            $toolDeclarations,
            $functionResults,
        );
    }

    /**
     * Kembalikan model Gemini yang sedang aktif.
     *
     * Method ini Gemini-specific — tidak ada di AiProvider contract.
     * Didelegasikan langsung ke GeminiProvider untuk backward compatibility.
     */
    public function getActiveModel(): string
    {
        return $this->geminiProvider->getActiveModel();
    }

    /**
     * Set model Gemini yang akan digunakan. Fluent interface.
     *
     * Method ini Gemini-specific — tidak ada di AiProvider contract.
     * Didelegasikan langsung ke GeminiProvider untuk backward compatibility.
     */
    public function setModel(string $model): static
    {
        $this->geminiProvider->setModel($model);
        return $this;
    }
}
