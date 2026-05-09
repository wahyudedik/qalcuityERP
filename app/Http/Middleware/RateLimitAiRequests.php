<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Notifications\SuspiciousAiActivityNotification;
use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;

/**
 * RateLimitAiRequests — Per-tenant rate limiting for AI/chat endpoints.
 *
 * Two layers of protection:
 *
 * 1. Basic rate limit: 60 requests/minute per tenant (prevents burst abuse).
 *    Bug 1.28 Fix: keyed by tenant_id so each tenant has its own bucket.
 *
 * 2. Suspicious write-op detection (Requirement 9.6):
 *    If a tenant executes ≥ WRITE_OPS_THRESHOLD write operations within
 *    WRITE_OPS_WINDOW seconds, the tenant is throttled for THROTTLE_TTL seconds
 *    and all tenant admins receive a SuspiciousAiActivityNotification.
 *    A cooldown prevents duplicate notifications within NOTIFY_COOLDOWN seconds.
 */
class RateLimitAiRequests
{
    /** Max write ops in the detection window before throttling kicks in */
    private const WRITE_OPS_THRESHOLD = 10;

    /** Detection window in seconds */
    private const WRITE_OPS_WINDOW = 60;

    /** How long to throttle the tenant after suspicious activity (seconds) */
    private const THROTTLE_TTL = 300; // 5 minutes

    /** Cooldown between admin notifications for the same tenant (seconds) */
    private const NOTIFY_COOLDOWN = 600; // 10 minutes

    public function __construct(private RateLimiter $limiter) {}

    /**
     * Handle an incoming request.
     *
     * @param  string|null  $type  Pass 'write' from route middleware to count write ops.
     *                             Example: ->middleware('rate.ai:write')
     */
    public function handle(Request $request, Closure $next, string $type = 'read'): Response
    {
        $user = auth()->user();
        $tenantId = $user?->tenant_id;

        // Super admin and unauthenticated users bypass rate limiting
        if (! $user || ! $tenantId || $user->isSuperAdmin()) {
            return $next($request);
        }

        // --- Layer 1: Basic per-tenant rate limit (60 req/min) ---
        $basicKey = "ai_requests:{$tenantId}";

        if ($this->limiter->tooManyAttempts($basicKey, 60)) {
            return response()->json([
                'error' => 'too_many_requests',
                'message' => 'Terlalu banyak permintaan AI. Silakan coba beberapa saat lagi.',
                'retry_after' => $this->limiter->availableIn($basicKey),
            ], 429);
        }

        $this->limiter->hit($basicKey, 60); // 60-second window

        // --- Layer 2: Suspicious write-op pattern detection (Req 9.6) ---
        if ($type === 'write') {
            $throttleResponse = $this->handleWriteOp($request, $tenantId, $user);
            if ($throttleResponse !== null) {
                return $throttleResponse;
            }
        }

        return $next($request);
    }

    /**
     * Track a write operation and check for suspicious patterns.
     * Returns a throttle response if the tenant should be blocked, null otherwise.
     */
    private function handleWriteOp(Request $request, int $tenantId, $user): ?Response
    {
        // Check if tenant is already throttled due to prior suspicious activity
        if (Cache::has($this->throttleKey($tenantId))) {
            $retryAfter = Cache::get($this->throttleKey($tenantId).'_ttl', self::THROTTLE_TTL);

            return response()->json([
                'error' => 'suspicious_activity_throttled',
                'message' => 'Akun Anda dibatasi sementara karena terdeteksi pola aktivitas yang tidak biasa. '
                    .'Silakan coba lagi dalam beberapa menit atau hubungi administrator.',
                'retry_after' => $retryAfter,
            ], 429);
        }

        // Increment write-op counter for this tenant in the detection window
        $writeKey = $this->writeOpsKey($tenantId);
        $writeCount = $this->limiter->attempts($writeKey);

        $this->limiter->hit($writeKey, self::WRITE_OPS_WINDOW);
        $writeCount++; // reflect the hit we just made

        // Trigger throttle + notification if threshold exceeded
        if ($writeCount >= self::WRITE_OPS_THRESHOLD) {
            $this->throttleTenant($tenantId);
            $this->notifyAdmins($tenantId, $user, $writeCount);

            Log::warning('RateLimitAiRequests: Suspicious write-op pattern detected', [
                'tenant_id' => $tenantId,
                'user_id' => $user->id,
                'write_ops_count' => $writeCount,
                'window_seconds' => self::WRITE_OPS_WINDOW,
            ]);

            return response()->json([
                'error' => 'suspicious_activity_throttled',
                'message' => 'Terdeteksi pola aktivitas yang tidak biasa. Akun Anda dibatasi sementara. '
                    .'Administrator telah diberitahu. Silakan coba lagi dalam beberapa menit.',
                'retry_after' => self::THROTTLE_TTL,
            ], 429);
        }

        return null;
    }

    /**
     * Mark tenant as throttled for THROTTLE_TTL seconds.
     */
    private function throttleTenant(int $tenantId): void
    {
        $key = $this->throttleKey($tenantId);
        Cache::put($key, true, self::THROTTLE_TTL);
        Cache::put($key.'_ttl', self::THROTTLE_TTL, self::THROTTLE_TTL);
    }

    /**
     * Send notification to all tenant admins (with cooldown to prevent spam).
     */
    private function notifyAdmins(int $tenantId, $user, int $writeCount): void
    {
        $cooldownKey = "ai_suspicious_notified:{$tenantId}";

        // Skip if we already notified within the cooldown window
        if (Cache::has($cooldownKey)) {
            return;
        }

        try {
            $tenant = Tenant::with('admins')->find($tenantId);

            if (! $tenant || $tenant->admins->isEmpty()) {
                return;
            }

            $notification = new SuspiciousAiActivityNotification(
                tenantId: $tenantId,
                tenantName: $tenant->name,
                userId: $user->id,
                userName: $user->name,
                writeOpsCount: $writeCount,
                windowSeconds: self::WRITE_OPS_WINDOW,
                detectedAt: now()->format('d/m/Y H:i:s'),
            );

            foreach ($tenant->admins as $admin) {
                $admin->notify($notification);
            }

            // Set cooldown so we don't spam admins
            Cache::put($cooldownKey, true, self::NOTIFY_COOLDOWN);
        } catch (\Throwable $e) {
            Log::error('RateLimitAiRequests: Failed to notify admins of suspicious activity', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function writeOpsKey(int $tenantId): string
    {
        return "ai_write_ops:{$tenantId}";
    }

    private function throttleKey(int $tenantId): string
    {
        return "ai_suspicious_throttle:{$tenantId}";
    }
}
