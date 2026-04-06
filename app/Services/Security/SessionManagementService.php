<?php

namespace App\Services\Security;

use App\Models\UserSession;
use Illuminate\Support\Facades\Request;

class SessionManagementService
{
    /**
     * Track user session
     */
    public function trackSession(int $userId, int $tenantId, string $sessionId): UserSession
    {
        // Detect device info
        $userAgent = Request::userAgent();
        $deviceInfo = $this->parseUserAgent($userAgent);

        // Get IP address
        $ipAddress = Request::ip();

        // Get location (simplified - would use geo-IP service in production)
        $location = $this->getLocationFromIp($ipAddress);

        return UserSession::create([
            'user_id' => $userId,
            'tenant_id' => $tenantId,
            'session_id' => $sessionId,
            'device_name' => $deviceInfo['device_name'],
            'device_type' => $deviceInfo['device_type'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'ip_address' => $ipAddress,
            'location' => $location,
            'user_agent' => $userAgent,
            'is_active' => true,
            'is_current' => true,
            'last_activity_at' => now(),
            'expires_at' => now()->addHours(24), // 24 hour sessions
        ]);
    }

    /**
     * Update session activity
     */
    public function updateActivity(string $sessionId): bool
    {
        try {
            $session = UserSession::where('session_id', $sessionId)->first();

            if ($session) {
                $session->update([
                    'last_activity_at' => now(),
                    'expires_at' => now()->addHours(24),
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Update session activity failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Terminate session
     */
    public function terminateSession(string $sessionId): bool
    {
        try {
            $session = UserSession::where('session_id', $sessionId)->first();

            if ($session) {
                $session->update([
                    'is_active' => false,
                    'is_current' => false,
                ]);

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Terminate session failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Terminate all sessions for user
     */
    public function terminateAllSessions(int $userId, string $excludeSessionId = null): int
    {
        try {
            $query = UserSession::where('user_id', $userId)
                ->where('is_active', true);

            if ($excludeSessionId) {
                $query->where('session_id', '!=', $excludeSessionId);
            }

            $count = $query->update([
                'is_active' => false,
                'is_current' => false,
            ]);

            return $count;
        } catch (\Exception $e) {
            \Log::error('Terminate all sessions failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }

    /**
     * Get active sessions for user
     */
    public function getActiveSessions(int $userId): array
    {
        return UserSession::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('last_activity_at', 'desc')
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'session_id' => $session->session_id,
                    'device_name' => $session->device_name,
                    'device_type' => $session->device_type,
                    'browser' => $session->browser,
                    'platform' => $session->platform,
                    'ip_address' => $session->ip_address,
                    'location' => $session->location,
                    'is_current' => $session->is_current,
                    'last_activity_at' => $session->last_activity_at,
                    'expires_at' => $session->expires_at,
                ];
            })
            ->toArray();
    }

    /**
     * Check if session is expired
     */
    public function isSessionExpired(string $sessionId): bool
    {
        $session = UserSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return true;
        }

        return $session->expires_at < now() || !$session->is_active;
    }

    /**
     * Clean expired sessions
     */
    public function cleanExpiredSessions(): int
    {
        return UserSession::where('expires_at', '<', now())
            ->orWhere('is_active', false)
            ->delete();
    }

    /**
     * Parse user agent string
     */
    protected function parseUserAgent(?string $userAgent): array
    {
        if (!$userAgent) {
            return [
                'device_name' => 'Unknown Device',
                'device_type' => 'desktop',
                'browser' => 'Unknown',
                'platform' => 'Unknown',
            ];
        }

        // Simple detection (in production, use mobiledetect/mobiledetectlib)
        $deviceType = 'desktop';
        $platform = 'Unknown';
        $browser = 'Unknown';

        if (stripos($userAgent, 'Mobile') !== false) {
            $deviceType = 'mobile';
        } elseif (stripos($userAgent, 'Tablet') !== false) {
            $deviceType = 'tablet';
        }

        if (stripos($userAgent, 'Windows') !== false) {
            $platform = 'Windows';
        } elseif (stripos($userAgent, 'Mac') !== false) {
            $platform = 'macOS';
        } elseif (stripos($userAgent, 'Android') !== false) {
            $platform = 'Android';
        } elseif (stripos($userAgent, 'iPhone') !== false) {
            $platform = 'iOS';
        }

        if (stripos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (stripos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (stripos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        }

        return [
            'device_name' => "{$browser} on {$platform}",
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Get location from IP (simplified)
     */
    protected function getLocationFromIp(?string $ipAddress): ?string
    {
        if (!$ipAddress) {
            return null;
        }

        // In production, use geo-IP service like ipapi.co or maxmind/geoip2
        // For now, return null or implement basic lookup
        return null;
    }
}
