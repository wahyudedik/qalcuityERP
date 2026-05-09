<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\ActivityLog;
use App\Models\ErpNotification;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserPointsLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    /**
     * Evaluate all achievements for a user after an activity.
     * Uses two bulk queries instead of one query per achievement (N+1 fix).
     */
    public static function evaluateAchievements(User $user, string $modelType, string $action): void
    {
        try {
            $achievements = Achievement::all();

            // One query to load all existing progress rows for this user
            $existingMap = UserAchievement::where('user_id', $user->id)
                ->get()
                ->keyBy('achievement_id');

            foreach ($achievements as $achievement) {
                $existing = $existingMap->get($achievement->id);

                // Skip if already earned
                if ($existing && $existing->isEarned()) {
                    continue;
                }

                $progress = self::calculateProgress($user, $achievement, $modelType, $action);

                if ($progress > 0) {
                    self::updateProgress($user, $achievement, $progress, $existing);
                }
            }
        } catch (\Throwable $e) {
            // Silently fail — gamification should never break core business logic
            Log::warning('Gamification evaluation failed: '.$e->getMessage());
        }
    }

    /**
     * Calculate current progress for an achievement
     */
    private static function calculateProgress(User $user, Achievement $achievement, string $modelType, string $action): int
    {
        return match ($achievement->requirement_type) {
            'count' => self::checkCountProgress($user, $achievement, $modelType),
            'streak' => self::checkStreakProgress($user, $achievement),
            default => 0,
        };
    }

    /**
     * Check count-based achievement progress
     */
    private static function checkCountProgress(User $user, Achievement $achievement, string $currentModel): int
    {
        // For AI explorer — count AI actions
        if ($achievement->requirement_action === 'ai_action') {
            return ActivityLog::where('user_id', $user->id)
                ->where('tenant_id', $user->tenant_id)
                ->where('is_ai_action', true)
                ->count();
        }

        // For model-based achievements
        if ($achievement->requirement_model) {
            $modelClass = $achievement->requirement_model;
            if (! class_exists($modelClass)) {
                return 0;
            }

            $query = $modelClass::query();

            // Try user_id first, then created_by, then tenant-wide
            if (in_array('user_id', (new $modelClass)->getFillable())) {
                $query->where('user_id', $user->id);
            } elseif (in_array('created_by', (new $modelClass)->getFillable())) {
                $query->where('created_by', $user->id);
            } else {
                $query->where('tenant_id', $user->tenant_id);
            }

            return $query->count();
        }

        return 0;
    }

    /**
     * Check streak-based achievement progress
     */
    private static function checkStreakProgress(User $user, Achievement $achievement): int
    {
        if ($achievement->requirement_action === 'daily_login') {
            return self::calculateLoginStreak($user);
        }

        if ($achievement->requirement_action === 'no_low_stock') {
            return self::calculateNoLowStockStreak($user);
        }

        return 0;
    }

    /**
     * Calculate consecutive login days.
     * One grouped-by-date query instead of up to 60 individual EXISTS queries.
     */
    private static function calculateLoginStreak(User $user): int
    {
        // Fetch distinct activity dates for the last 60 days in a single query
        $dates = ActivityLog::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->where('created_at', '>=', now()->subDays(60)->startOfDay())
            ->selectRaw('DATE(created_at) as activity_date')
            ->distinct()
            ->orderByDesc('activity_date')
            ->pluck('activity_date')
            ->map(fn ($d) => Carbon::parse($d)->startOfDay())
            ->values()
            ->all();

        if (empty($dates) || ! Carbon::instance($dates[0])->isToday()) {
            return 0;
        }

        $streak = 1;
        for ($i = 1; $i < count($dates); $i++) {
            $expected = Carbon::instance($dates[$i - 1])->subDay()->startOfDay();
            if (Carbon::instance($dates[$i])->equalTo($expected)) {
                $streak++;
            } else {
                break;
            }
        }

        return $streak;
    }

    /**
     * Calculate days without low stock alert
     */
    private static function calculateNoLowStockStreak(User $user): int
    {
        $lastLowStock = ErpNotification::where('tenant_id', $user->tenant_id)
            ->where('type', 'low_stock')
            ->latest()
            ->first();

        if (! $lastLowStock) {
            // No low stock ever — count as 30 (full streak)
            return 30;
        }

        return (int) $lastLowStock->created_at->diffInDays(now());
    }

    /**
     * Update achievement progress and unlock if complete
     */
    private static function updateProgress(User $user, Achievement $achievement, int $progress, ?UserAchievement $existing): void
    {
        $userAchievement = $existing ?? UserAchievement::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'current_progress' => 0,
        ]);

        $userAchievement->current_progress = $progress;

        // Check if achievement is now complete
        if ($progress >= $achievement->requirement_value && ! $userAchievement->isEarned()) {
            $userAchievement->earned_at = now();
            $userAchievement->save();
            self::unlockAchievement($user, $achievement);
        } else {
            $userAchievement->save();
        }
    }

    /**
     * Unlock achievement — award points and notify
     */
    private static function unlockAchievement(User $user, Achievement $achievement): void
    {
        self::addPoints($user, $achievement->points, "Achievement: {$achievement->name}");

        // Create notification
        try {
            ErpNotification::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'type' => 'achievement_unlocked',
                'module' => 'gamification',
                'title' => "Achievement Unlocked: {$achievement->icon} {$achievement->name}",
                'body' => $achievement->description." (+{$achievement->points} poin)",
            ]);
        } catch (\Throwable $e) {
            // Notification failure should not break the flow
        }
    }

    /**
     * Add points to user and log
     */
    public static function addPoints(User $user, int $points, string $reason): void
    {
        DB::transaction(function () use ($user, $points, $reason) {
            UserPointsLog::create([
                'tenant_id' => $user->tenant_id,
                'user_id' => $user->id,
                'points' => $points,
                'reason' => $reason,
            ]);

            $user->increment('gamification_points', $points);
            $user->gamification_level = self::calculateLevel($user->gamification_points + $points);
            $user->save();
        });
    }

    /**
     * Calculate level from points (every 100 points = 1 level)
     */
    public static function calculateLevel(int $points): int
    {
        return max(1, (int) floor($points / 100) + 1);
    }

    /**
     * Get tenant leaderboard
     */
    public static function getLeaderboard(int $tenantId, int $limit = 10): Collection
    {
        return User::where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('gamification_points', '>', 0)
            ->orderByDesc('gamification_points')
            ->limit($limit)
            ->get(['id', 'name', 'email', 'role', 'avatar', 'gamification_points', 'gamification_level']);
    }

    /**
     * Get recent points log for a user (for points history page)
     */
    public static function getPointsHistory(User $user, int $limit = 30): Collection
    {
        return UserPointsLog::where('user_id', $user->id)
            ->where('tenant_id', $user->tenant_id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Trigger streak-based achievement check on login event.
     * Called from auth controllers after a successful login.
     */
    public static function onLogin(User $user): void
    {
        try {
            $existingMap = UserAchievement::where('user_id', $user->id)
                ->get()
                ->keyBy('achievement_id');

            $streakAchievements = Achievement::where('requirement_type', 'streak')
                ->where('requirement_action', 'daily_login')
                ->get();

            $streak = self::calculateLoginStreak($user);

            foreach ($streakAchievements as $achievement) {
                $existing = $existingMap->get($achievement->id);
                if ($existing && $existing->isEarned()) {
                    continue;
                }
                if ($streak > 0) {
                    self::updateProgress($user, $achievement, $streak, $existing);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Gamification onLogin failed: '.$e->getMessage());
        }
    }

    /**
     * Get user gamification stats
     */
    public static function getUserStats(User $user): array
    {
        $totalAchievements = Achievement::count();
        $earnedAchievements = UserAchievement::where('user_id', $user->id)
            ->whereNotNull('earned_at')
            ->count();

        // Calculate rank within tenant
        $rank = User::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->where('gamification_points', '>', $user->gamification_points)
            ->count() + 1;

        $totalUsers = User::where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->where('gamification_points', '>', 0)
            ->count();

        $currentLevel = self::calculateLevel($user->gamification_points);
        $pointsForCurrentLevel = ($currentLevel - 1) * 100;
        $progressToNext = $user->gamification_points - $pointsForCurrentLevel;
        $pointsNeeded = 100; // always 100 per level

        return [
            'total_points' => $user->gamification_points,
            'level' => $currentLevel,
            'rank' => $rank,
            'total_users' => max(1, $totalUsers),
            'earned_achievements' => $earnedAchievements,
            'total_achievements' => $totalAchievements,
            'progress_to_next_level' => $progressToNext,
            'points_needed_for_next' => $pointsNeeded,
            'progress_percent' => min(100, (int) round(($progressToNext / $pointsNeeded) * 100)),
            'recent_achievements' => UserAchievement::where('user_id', $user->id)
                ->whereNotNull('earned_at')
                ->with('achievement')
                ->latest('earned_at')
                ->limit(5)
                ->get(),
        ];
    }
}
