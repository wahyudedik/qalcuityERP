<?php

namespace App\Services\AI;

use App\Contracts\AiProvider;
use App\Exceptions\AllProvidersUnavailableException;
use App\Exceptions\RateLimitException;
use App\Models\AiProviderSwitchLog;
use App\Models\AiUsageLog;
use App\Models\SystemSetting;
use App\Models\TenantApiSetting;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Log;

/**
 * AiProviderRouter — orkestrasi pemilihan provider dan fallback lintas-provider.
 *
 * Tanggung jawab:
 * - Memilih provider aktif berdasarkan konfigurasi berlapis:
 *     1. TenantApiSetting['ai_provider'] (per-tenant override)
 *     2. SystemSetting['ai_default_provider'] (global platform)
 *     3. config('ai.default_provider', 'gemini') (config default)
 * - Mendelegasikan request ke provider yang dipilih
 * - Menangani fallback otomatis via ProviderSwitcher ketika provider gagal
 * - Mencatat penggunaan ke ai_usage_logs (dengan kolom provider)
 * - Mencatat peralihan ke ai_provider_switch_logs
 * - Memuat ulang konfigurasi dari SystemSetting tanpa restart server
 *
 * Requirements: 3.1–3.8, 5.5, 5.6, 6.7, 7.1, 7.2
 */
class AiProviderRouter implements AiProvider
{
    /**
     * Tenant context yang akan dipropagasi ke provider aktif.
     */
    private ?string $tenantContext = null;

    /**
     * Bahasa yang akan dipropagasi ke provider aktif.
     */
    private string $language = 'id';

    /**
     * Tenant ID yang sedang aktif (diset via withTenantContext atau dari auth).
     */
    private ?int $activeTenantId = null;

    /**
     * UseCaseRouter untuk routing berbasis use case (lazy-loaded).
     */
    private ?UseCaseRouter $useCaseRouter = null;

    public function __construct(
        private readonly GeminiProvider $geminiProvider,
        private readonly AnthropicProvider $anthropicProvider,
        private readonly ProviderSwitcher $switcher,
    ) {}

    // ─── AiProvider Contract ──────────────────────────────────────

    /**
     * Kembalikan identifier unik router.
     * Requirements: 1.6
     */
    public function getProviderName(): string
    {
        return 'router';
    }

    /**
     * Cek apakah setidaknya satu provider tersedia.
     * Requirements: 1.5
     */
    public function isAvailable(): bool
    {
        return $this->geminiProvider->isAvailable() || $this->anthropicProvider->isAvailable();
    }

    /**
     * Set konteks bisnis tenant untuk system prompt.
     * Dipropagasi ke provider aktif saat request dieksekusi.
     * Requirements: 1.7
     */
    public function withTenantContext(string $context): static
    {
        $this->tenantContext = $context;
        return $this;
    }

    /**
     * Set bahasa respons AI.
     * Dipropagasi ke provider aktif saat request dieksekusi.
     * Requirements: 1.8
     */
    public function withLanguage(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    /**
     * Set tenant ID aktif untuk resolusi provider dan logging.
     */
    public function withTenantId(?int $tenantId): static
    {
        $this->activeTenantId = $tenantId;
        return $this;
    }

    /**
     * Chat biasa dengan history percakapan.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.1, 3.1–3.8, 2.7, 2.8
     *
     * @param  string  $prompt   Prompt untuk AI
     * @param  array  $history   History percakapan
     * @param  array  $options   Opsi tambahan
     * @param  string|null  $useCase   Use case identifier untuk routing berbasis use case (opsional)
     */
    public function chat(string $prompt, array $history = [], array $options = [], ?string $useCase = null): array
    {
        // Jika useCase diberikan, delegasikan ke UseCaseRouter
        if ($useCase !== null) {
            return $this->routeViaUseCase(
                $useCase,
                fn(AiProvider $provider) => $provider->chat($prompt, $history, $options)
            );
        }

        // Perilaku lama (backward compatible)
        return $this->executeWithFallback(
            fn(AiProvider $provider) => $provider->chat($prompt, $history, $options),
            $this->activeTenantId,
        );
    }

    /**
     * One-shot generation tanpa history.
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.2, 3.1–3.8, 2.7, 2.8
     *
     * @param  string  $prompt   Prompt untuk AI
     * @param  array  $options   Opsi tambahan
     * @param  string|null  $useCase   Use case identifier untuk routing berbasis use case (opsional)
     */
    public function generate(string $prompt, array $options = [], ?string $useCase = null): array
    {
        // Jika useCase diberikan, delegasikan ke UseCaseRouter
        if ($useCase !== null) {
            return $this->routeViaUseCase(
                $useCase,
                fn(AiProvider $provider) => $provider->generate($prompt, $options)
            );
        }

        // Perilaku lama (backward compatible)
        return $this->executeWithFallback(
            fn(AiProvider $provider) => $provider->generate($prompt, $options),
            $this->activeTenantId,
        );
    }

    /**
     * Chat dengan lampiran file/gambar (multimodal).
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.3, 3.1–3.8, 2.7, 2.8
     *
     * @param  string  $message   Pesan untuk AI
     * @param  array  $files   Array file/gambar
     * @param  array  $history   History percakapan
     * @param  array  $options   Opsi tambahan
     * @param  string|null  $useCase   Use case identifier untuk routing berbasis use case (opsional)
     */
    public function chatWithMedia(string $message, array $files, array $history = [], array $options = [], ?string $useCase = null): array
    {
        // Jika useCase diberikan, delegasikan ke UseCaseRouter
        if ($useCase !== null) {
            return $this->routeViaUseCase(
                $useCase,
                fn(AiProvider $provider) => $provider->chatWithMedia($message, $files, $history, $options)
            );
        }

        // Perilaku lama (backward compatible)
        return $this->executeWithFallback(
            fn(AiProvider $provider) => $provider->chatWithMedia($message, $files, $history, $options),
            $this->activeTenantId,
        );
    }

    /**
     * Generate teks dari prompt + gambar (base64).
     * Return: ['text' => string, 'model' => string]
     * Requirements: 1.4, 3.1–3.8, 2.7, 2.8
     *
     * @param  string  $prompt   Prompt untuk AI
     * @param  string  $imageData   Data gambar dalam format base64
     * @param  string  $mimeType   MIME type gambar
     * @param  string|null  $useCase   Use case identifier untuk routing berbasis use case (opsional)
     */
    public function generateWithImage(string $prompt, string $imageData, string $mimeType, ?string $useCase = null): array
    {
        // Jika useCase diberikan, delegasikan ke UseCaseRouter
        if ($useCase !== null) {
            return $this->routeViaUseCase(
                $useCase,
                fn(AiProvider $provider) => $provider->generateWithImage($prompt, $imageData, $mimeType)
            );
        }

        // Perilaku lama (backward compatible)
        return $this->executeWithFallback(
            fn(AiProvider $provider) => $provider->generateWithImage($prompt, $imageData, $mimeType),
            $this->activeTenantId,
        );
    }

    // ─── Provider Resolution ──────────────────────────────────────

    /**
     * Resolve provider yang tepat berdasarkan konfigurasi berlapis.
     *
     * Urutan prioritas:
     * 1. TenantApiSetting['ai_provider'] — override per-tenant
     * 2. SystemSetting['ai_default_provider'] — konfigurasi global platform
     * 3. config('ai.default_provider', 'gemini') — config default
     *
     * Juga menerapkan API key tenant jika dikonfigurasi.
     *
     * Requirements: 3.1, 5.5, 5.6, 6.7
     */
    public function resolveProvider(?int $tenantId = null): AiProvider
    {
        // Muat ulang konfigurasi dari SystemSetting (tanpa restart server)
        $this->reloadSystemConfig();

        $providerName = $this->resolveProviderName($tenantId);

        $provider = $this->getProviderInstance($providerName);

        // Terapkan API key tenant jika ada
        if ($tenantId !== null) {
            $provider = $this->applyTenantApiKey($provider, $providerName, $tenantId);
        }

        // Propagasi context dan bahasa ke provider
        if ($this->tenantContext !== null) {
            $provider->withTenantContext($this->tenantContext);
        }

        $provider->withLanguage($this->language);

        return $provider;
    }

    // ─── Use Case Routing ─────────────────────────────────────────

    /**
     * Route request melalui UseCaseRouter dan eksekusi dengan provider yang dipilih.
     *
     * Requirements: 2.7, 2.8, 8.7
     *
     * @param  string  $useCase   Use case identifier
     * @param  callable  $fn   Callable yang menerima AiProvider dan mengembalikan array result
     * @return array  Result dari callable
     */
    private function routeViaUseCase(string $useCase, callable $fn): array
    {
        // Lazy-load UseCaseRouter
        if ($this->useCaseRouter === null) {
            $this->useCaseRouter = app(UseCaseRouter::class);
        }

        // Propagasi context dan bahasa ke UseCaseRouter
        if ($this->tenantContext !== null) {
            $this->useCaseRouter->withTenantContext($this->tenantContext);
        }

        $this->useCaseRouter->withLanguage($this->language);

        // Delegasikan ke UseCaseRouter untuk routing dan eksekusi
        return $this->useCaseRouter->routeAndExecute($useCase, $fn, $this->activeTenantId);
    }

    // ─── Fallback Execution ───────────────────────────────────────

    /**
     * Eksekusi callable dengan fallback otomatis ke provider berikutnya.
     *
     * Alur:
     * 1. Resolve provider aktif
     * 2. Coba eksekusi
     * 3. Jika gagal dengan RateLimitException atau server error (5xx):
     *    - Mark provider unavailable
     *    - Catat switch ke ai_provider_switch_logs
     *    - Coba provider berikutnya via ProviderSwitcher
     * 4. Jika semua provider gagal: lempar AllProvidersUnavailableException
     * 5. Setelah berhasil: catat ke ai_usage_logs
     *
     * Requirements: 3.2–3.8, 7.1, 7.2
     */
    private function executeWithFallback(callable $fn, ?int $tenantId = null): array
    {
        $fallbackOrder = $this->resolveFallbackOrder($tenantId);
        $providerInstances = $this->buildProviderInstances($tenantId);

        $primaryProviderName = $this->resolveProviderName($tenantId);

        // Susun fallback order dimulai dari provider yang dipilih
        $orderedFallback = $this->reorderFallback($fallbackOrder, $primaryProviderName);

        $lastException = null;
        $fromProvider = null;

        foreach ($orderedFallback as $providerName) {
            if (!isset($providerInstances[$providerName])) {
                continue;
            }

            if (!$this->switcher->isProviderAvailable($providerName)) {
                Log::debug("AiProviderRouter: provider [{$providerName}] dalam cooldown, melewati.");
                continue;
            }

            $provider = $providerInstances[$providerName];

            try {
                $result = $fn($provider);

                // Berhasil — catat ke ai_usage_logs
                $this->logUsage($tenantId, $providerName);

                // Jika ada provider sebelumnya yang gagal, catat switch ke provider ini
                if ($fromProvider !== null) {
                    $reason = $lastException instanceof RateLimitException ? 'rate_limit' : 'server_error';
                    $this->logProviderSwitch($tenantId, $fromProvider, $providerName, $reason, $lastException?->getMessage());
                }

                return $result;
            } catch (RateLimitException $e) {
                Log::warning("AiProviderRouter: rate limit pada provider [{$providerName}]", [
                    'provider' => $providerName,
                    'error'    => $e->getMessage(),
                ]);

                $this->switcher->markProviderUnavailable($providerName, 'rate_limit');

                // Catat switch dari provider yang gagal ke provider berikutnya
                // fromProvider diset ke providerName saat ini agar switch berikutnya bisa dicatat
                $fromProvider = $providerName;
                $lastException = $e;
                continue;
            } catch (\RuntimeException $e) {
                $statusCode = $e->getCode();

                // Server error (5xx) — bisa di-retry dengan provider lain
                if ($statusCode >= 500) {
                    Log::warning("AiProviderRouter: server error pada provider [{$providerName}] (HTTP {$statusCode})", [
                        'provider' => $providerName,
                        'error'    => $e->getMessage(),
                    ]);

                    $this->switcher->markProviderUnavailable($providerName, 'server_error');

                    $fromProvider = $providerName;
                    $lastException = $e;
                    continue;
                }

                // Error lain (401, 403, dll.) — langsung lempar
                throw $e;
            }
        }

        // Semua provider gagal — coba via ProviderSwitcher untuk dispatch event
        try {
            $this->switcher->getNextAvailableProvider($orderedFallback, $providerInstances);
        } catch (AllProvidersUnavailableException $e) {
            // Catat switch terakhir jika ada
            if ($fromProvider !== null) {
                $this->logProviderSwitch(
                    $tenantId,
                    $fromProvider,
                    'none',
                    $lastException instanceof RateLimitException ? 'rate_limit' : 'server_error',
                    $lastException?->getMessage(),
                );
            }

            throw $e;
        }

        // Seharusnya tidak sampai sini, tapi untuk keamanan
        throw new AllProvidersUnavailableException($orderedFallback);
    }

    // ─── Provider Name Resolution ─────────────────────────────────

    /**
     * Resolve nama provider berdasarkan konfigurasi berlapis.
     *
     * Requirements: 3.1, 5.5, 5.6, 6.7
     */
    private function resolveProviderName(?int $tenantId = null): string
    {
        // 1. Cek TenantApiSetting
        if ($tenantId !== null) {
            $tenantProvider = TenantApiSetting::get($tenantId, 'ai_provider');
            if (!empty($tenantProvider)) {
                return $tenantProvider;
            }
        }

        // 2. Cek SystemSetting
        $systemProvider = SystemSetting::get('ai_default_provider');
        if (!empty($systemProvider)) {
            return $systemProvider;
        }

        // 3. Fallback ke config default
        return config('ai.default_provider', 'gemini');
    }

    /**
     * Resolve urutan fallback provider.
     * Cek SystemSetting['ai_provider_fallback_order'] terlebih dahulu,
     * lalu fallback ke config('ai.fallback_order').
     */
    private function resolveFallbackOrder(?int $tenantId = null): array
    {
        $systemFallbackOrder = SystemSetting::get('ai_provider_fallback_order');

        if (!empty($systemFallbackOrder)) {
            $decoded = json_decode($systemFallbackOrder, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }

        return config('ai.fallback_order', ['gemini', 'anthropic']);
    }

    /**
     * Susun ulang fallback order agar provider utama ada di posisi pertama.
     */
    private function reorderFallback(array $fallbackOrder, string $primaryProvider): array
    {
        $reordered = [$primaryProvider];

        foreach ($fallbackOrder as $provider) {
            if ($provider !== $primaryProvider) {
                $reordered[] = $provider;
            }
        }

        return $reordered;
    }

    // ─── Provider Instance Management ────────────────────────────

    /**
     * Kembalikan instance provider berdasarkan nama.
     */
    private function getProviderInstance(string $providerName): AiProvider
    {
        return match ($providerName) {
            'anthropic' => $this->anthropicProvider,
            default     => $this->geminiProvider,  // 'gemini' dan default
        };
    }

    /**
     * Bangun map provider name → instance untuk digunakan oleh ProviderSwitcher.
     * Terapkan context dan bahasa ke semua provider.
     */
    private function buildProviderInstances(?int $tenantId = null): array
    {
        $instances = [];

        foreach (['gemini', 'anthropic'] as $name) {
            $provider = $this->getProviderInstance($name);

            // Terapkan API key tenant jika ada
            if ($tenantId !== null) {
                $provider = $this->applyTenantApiKey($provider, $name, $tenantId);
            }

            // Propagasi context dan bahasa
            if ($this->tenantContext !== null) {
                $provider->withTenantContext($this->tenantContext);
            }

            $provider->withLanguage($this->language);

            $instances[$name] = $provider;
        }

        return $instances;
    }

    /**
     * Terapkan API key tenant ke provider jika dikonfigurasi di TenantApiSetting.
     *
     * Requirements: 5.3, 5.4
     */
    private function applyTenantApiKey(AiProvider $provider, string $providerName, int $tenantId): AiProvider
    {
        $keyMap = [
            'gemini'    => 'gemini_api_key',
            'anthropic' => 'anthropic_api_key',
        ];

        $settingKey = $keyMap[$providerName] ?? null;

        if ($settingKey === null) {
            return $provider;
        }

        $tenantApiKey = TenantApiSetting::get($tenantId, $settingKey);

        if (empty($tenantApiKey)) {
            return $provider;
        }

        // Override config API key untuk request ini
        // Buat instance baru dengan API key tenant
        if ($providerName === 'anthropic') {
            config(['ai.providers.anthropic.api_key' => $tenantApiKey]);
        } elseif ($providerName === 'gemini') {
            config(['ai.providers.gemini.api_key' => $tenantApiKey]);
        }

        return $provider;
    }

    // ─── Config Reload ────────────────────────────────────────────

    /**
     * Muat ulang konfigurasi dari SystemSetting ke Laravel config.
     * Memungkinkan perubahan konfigurasi tanpa restart server.
     *
     * Requirements: 4.9, 6.7
     */
    private function reloadSystemConfig(): void
    {
        SystemSetting::loadIntoConfig([
            'ai_default_provider'        => 'ai.default_provider',
            'ai_provider_mode'           => 'ai.mode',
            'anthropic_api_key'          => 'ai.providers.anthropic.api_key',
            'anthropic_model'            => 'ai.providers.anthropic.model',
        ]);
    }

    // ─── Logging ──────────────────────────────────────────────────

    /**
     * Catat penggunaan AI ke ai_usage_logs dengan kolom provider.
     *
     * Requirements: 7.1
     */
    private function logUsage(?int $tenantId, string $providerName): void
    {
        if ($tenantId === null) {
            return;
        }

        try {
            $userId = null;
            try {
                $userId = auth()->id();
            } catch (\Throwable) {
                // Tidak dalam konteks HTTP
            }

            if ($userId === null) {
                return;
            }

            $month = now()->format('Y-m');

            // Update atau buat record, set provider
            $log = AiUsageLog::withoutGlobalScope('tenant')
                ->firstOrCreate(
                    ['tenant_id' => $tenantId, 'user_id' => $userId, 'month' => $month],
                    ['message_count' => 0, 'token_count' => 0, 'provider' => $providerName],
                );

            AiUsageLog::withoutGlobalScope('tenant')
                ->where('id', $log->id)
                ->update([
                    'message_count' => \Illuminate\Support\Facades\DB::raw('message_count + 1'),
                    'provider'      => $providerName,
                ]);
        } catch (\Throwable $e) {
            // Logging tidak boleh mengganggu request utama
            Log::warning('AiProviderRouter: gagal mencatat ai_usage_logs', [
                'tenant_id' => $tenantId,
                'provider'  => $providerName,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Catat peralihan provider ke ai_provider_switch_logs.
     *
     * Requirements: 3.4, 7.2
     */
    private function logProviderSwitch(
        ?int $tenantId,
        string $fromProvider,
        string $toProvider,
        string $reason,
        ?string $errorMessage = null,
    ): void {
        try {
            AiProviderSwitchLog::withoutGlobalScope('tenant')->create([
                'tenant_id'     => $tenantId,
                'from_provider' => $fromProvider,
                'to_provider'   => $toProvider,
                'reason'        => $reason,
                'error_message' => $errorMessage ? mb_substr($errorMessage, 0, 65535) : null,
                'created_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // Logging tidak boleh mengganggu request utama
            Log::warning('AiProviderRouter: gagal mencatat ai_provider_switch_logs', [
                'from_provider' => $fromProvider,
                'to_provider'   => $toProvider,
                'reason'        => $reason,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}
