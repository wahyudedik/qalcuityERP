<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\UserAchievement;
use App\Services\GamificationService;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $stats = GamificationService::getUserStats($user);
        $leaderboard = GamificationService::getLeaderboard($user->tenant_id, 10);

        $achievements = Achievement::ordered()->get();
        $userAchievements = UserAchievement::where('user_id', $user->id)
            ->pluck('current_progress', 'achievement_id')
            ->toArray();
        $earnedIds = UserAchievement::where('user_id', $user->id)
            ->whereNotNull('earned_at')
            ->pluck('achievement_id')
            ->toArray();

        // Group achievements by category
        $grouped = $achievements->groupBy('category');

        return view('gamification.index', compact(
            'stats',
            'leaderboard',
            'achievements',
            'grouped',
            'userAchievements',
            'earnedIds',
            'user'
        ));
    }

    public function achievements(Request $request)
    {
        $user = $request->user();
        $category = $request->get('category');

        $query = Achievement::ordered();
        if ($category) {
            $query->byCategory($category);
        }
        $achievements = $query->get();

        $userAchievements = UserAchievement::where('user_id', $user->id)
            ->pluck('current_progress', 'achievement_id')
            ->toArray();
        $earnedIds = UserAchievement::where('user_id', $user->id)
            ->whereNotNull('earned_at')
            ->pluck('achievement_id')
            ->toArray();

        $stats = GamificationService::getUserStats($user);
        $grouped = $achievements->groupBy('category');

        return view('gamification.index', compact(
            'stats',
            'achievements',
            'grouped',
            'userAchievements',
            'earnedIds',
            'user',
            'category'
        ))->with('leaderboard', collect())
            ->with('showAchievementsOnly', true);
    }

    public function leaderboard(Request $request)
    {
        $user = $request->user();
        $leaderboard = GamificationService::getLeaderboard($user->tenant_id, 50);
        $stats = GamificationService::getUserStats($user);

        return view('gamification.index', compact('leaderboard', 'stats', 'user'))
            ->with('achievements', collect())
            ->with('grouped', collect())
            ->with('userAchievements', [])
            ->with('earnedIds', [])
            ->with('showLeaderboardOnly', true);
    }
}
