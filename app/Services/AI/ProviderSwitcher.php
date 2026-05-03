<?php

namespace App\Services\AI;

use App\Contracts\AiProvider;
use App\Events\AllModelsUnavailable;
use App\Exceptions\AllProvidersUnavailableException;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;

/**
 * Manages cross-provider cooldowns and fallback ordering.
 * Operates at the provider level (not model level) — the cross-provider
 * equivalent of ModelSwitcher.
 *
 * Cache keys:
 *   ai_provider_switcher:unavailable:{provider_name}  → cooldown entry
 *
 * Requirements: 3.2–3.8, 9.1
 */
class ProviderSwitcher
{
    // Cache key constants
    const UNAVAILABLE_PREFIX = 'ai_provider_switcher:unavailable:';

    /**
     * In-memory fallback state used when cache is unavailable.
     *
     * @var array<string, mixed>
     */
    private array $inMemoryState = [];

    public function __construct(
        private readonly CacheRepository $cache,
    ) {}

    /**
     * Mark a provider as unavailable with a cooldown TTL based on the reason.
     *
     * Cooldown durations:
     *   - rate_limit     → config('ai.providers.{provider}.rate_limit_cooldown', 60)
     *   - quota_exceeded → config('ai.providers.{provider}.quota_cooldown', 3600)
     *   - server_error   → same as rate_limit (60 seconds)
     *
     * Requirements: 3.6
     */
    public function markProviderUnavailable(string $provider, string $reason): void
    {
        $cooldown = $this->cooldownForReason($provider, $reason);
        $now = Carbon::now();

        $entry = [
            'reason'     => $reason,
            'marked_at'  => $now->toIso8601String(),
            'expires_at' => $now->copy()->addSeconds($cooldown)->toIso8601String(),
        ];

        try {
            $this->cache->put(self::UNAVAILABLE_PREFIX . $provider, $entry, $cooldown);
        } catch (\Throwable $e) {
            Log::warning('ProviderSwitcher: cache unavailable in markProviderUnavailable, using in-memory fallback.', [
                'provider' => $provider,
                'reason'   => $reason,
                'error'    => $e->getMessage(),
            ]);
            $this->inMemoryState['unavailable'][$provider] = $entry;
        }
    }

    /**
     * Check whether a provider is currently available (not in cooldown).
     *
     * Requirements: 3.5, 3.7
     */
    public function isProviderAvailable(string $provider): bool
    {
        return $this->getUnavailableEntry($provider) === null;
    }

    /**
     * Return the next available provider instance from the fallback order.
     *
     * In 'single' mode: only the first provider in $fallbackOrder is considered,
     * no fallback is attempted.
     *
     * In 'failover' mode: iterate through $fallbackOrder, skip providers in cooldown,
     * return the first available one.
     *
     * Throws AllProvidersUnavailableException and dispatches AllModelsUnavailable event
     * if all providers are in cooldown.
     *
     * @param  array<string>      $fallbackOrder     Ordered list of provider names, e.g. ['gemini', 'anthropic']
     * @param  array<string, AiProvider> $providerInstances Map of provider name → AiProvider instance
     * @return AiProvider
     *
     * @throws AllProvidersUnavailableException
     *
     * Requirements: 3.2, 3.3, 3.7, 3.8
     */
    public function getNextAvailableProvider(array $fallbackOrder, array $providerInstances): AiProvider
    {
        $mode = config('ai.mode', 'failover');

        if ($mode === 'single') {
            // Single mode: only use the first provider, no fallback
            $primaryName = $fallbackOrder[0] ?? null;

            if ($primaryName === null || !isset($providerInstances[$primaryName])) {
                $this->dispatchAllUnavailableEvent($fallbackOrder);
                throw new AllProvidersUnavailableException($fallbackOrder);
            }

            if (!$this->isProviderAvailable($primaryName)) {
                $this->dispatchAllUnavailableEvent([$primaryName]);
                throw new AllProvidersUnavailableException([$primaryName]);
            }

            return $providerInstances[$primaryName];
        }

        // Failover mode: try each provider in order, skip those in cooldown
        $unavailableProviders = [];

        foreach ($fallbackOrder as $providerName) {
            if (!isset($providerInstances[$providerName])) {
                // Provider instance not registered — skip silently
                Log::debug('ProviderSwitcher: provider instance not found, skipping.', [
                    'provider' => $providerName,
                ]);
                continue;
            }

            if ($this->isProviderAvailable($providerName)) {
                return $providerInstances[$providerName];
            }

            $unavailableProviders[] = $providerName;
        }

        // All providers exhausted — dispatch event and throw
        $this->dispatchAllUnavailableEvent($unavailableProviders);

        throw new AllProvidersUnavailableException($unavailableProviders);
    }

    /**
     * Return availability status for all providers in the given list.
     * Format: [['provider' => '...', 'available' => bool, 'reason' => '...', 'recovers_at' => Carbon|null], ...]
     *
     * Requirements: 3.5, 3.6
     */
    public function getProviderAvailability(array $providers): array
    {
        $result = [];

        foreach ($providers as $provider) {
            $entry = $this->getUnavailableEntry($provider);

            if ($entry === null) {
                $result[] = [
                    'provider'    => $provider,
                    'available'   => true,
                    'reason'      => null,
                    'recovers_at' => null,
                ];
            } else {
                $result[] = [
                    'provider'    => $provider,
                    'available'   => false,
                    'reason'      => $entry['reason'],
                    'recovers_at' => Carbon::parse($entry['expires_at']),
                ];
            }
        }

        return $result;
    }

    /**
     * Clear cooldown for a specific provider (e.g. for admin force-reset).
     */
    public function resetProvider(string $provider): void
    {
        try {
            $this->cache->forget(self::UNAVAILABLE_PREFIX . $provider);
        } catch (\Throwable $e) {
            Log::warning('ProviderSwitcher: cache unavailable in resetProvider.', [
                'provider' => $provider,
                'error'    => $e->getMessage(),
            ]);
        }

        unset($this->inMemoryState['unavailable'][$provider]);
    }

    /**
     * Clear cooldown for all providers in the given list.
     */
    public function resetAll(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->resetProvider($provider);
        }
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Retrieve the unavailability entry for a provider, or null if available.
     * Checks cache first, then in-memory fallback.
     */
    private function getUnavailableEntry(string $provider): ?array
    {
        try {
            $entry = $this->cache->get(self::UNAVAILABLE_PREFIX . $provider);
        } catch (\Throwable) {
            $entry = $this->inMemoryState['unavailable'][$provider] ?? null;
        }

        if (!is_array($entry)) {
            return null;
        }

        // Double-check expiry in case cache TTL was not honoured (e.g. in-memory path)
        if (isset($entry['expires_at']) && Carbon::now()->isAfter(Carbon::parse($entry['expires_at']))) {
            // Expired — clean up in-memory state if present
            unset($this->inMemoryState['unavailable'][$provider]);
            return null;
        }

        return $entry;
    }

    /**
     * Return the cooldown duration in seconds for a given provider and reason.
     *
     * Reads from config('ai.providers.{provider}.rate_limit_cooldown') or
     * config('ai.providers.{provider}.quota_cooldown').
     *
     * Requirements: 3.6
     */
    private function cooldownForReason(string $provider, string $reason): int
    {
        return match ($reason) {
            'quota_exceeded' => (int) config("ai.providers.{$provider}.quota_cooldown", 3600),
            default          => (int) config("ai.providers.{$provider}.rate_limit_cooldown", 60),
        };
    }

    /**
     * Dispatch the AllModelsUnavailable event to notify listeners that all
     * AI providers are currently unavailable.
     *
     * Requirements: 7.4
     */
    private function dispatchAllUnavailableEvent(array $unavailableProviders): void
    {
        try {
            AllModelsUnavailable::dispatch($unavailableProviders);
        } catch (\Throwable $e) {
            Log::error('ProviderSwitcher: failed to dispatch AllModelsUnavailable event.', [
                'error'               => $e->getMessage(),
                'unavailable_providers' => $unavailableProviders,
            ]);
        }
    }
}
