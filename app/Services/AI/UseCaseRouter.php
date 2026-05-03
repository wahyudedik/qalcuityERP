<?php

namespace App\Services\AI;

use App\Contracts\AiProvider;
use App\Enums\AiUseCase;
use App\Exceptions\AllProvidersUnavailableException;
use App\Exceptions\InsufficientPlanException;
use App\Models\AiProviderSwitchLog;
use App\Models\AiUsageCostLog;
use App\Models\AiUseCaseRoute;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GeminiProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * UseCaseRouter — intelligent routing berbasis use case AI.
 *
 * Tanggung jawab:
 * - Resolve routing rule berdasarkan use case dan tenant (tenant-specific > global > config)
 * - Tier gating — validasi plan tenant memenuhi min_plan requirement
 * - Provider availability check dan fallback chain
 * - Cost attribution — catat setiap request ke ai_usage_cost_logs
 * - Fallback degradation tracking — tandai jika fallback dari heavyweight ke lightweight
 *
 * Requirements: 2.1–2.6, 3.1–3.8, 6.3–6.6, 7.1–7.6, 9.4
 */
class UseCaseRouter
{
    /**
     * Tenant context yang akan dipropagasi ke provider.
     */
    private ?string $tenantContext = null;

    /**
     * Bahasa yang akan dipropagasi ke provider.
     */
    private string $language = 'id';

    public function __construct(
        private readonly GeminiProvider $geminiProvider,
        private readonly AnthropicProvider $anthropicProvider,
        private readonly ProviderSwitcher $switcher,
    ) {}

    // ─── Public API ───────────────────────────────────────────────

    /**
     * Resolve routing rule yang berlaku untuk use case dan tenant tertentu.
     *
     * Urutan prioritas:
     * 1. Tenant-specific rule (tenant_id = X)
     * 2. Global rule (tenant_id = NULL)
     * 3. NULL (gunakan config default)
     *
     * Cache: 5 menit TTL dengan key ai_routing_rules:{tenantId} dan ai_routing_rules:global
     *
     * Requirements: 2.1, 2.2, 9.4
     *
     * @param  string  $useCase   Use case identifier (e.g., 'chatbot', 'financial_report')
     * @param  int|null  $tenantId  Tenant ID untuk resolusi tenant-specific rule
     * @return AiUseCaseRoute|null  Routing rule yang berlaku, atau null jika tidak ada
     */
    public function resolveRule(string $useCase, ?int $tenantId = null): ?AiUseCaseRoute
    {
        // 1. Cek tenant-specific rule (jika tenantId diberikan)
        if ($tenantId !== null) {
            $tenantRules = Cache::remember(
                "ai_routing_rules:{$tenantId}",
                300, // 5 menit
                fn() => AiUseCaseRoute::withoutTenantScope()
                    ->where('tenant_id', $tenantId)
                    ->where('is_active', true)
                    ->get()
                    ->keyBy('use_case')
            );

            if (isset($tenantRules[$useCase])) {
                return $tenantRules[$useCase];
            }
        }

        // 2. Cek global rule
        $globalRules = Cache::remember(
            'ai_routing_rules:global',
            300, // 5 menit
            fn() => AiUseCaseRoute::withoutTenantScope()
                ->whereNull('tenant_id')
                ->where('is_active', true)
                ->get()
                ->keyBy('use_case')
        );

        if (isset($globalRules[$useCase])) {
            return $globalRules[$useCase];
        }

        // 3. Tidak ada rule — gunakan config default
        return null;
    }

    /**
     * Route request ke provider yang tepat berdasarkan use case dan tenant.
     *
     * Alur:
     * 1. Resolve routing rule (tenant-specific > global > config)
     * 2. Tier gate check — validasi plan tenant
     * 3. Provider availability check
     * 4. Fallback chain jika provider tidak tersedia
     * 5. Propagasi context dan bahasa ke provider
     *
     * Requirements: 2.3, 2.4, 2.5, 2.6, 3.1–3.8, 7.1, 7.2
     *
     * @param  string  $useCase   Use case identifier
     * @param  int|null  $tenantId  Tenant ID untuk tier gating dan resolusi rule
     * @return AiProvider  Provider instance yang siap digunakan
     * @throws InsufficientPlanException  Jika plan tenant tidak memenuhi min_plan
     * @throws AllProvidersUnavailableException  Jika semua provider tidak tersedia
     */
    public function route(string $useCase, ?int $tenantId = null): AiProvider
    {
        // 1. Resolve routing rule
        $rule = $this->resolveRule($useCase, $tenantId);

        $providerName = null;
        $modelName = null;
        $minPlan = null;
        $fallbackChain = null;

        if ($rule !== null) {
            $providerName = $rule->provider;
            $modelName = $rule->model;
            $minPlan = $rule->min_plan;
            $fallbackChain = $rule->fallback_chain;

            Log::debug("[UseCaseRouter] use_case={$useCase} provider={$providerName} model={$modelName} tenant_id={$tenantId} reason=tenant_rule");
        } else {
            // Gunakan config default
            $config = config("ai.use_case_routing.{$useCase}");

            if ($config !== null) {
                $providerName = $config['provider'];
                $modelName = $config['model'] ?? null;
                $minPlan = $config['min_plan'] ?? null;

                Log::debug("[UseCaseRouter] use_case={$useCase} provider={$providerName} model={$modelName} tenant_id={$tenantId} reason=config_default");
            } else {
                // Unknown use case — fallback ke default provider
                $providerName = config('ai.default_provider', 'gemini');
                $modelName = null;
                $minPlan = null;

                Log::debug("[UseCaseRouter] use_case={$useCase} provider={$providerName} tenant_id={$tenantId} reason=unknown_use_case_fallback");
            }
        }

        // 2. Tier gate check
        if ($minPlan !== null && $tenantId !== null) {
            $this->checkTierGate($useCase, $minPlan, $tenantId);
        }

        // 3. Resolve fallback chain
        if ($fallbackChain === null || empty($fallbackChain)) {
            $fallbackChain = $this->getDefaultFallbackChain($useCase);
        }

        // 4. Cek provider availability dan fallback
        $provider = $this->resolveProviderWithFallback($providerName, $fallbackChain, $useCase, $tenantId);

        // 5. Set model jika ditentukan
        if ($modelName !== null && method_exists($provider, 'setModel')) {
            $provider->setModel($modelName);
        }

        // 6. Propagasi context dan bahasa
        if ($this->tenantContext !== null) {
            $provider->withTenantContext($this->tenantContext);
        }

        $provider->withLanguage($this->language);

        return $provider;
    }

    /**
     * Gabungkan routing dan eksekusi dalam satu panggilan.
     *
     * Alur:
     * 1. Route ke provider yang tepat
     * 2. Eksekusi callable dengan provider
     * 3. Ukur response time
     * 4. Catat ke ai_usage_cost_logs
     *
     * Requirements: 2.3, 6.3, 6.4, 6.5, 6.6
     *
     * @param  string  $useCase   Use case identifier
     * @param  callable  $fn   Callable yang menerima AiProvider dan mengembalikan array result
     * @param  int|null  $tenantId  Tenant ID
     * @return array  Result dari callable (format: ['text' => string, 'model' => string])
     * @throws InsufficientPlanException
     * @throws AllProvidersUnavailableException
     */
    public function routeAndExecute(string $useCase, callable $fn, ?int $tenantId = null): array
    {
        $startTime = microtime(true);

        $provider = $this->route($useCase, $tenantId);
        $providerName = $provider->getProviderName();

        $result = $fn($provider);

        $endTime = microtime(true);
        $responseTimeMs = (int) (($endTime - $startTime) * 1000);

        // Catat warning jika response time melebihi threshold
        $this->checkResponseTimeThreshold($useCase, $providerName, $responseTimeMs);

        // Catat ke ai_usage_cost_logs
        $this->logCost($useCase, $providerName, $result, $responseTimeMs, $tenantId);

        return $result;
    }

    /**
     * Set konteks bisnis tenant untuk system prompt.
     * Fluent interface.
     */
    public function withTenantContext(string $context): static
    {
        $this->tenantContext = $context;
        return $this;
    }

    /**
     * Set bahasa respons AI.
     * Fluent interface.
     */
    public function withLanguage(string $language): static
    {
        $this->language = $language;
        return $this;
    }

    // ─── Tier Gating ──────────────────────────────────────────────

    /**
     * Validasi plan tenant memenuhi min_plan requirement.
     *
     * Requirements: 3.1, 3.2, 3.3, 3.4, 3.7, 3.8
     *
     * @throws InsufficientPlanException  Jika plan tidak memenuhi syarat
     */
    private function checkTierGate(string $useCase, string $minPlan, int $tenantId): void
    {
        $tenant = Tenant::find($tenantId);

        if ($tenant === null) {
            return;
        }

        // Resolve plan tenant
        $currentPlan = $tenant->plan;

        // Jika tenant menggunakan SubscriptionPlan, gunakan slug dari SubscriptionPlan
        if ($tenant->subscription_plan_id !== null && $tenant->subscriptionPlan !== null) {
            $currentPlan = $tenant->subscriptionPlan->slug;
        }

        // Enterprise tenant selalu diizinkan
        if ($currentPlan === 'enterprise') {
            return;
        }

        // Cek hierarki plan
        $hierarchy = config('ai.plan_hierarchy', ['trial', 'starter', 'business', 'professional', 'enterprise']);

        $currentIndex = array_search($currentPlan, $hierarchy);
        $minIndex = array_search($minPlan, $hierarchy);

        // Jika plan tidak ditemukan dalam hierarki, izinkan (backward compatibility)
        if ($currentIndex === false || $minIndex === false) {
            return;
        }

        // Jika plan tenant di bawah min_plan, lempar exception
        if ($currentIndex < $minIndex) {
            throw new InsufficientPlanException($minPlan, $currentPlan, $useCase);
        }
    }

    // ─── Fallback Chain ───────────────────────────────────────────

    /**
     * Kembalikan fallback chain default berdasarkan kategori use case.
     *
     * Requirements: 7.1, 7.2, 9.3
     */
    private function getDefaultFallbackChain(string $useCase): array
    {
        // Cek apakah use case adalah heavyweight
        $isHeavyweight = false;

        try {
            $useCaseEnum = AiUseCase::tryFrom($useCase);
            if ($useCaseEnum !== null) {
                $isHeavyweight = $useCaseEnum->isHeavyweight();
            }
        } catch (\Throwable) {
            // Use case tidak dikenal — gunakan lightweight fallback
        }

        $category = $isHeavyweight ? 'heavyweight' : 'lightweight';

        return config("ai.use_case_fallback_chains.{$category}", ['gemini', 'anthropic']);
    }

    /**
     * Resolve provider dengan fallback chain.
     *
     * Requirements: 7.1, 7.2, 7.3, 7.4, 7.5, 7.6
     *
     * @throws AllProvidersUnavailableException  Jika semua provider tidak tersedia
     */
    private function resolveProviderWithFallback(
        string $primaryProvider,
        array $fallbackChain,
        string $useCase,
        ?int $tenantId
    ): AiProvider {
        // Susun urutan provider: primary provider + fallback chain
        $orderedProviders = [$primaryProvider];

        foreach ($fallbackChain as $provider) {
            if ($provider !== $primaryProvider) {
                $orderedProviders[] = $provider;
            }
        }

        $fromProvider = null;

        foreach ($orderedProviders as $providerName) {
            $provider = $this->getProviderInstance($providerName);

            // Cek apakah provider dikonfigurasi (API key tidak kosong)
            if (!$this->isProviderConfigured($providerName)) {
                Log::debug("[UseCaseRouter] provider [{$providerName}] tidak dikonfigurasi, melewati.");
                continue;
            }

            // Cek apakah provider tersedia (tidak dalam cooldown)
            if (!$this->switcher->isProviderAvailable($providerName)) {
                Log::debug("[UseCaseRouter] provider [{$providerName}] dalam cooldown, melewati.");
                continue;
            }

            // Provider tersedia — catat fallback jika ada
            if ($fromProvider !== null) {
                $this->logFallback($useCase, $fromProvider, $providerName, $tenantId);
            }

            return $provider;
        }

        // Semua provider tidak tersedia
        throw new AllProvidersUnavailableException($orderedProviders);
    }

    /**
     * Cek apakah provider dikonfigurasi (API key tidak kosong).
     *
     * Requirements: 11.4
     */
    private function isProviderConfigured(string $providerName): bool
    {
        $apiKey = config("ai.providers.{$providerName}.api_key");

        return !empty($apiKey);
    }

    /**
     * Kembalikan instance provider berdasarkan nama.
     */
    private function getProviderInstance(string $providerName): AiProvider
    {
        return match ($providerName) {
            'anthropic' => $this->anthropicProvider,
            default     => $this->geminiProvider,
        };
    }

    // ─── Cost Logging ─────────────────────────────────────────────

    /**
     * Catat penggunaan AI ke ai_usage_cost_logs.
     *
     * Requirements: 6.3, 6.4, 6.5, 6.6
     */
    private function logCost(
        string $useCase,
        string $providerName,
        array $result,
        int $responseTimeMs,
        ?int $tenantId
    ): void {
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

            $modelName = $result['model'] ?? 'unknown';
            $text = $result['text'] ?? '';

            // Estimasi token jika tidak tersedia
            $inputTokens = $result['input_tokens'] ?? $this->estimateTokens($text);
            $outputTokens = $result['output_tokens'] ?? $this->estimateTokens($text);

            // Hitung estimated_cost_idr
            $costPer1kTokens = $this->getCostPer1kTokens($providerName, $modelName);
            $estimatedCostIdr = (($inputTokens + $outputTokens) / 1000) * $costPer1kTokens;

            // Cek apakah fallback degraded (dari heavyweight ke lightweight)
            $fallbackDegraded = $this->isFallbackDegraded($useCase, $providerName);

            AiUsageCostLog::record([
                'tenant_id'          => $tenantId,
                'user_id'            => $userId,
                'use_case'           => $useCase,
                'provider'           => $providerName,
                'model'              => $modelName,
                'input_tokens'       => $inputTokens,
                'output_tokens'      => $outputTokens,
                'estimated_cost_idr' => $estimatedCostIdr,
                'response_time_ms'   => $responseTimeMs,
                'fallback_degraded'  => $fallbackDegraded,
                'created_at'         => now(),
            ]);
        } catch (\Throwable $e) {
            // Logging tidak boleh mengganggu request utama
            Log::warning('[UseCaseRouter] gagal mencatat ai_usage_cost_logs', [
                'use_case'  => $useCase,
                'provider'  => $providerName,
                'tenant_id' => $tenantId,
                'error'     => $e->getMessage(),
            ]);
        }
    }

    /**
     * Estimasi token menggunakan rasio 4 karakter per token.
     *
     * Requirements: 6.6
     */
    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Kembalikan cost per 1K tokens untuk provider dan model tertentu.
     *
     * Requirements: 6.5, 9.2
     */
    private function getCostPer1kTokens(string $providerName, string $modelName): float
    {
        $cost = config("ai.cost_per_1k_tokens.{$providerName}.{$modelName}");

        if ($cost !== null) {
            return (float) $cost;
        }

        // Fallback ke default provider
        $defaultCost = config("ai.cost_per_1k_tokens.{$providerName}.default");

        return $defaultCost !== null ? (float) $defaultCost : 0.0;
    }

    /**
     * Cek apakah fallback dari heavyweight ke lightweight.
     *
     * Requirements: 7.6
     */
    private function isFallbackDegraded(string $useCase, string $actualProvider): bool
    {
        // Cek apakah use case adalah heavyweight
        $isHeavyweight = false;

        try {
            $useCaseEnum = AiUseCase::tryFrom($useCase);
            if ($useCaseEnum !== null) {
                $isHeavyweight = $useCaseEnum->isHeavyweight();
            }
        } catch (\Throwable) {
            return false;
        }

        // Jika use case heavyweight dan actual provider adalah lightweight (gemini), maka degraded
        if ($isHeavyweight && $actualProvider === 'gemini') {
            return true;
        }

        return false;
    }

    // ─── Fallback Logging ─────────────────────────────────────────

    /**
     * Catat fallback ke ai_provider_switch_logs dengan kolom use_case.
     *
     * Requirements: 7.3, 7.4, 7.5
     */
    private function logFallback(
        string $useCase,
        string $fromProvider,
        string $toProvider,
        ?int $tenantId
    ): void {
        try {
            AiProviderSwitchLog::withoutGlobalScope('tenant')->create([
                'tenant_id'     => $tenantId,
                'from_provider' => $fromProvider,
                'to_provider'   => $toProvider,
                'reason'        => 'use_case_fallback',
                'use_case'      => $useCase,
                'error_message' => null,
                'created_at'    => now(),
            ]);

            Log::debug("[UseCaseRouter] FALLBACK use_case={$useCase} from={$fromProvider} to={$toProvider} reason=use_case_fallback fallback_degraded=" . ($this->isFallbackDegraded($useCase, $toProvider) ? 'true' : 'false'));
        } catch (\Throwable $e) {
            // Logging tidak boleh mengganggu request utama
            Log::warning('[UseCaseRouter] gagal mencatat ai_provider_switch_logs', [
                'use_case'      => $useCase,
                'from_provider' => $fromProvider,
                'to_provider'   => $toProvider,
                'error'         => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cek response time dan catat warning jika melebihi threshold.
     *
     * Requirements: 10.7
     */
    private function checkResponseTimeThreshold(string $useCase, string $providerName, int $responseTimeMs): void
    {
        $threshold = config('ai.response_time_threshold_ms', 30000); // Default: 30 detik

        if ($responseTimeMs > $threshold) {
            Log::warning('[UseCaseRouter] Response time melebihi threshold', [
                'use_case'         => $useCase,
                'provider'         => $providerName,
                'response_time_ms' => $responseTimeMs,
                'threshold_ms'     => $threshold,
                'response_time_s'  => round($responseTimeMs / 1000, 2),
                'threshold_s'      => round($threshold / 1000, 2),
            ]);
        }
    }
}
