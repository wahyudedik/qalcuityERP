<?php

namespace App\Contracts;

interface AiProvider
{
    /**
     * Kirim pesan chat dengan history percakapan.
     *
     * @param  string  $prompt  Pesan terbaru dari pengguna
     * @param  array  $history  History percakapan sebelumnya
     * @param  array  $options  Opsi tambahan (temperature, max_tokens, dll.)
     * @return array{text: string, model: string}
     */
    public function chat(string $prompt, array $history = [], array $options = []): array;

    /**
     * Generate teks dari prompt tunggal (tanpa history).
     *
     * @param  string  $prompt  Prompt yang akan di-generate
     * @param  array  $options  Opsi tambahan
     * @return array{text: string, model: string}
     */
    public function generate(string $prompt, array $options = []): array;

    /**
     * Chat dengan lampiran file/gambar.
     *
     * @param  string  $message  Pesan dari pengguna
     * @param  array  $files  Array file yang dilampirkan
     * @param  array  $history  History percakapan sebelumnya
     * @param  array  $options  Opsi tambahan
     * @return array{text: string, model: string}
     */
    public function chatWithMedia(string $message, array $files, array $history = [], array $options = []): array;

    /**
     * Generate teks dari prompt + gambar (base64).
     *
     * @param  string  $prompt  Prompt yang akan di-generate
     * @param  string  $imageData  Data gambar dalam format base64
     * @param  string  $mimeType  MIME type gambar (contoh: 'image/jpeg')
     * @return array{text: string, model: string}
     */
    public function generateWithImage(string $prompt, string $imageData, string $mimeType): array;

    /**
     * Cek apakah provider siap menerima request.
     * Mengembalikan true jika API key terkonfigurasi dan provider tidak dalam cooldown.
     */
    public function isAvailable(): bool;

    /**
     * Kembalikan identifier unik provider.
     * Contoh: 'gemini', 'anthropic'
     */
    public function getProviderName(): string;

    /**
     * Set konteks bisnis tenant untuk system prompt.
     * Fluent interface — mengembalikan instance yang sama.
     */
    public function withTenantContext(string $context): static;

    /**
     * Set bahasa respons AI.
     * Fluent interface — mengembalikan instance yang sama.
     */
    public function withLanguage(string $language): static;
}
