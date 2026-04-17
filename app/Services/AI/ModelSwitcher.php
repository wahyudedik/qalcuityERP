<?php

namespace App\Services\AI;

use App\Events\AllModelsUnavailable;
use App\Exceptions\AllModelsUnavailableException;
use App\Models\SystemSetting;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Log;

/**
 * Manages persistent Gemini model state, fallback chain, cooldowns, and recovery.
 * Operates at application level (non-tenant-specific) since all tenants share one API key.
 *
 * Requirements: 1.1, 1.2, 1.3, 1.4, 1.5, 2.x, 3.x, 4.1, 7.2, 7.5, 7.6, 8.1, 8.3, 10.3
 */
class ModelSwitcher
{
    // Cache key constants — application-level, not scoped per tenant
    const CACHE_PREFIX = 'gemini_switcher:';
    const ACTIVE_MODEL_KEY = 'gemini_switcher:active_model';
    const UNAVAILABLE_PREFIX = 'gemini_switcher:unavailable:';
    const SWITCH_COUNT_KEY = 'gemini_switcher:switch_count:';

    /**
     * In-memory fallback state used when cache is unavailable.
     * Keyed by model name, value is the same structure as cache entries.
     *
     * @var array<string, mixed>
     */
    private array $inMemoryState = [];

    public function __construct(
        private readonly CacheRepository $cache,
    ) {}

    /**
     * Return the ordered fallback chain.
     * Reads from SystemSetting first, falls back to config.
     *
     * Requirements: 8.1, 8.3
     */
    public function getFallbackChain(): array
    {
        $fromSetting = SystemSetting::get('gemini_fallback_models');

        if (!empty($fromSetting)) {
            $decoded = is_array($fromSetting) ? $fromSetting : json_decode($fromSetting, true);
            if (is_array($decoded) && count($decoded) > 0) {
                return $decoded;
            }
        }

        return config('gemini.fallback_models', [config('gemini.model', 'gemini-2.5-flash')]);
    }

    /**
     * Return the currently active model.
     * If the primary model's cooldown has expired, return primary so it can be retried.
     * Defaults to config primary model if no switch has occurred.
     *
     * Requirements: 1.1, 1.2, 1.5, 4.1
     */
    public function getActiveModel(): string
    {
        $primary = config('gemini.model', 'gemini-2.5-flash');

        try {
            $active = $this->cache->get(self::ACTIVE_MODEL_KEY);
        } catch (\Throwable $e) {
            Log::warning('ModelSwitcher: cache unavailable in getActiveModel, using in-memory state.', ['error' => $e->getMessage()]);
            $active = $this->inMemoryState['active_model'] ?? null;
        }

        // No switch has occurred yet — use primary
        if ($active === null) {
            return $primary;
        }

        // If we're on a fallback, check if primary's cooldown has expired
        if ($active !== $primary && !$this->isUnavailable($primary)) {
            return $primary;
        }

        return $active;
    }

    /**
     * Mark a model as unavailable with a cooldown TTL based on the reason.
     * Falls back to in-memory state if cache throws.
     * Tracks switch frequency and logs a warning if >= 10 switches in 1 hour.
     *
     * Requirements: 3.1, 3.2, 3.3, 10.3
     */
    public function markUnavailable(string $model, string $reason): void
    {
        $cooldown = $this->cooldownForReason($reason);
        $now = Carbon::now();

        $entry = [
            'reason'     => $reason,
            'marked_at'  => $now->toIso8601String(),
            'expires_at' => $now->copy()->addSeconds($cooldown)->toIso8601String(),
        ];

        try {
            $this->cache->put(self::UNAVAILABLE_PREFIX . $model, $entry, $cooldown);
            $this->incrementSwitchCount();
        } catch (\Throwable $e) {
            Log::warning('ModelSwitcher: cache unavailable in markUnavailable, using in-memory fallback.', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            $this->inMemoryState['unavailable'][$model] = $entry;
            $this->incrementSwitchCountInMemory();
        }
    }

    /**
     * Return the next available model in the fallback chain after $failedModel.
     * Skips models currently in cooldown.
     * Throws AllModelsUnavailableException and dispatches event if all are exhausted.
     *
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.7
     */
    public function nextAvailableModel(string $failedModel): string
    {
        $chain = $this->getFallbackChain();

        foreach ($chain as $model) {
            if ($model === $failedModel) {
                continue;
            }

            if (!$this->isUnavailable($model)) {
                return $model;
            }
        }

        // All models exhausted — dispatch event and throw
        $unavailableModels = array_values(array_filter($chain, fn($m) => $this->isUnavailable($m)));

        AllModelsUnavailable::dispatch($unavailableModels);

        throw new AllModelsUnavailableException();
    }

    /**
     * Persist the active model to cache without TTL (persistent).
     *
     * Requirements: 1.2, 1.3
     */
    public function setActiveModel(string $model): void
    {
        try {
            // Store forever (no TTL)
            $this->cache->forever(self::ACTIVE_MODEL_KEY, $model);
        } catch (\Throwable $e) {
            Log::warning('ModelSwitcher: cache unavailable in setActiveModel, using in-memory fallback.', [
                'model' => $model,
                'error' => $e->getMessage(),
            ]);
            $this->inMemoryState['active_model'] = $model;
        }
    }

    /**
     * Return availability status for all models in the fallback chain.
     * Format: [['model' => '...', 'available' => bool, 'reason' => '...', 'recovers_at' => Carbon|null], ...]
     *
     * Requirements: 3.5, 3.6, 7.2, 7.5
     */
    public function getModelAvailability(): array
    {
        $chain = $this->getFallbackChain();
        $result = [];

        foreach ($chain as $model) {
            $entry = $this->getUnavailableEntry($model);

            if ($entry === null) {
                $result[] = [
                    'model'       => $model,
                    'available'   => true,
                    'reason'      => null,
                    'recovers_at' => null,
                ];
            } else {
                $result[] = [
                    'model'       => $model,
                    'available'   => false,
                    'reason'      => $entry['reason'],
                    'recovers_at' => Carbon::parse($entry['expires_at']),
                ];
            }
        }

        return $result;
    }

    /**
     * Reset all cooldown and active model cache keys.
     * Used by the Force Reset admin action.
     *
     * Requirements: 7.6
     */
    public function resetAll(): void
    {
        $chain = $this->getFallbackChain();

        try {
            $this->cache->forget(self::ACTIVE_MODEL_KEY);

            foreach ($chain as $model) {
                $this->cache->forget(self::UNAVAILABLE_PREFIX . $model);
            }
        } catch (\Throwable $e) {
            Log::warning('ModelSwitcher: cache unavailable in resetAll.', ['error' => $e->getMessage()]);
        }

        // Also clear in-memory state
        $this->inMemoryState = [];
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Check whether a model is currently in its cooldown window.
     */
    private function isUnavailable(string $model): bool
    {
        return $this->getUnavailableEntry($model) !== null;
    }

    /**
     * Retrieve the unavailability entry for a model, or null if available.
     * Checks cache first, then in-memory fallback.
     */
    private function getUnavailableEntry(string $model): ?array
    {
        try {
            $entry = $this->cache->get(self::UNAVAILABLE_PREFIX . $model);
        } catch (\Throwable) {
            $entry = $this->inMemoryState['unavailable'][$model] ?? null;
        }

        if (!is_array($entry)) {
            return null;
        }

        // Double-check expiry in case cache TTL was not honoured (e.g. in-memory path)
        if (isset($entry['expires_at']) && Carbon::now()->isAfter(Carbon::parse($entry['expires_at']))) {
            return null;
        }

        return $entry;
    }

    /**
     * Return the cooldown duration in seconds for a given reason.
     *
     * Reads from SystemSetting first (keys: gemini_rate_limit_cooldown, gemini_quota_cooldown),
     * falls back to config values. Requirements: 8.1, 8.2
     */
    private function cooldownForReason(string $reason): int
    {
        return match ($reason) {
            'quota_exceeded' => (int) (SystemSetting::get('gemini_quota_cooldown') ?? config('gemini.quota_cooldown', 3600)),
            default          => (int) (SystemSetting::get('gemini_rate_limit_cooldown') ?? config('gemini.rate_limit_cooldown', 60)),
        };
    }

    /**
     * Return the recovery check interval in seconds.
     * Reads from SystemSetting (key: gemini_recovery_check_interval),
     * falls back to config('gemini.recovery_check_interval', 300).
     *
     * Used by optional scheduled recovery probe. Requirements: 4.5, 8.1, 8.2
     */
    public function getRecoveryCheckInterval(): int
    {
        return (int) (SystemSetting::get('gemini_recovery_check_interval') ?? config('gemini.recovery_check_interval', 300));
    }

    /**
     * Increment the per-hour switch counter in cache and warn if threshold reached.
     * Requirements: 10.3
     */
    private function incrementSwitchCount(): void
    {
        $hourKey = self::SWITCH_COUNT_KEY . now()->format('YmdH');

        try {
            $count = (int) $this->cache->get($hourKey, 0) + 1;
            $this->cache->put($hourKey, $count, 3600);

            if ($count >= 10) {
                Log::warning('ModelSwitcher: high switch frequency detected.', [
                    'switches_in_last_hour' => $count,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('ModelSwitcher: could not increment switch count in cache.', ['error' => $e->getMessage()]);
            $this->incrementSwitchCountInMemory();
        }
    }

    /**
     * In-memory fallback for switch count tracking when cache is down.
     */
    private function incrementSwitchCountInMemory(): void
    {
        $this->inMemoryState['switch_count'] = ($this->inMemoryState['switch_count'] ?? 0) + 1;

        if ($this->inMemoryState['switch_count'] >= 10) {
            Log::warning('ModelSwitcher: high switch frequency detected (in-memory counter).', [
                'switches_this_cycle' => $this->inMemoryState['switch_count'],
            ]);
        }
    }
}
