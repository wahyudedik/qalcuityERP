<?php

namespace App\Services;

use App\Models\ErpNotification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Notifications\NotificationDigestEmail;
use Illuminate\Support\Facades\Log;

/**
 * NotificationDigestService - Generate and send notification digest emails.
 * 
 * Supports:
 * - Daily digest (summary of yesterday's notifications)
 * - Weekly digest (summary of last week's notifications)
 * - Custom grouping by module and priority
 */
class NotificationDigestService
{
    /**
     * Send daily digest to users who prefer daily frequency.
     * 
     * @return int Number of digests sent
     */
    public function sendDailyDigest(): int
    {
        $count = 0;
        $yesterday = now()->subDay();

        // Get users with daily digest preference
        $preferences = NotificationPreference::where('digest_frequency', 'daily')
            ->where('email', true)
            ->get();

        foreach ($preferences as $pref) {
            try {
                $user = User::find($pref->user_id);

                if (!$user || !$user->email) {
                    continue;
                }

                // Get notifications from yesterday
                $notifications = ErpNotification::where('user_id', $user->id)
                    ->whereBetween('created_at', [$yesterday->startOfDay(), $yesterday->endOfDay()])
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($notifications->isEmpty()) {
                    continue;
                }

                // Group notifications by module
                $groupedNotifications = $this->groupNotifications($notifications);

                // Send digest email
                $user->notify(new NotificationDigestEmail(
                    $groupedNotifications,
                    'daily',
                    $yesterday
                ));

                $count++;
                Log::info("Daily digest sent to {$user->email}");
            } catch (\Throwable $e) {
                Log::error("Failed to send daily digest to user {$pref->user_id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Send weekly digest to users who prefer weekly frequency.
     * 
     * @return int Number of digests sent
     */
    public function sendWeeklyDigest(): int
    {
        $count = 0;
        $lastWeekStart = now()->subWeek()->startOfWeek();
        $lastWeekEnd = now()->subWeek()->endOfWeek();

        // Get users with weekly digest preference
        $preferences = NotificationPreference::where('digest_frequency', 'weekly')
            ->where('email', true)
            ->get();

        foreach ($preferences as $pref) {
            try {
                $user = User::find($pref->user_id);

                if (!$user || !$user->email) {
                    continue;
                }

                // Get notifications from last week
                $notifications = ErpNotification::where('user_id', $user->id)
                    ->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd])
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($notifications->isEmpty()) {
                    continue;
                }

                // Group notifications by module
                $groupedNotifications = $this->groupNotifications($notifications);

                // Send digest email
                $user->notify(new NotificationDigestEmail(
                    $groupedNotifications,
                    'weekly',
                    $lastWeekStart,
                    $lastWeekEnd
                ));

                $count++;
                Log::info("Weekly digest sent to {$user->email}");
            } catch (\Throwable $e) {
                Log::error("Failed to send weekly digest to user {$pref->user_id}: " . $e->getMessage());
            }
        }

        return $count;
    }

    /**
     * Group notifications by module and calculate statistics.
     */
    protected function groupNotifications($notifications): array
    {
        $grouped = [
            'summary' => [
                'total' => $notifications->count(),
                'unread' => $notifications->whereNull('read_at')->count(),
                'modules' => [],
            ],
            'by_module' => [],
        ];

        foreach ($notifications->groupBy('module') as $module => $moduleNotifications) {
            $grouped['by_module'][$module] = [
                'count' => $moduleNotifications->count(),
                'unread' => $moduleNotifications->whereNull('read_at')->count(),
                'notifications' => $moduleNotifications->map(fn($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'created_at' => $n->created_at,
                    'read_at' => $n->read_at,
                    'data' => $n->data,
                ])->toArray(),
            ];

            $grouped['summary']['modules'][$module] = $moduleNotifications->count();
        }

        return $grouped;
    }

    /**
     * Get digest statistics for a user.
     */
    public function getUserDigestStats(int $userId, string $period = 'daily'): array
    {
        $query = ErpNotification::where('user_id', $userId);

        if ($period === 'daily') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'weekly') {
            $query->whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ]);
        }

        $notifications = $query->get();

        return [
            'total' => $notifications->count(),
            'unread' => $notifications->whereNull('read_at')->count(),
            'read' => $notifications->whereNotNull('read_at')->count(),
            'by_module' => $notifications->groupBy('module')->map->count()->toArray(),
        ];
    }

    /**
     * Send digest to specific user (on-demand).
     */
    public function sendUserDigest(int $userId, string $frequency = 'daily'): bool
    {
        try {
            $user = User::find($userId);

            if (!$user || !$user->email) {
                return false;
            }

            $dateRange = $this->getDateRange($frequency);

            $notifications = ErpNotification::where('user_id', $userId)
                ->whereBetween('created_at', $dateRange)
                ->orderBy('created_at', 'desc')
                ->get();

            if ($notifications->isEmpty()) {
                return false;
            }

            $groupedNotifications = $this->groupNotifications($notifications);

            $user->notify(new NotificationDigestEmail(
                $groupedNotifications,
                $frequency,
                $dateRange[0],
                $dateRange[1] ?? now()
            ));

            return true;
        } catch (\Throwable $e) {
            Log::error("Failed to send user digest: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get date range based on frequency.
     */
    protected function getDateRange(string $frequency): array
    {
        return match ($frequency) {
            'daily' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'weekly' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            default => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
        };
    }
}
