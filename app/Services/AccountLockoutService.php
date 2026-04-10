<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AccountLockoutService
{
    /**
     * Maximum failed login attempts before lockout
     */
    protected int $maxAttempts;

    /**
     * Lockout duration in seconds (default: 15 minutes)
     */
    protected int $lockoutDuration;

    /**
     * Cache key prefix
     */
    protected string $cachePrefix = 'account_lockout:';

    public function __construct()
    {
        $this->maxAttempts = config('security.lockout.max_attempts', 5);
        $this->lockoutDuration = config('security.lockout.duration_minutes', 15) * 60;
    }

    /**
     * Record a failed login attempt
     */
    public function recordFailedLogin(User $user): void
    {
        $user->increment('failed_login_attempts');
        $user->update(['last_failed_login' => now()]);

        $attempts = $user->fresh()->failed_login_attempts;

        Log::warning('Failed login attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'attempt' => $attempts,
            'max_attempts' => $this->maxAttempts,
        ]);

        // Lock account if max attempts reached
        if ($attempts >= $this->maxAttempts) {
            $this->lockAccount($user);
        }
    }

    /**
     * Reset failed login attempts after successful login
     */
    public function resetFailedAttempts(User $user): void
    {
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
            'last_failed_login' => null,
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);

        // Clear cache
        Cache::forget($this->cachePrefix . $user->id);

        Log::info('Login successful, attempts reset', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Check if account is locked
     */
    public function isLocked(User $user): bool
    {
        // Check cache first
        $cached = Cache::get($this->cachePrefix . $user->id);
        if ($cached !== null) {
            return (bool) $cached;
        }

        // Check database
        if ($user->locked_until && $user->locked_until->isFuture()) {
            Cache::put(
                $this->cachePrefix . $user->id,
                true,
                $user->locked_until
            );
            return true;
        }

        // Auto-unlock if lockout period has passed
        if ($user->locked_until && $user->locked_until->isPast()) {
            $user->update([
                'locked_until' => null,
                'failed_login_attempts' => 0,
            ]);
            Cache::forget($this->cachePrefix . $user->id);
        }

        return false;
    }

    /**
     * Lock user account
     */
    public function lockAccount(User $user, ?int $duration = null): void
    {
        $duration = $duration ?? $this->lockoutDuration;
        $lockedUntil = now()->addSeconds($duration);

        $user->update([
            'locked_until' => $lockedUntil,
        ]);

        // Cache lockout status
        Cache::put(
            $this->cachePrefix . $user->id,
            true,
            $lockedUntil
        );

        Log::alert('Account locked due to failed attempts', [
            'user_id' => $user->id,
            'email' => $user->email,
            'locked_until' => $lockedUntil->toDateTimeString(),
            'failed_attempts' => $user->failed_login_attempts,
        ]);

        // Send notification email
        try {
            $this->sendLockoutNotification($user, $lockedUntil);
        } catch (\Exception $e) {
            Log::error('Failed to send lockout notification: ' . $e->getMessage());
        }
    }

    /**
     * Manually unlock user account
     */
    public function unlockAccount(User $user): void
    {
        $user->update([
            'locked_until' => null,
            'failed_login_attempts' => 0,
            'last_failed_login' => null,
        ]);

        Cache::forget($this->cachePrefix . $user->id);

        Log::info('Account manually unlocked', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);
    }

    /**
     * Get remaining lockout time in seconds
     */
    public function getRemainingLockoutTime(User $user): int
    {
        if (!$user->locked_until || $user->locked_until->isPast()) {
            return 0;
        }

        return now()->diffInSeconds($user->locked_until);
    }

    /**
     * Get remaining lockout time formatted
     */
    public function getFormattedLockoutTime(User $user): string
    {
        $seconds = $this->getRemainingLockoutTime($user);

        if ($seconds === 0) {
            return 'Account is not locked';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return "{$minutes} minute" . ($minutes > 1 ? 's' : '') . " {$remainingSeconds} second" . ($remainingSeconds > 1 ? 's' : '');
        }

        return "{$remainingSeconds} second" . ($remainingSeconds > 1 ? 's' : '');
    }

    /**
     * Get failed attempts count
     */
    public function getFailedAttempts(User $user): int
    {
        return $user->failed_login_attempts ?? 0;
    }

    /**
     * Check if user should be warned about failed attempts
     */
    public function shouldWarn(User $user): bool
    {
        $warningThreshold = config('security.lockout.warning_threshold', 3);
        return $user->failed_login_attempts >= $warningThreshold && $user->failed_login_attempts < $this->maxAttempts;
    }

    /**
     * Send lockout notification email
     */
    protected function sendLockoutNotification(User $user, $lockedUntil): void
    {
        // You can implement this with your preferred email notification
        // For now, we'll log it
        Log::info('Lockout notification would be sent', [
            'user_id' => $user->id,
            'email' => $user->email,
            'locked_until' => $lockedUntil->toDateTimeString(),
        ]);

        // Example: Send email
        // Mail::to($user->email)->send(new AccountLockoutNotification($user, $lockedUntil));
    }

    /**
     * Get lockout status info
     */
    public function getLockoutStatus(User $user): array
    {
        return [
            'is_locked' => $this->isLocked($user),
            'failed_attempts' => $this->getFailedAttempts($user),
            'max_attempts' => $this->maxAttempts,
            'remaining_time_seconds' => $this->getRemainingLockoutTime($user),
            'remaining_time_formatted' => $this->getFormattedLockoutTime($user),
            'should_warn' => $this->shouldWarn($user),
            'locked_until' => $user->locked_until?->toDateTimeString(),
        ];
    }
}
