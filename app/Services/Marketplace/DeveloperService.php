<?php

namespace App\Services\Marketplace;

use App\Models\DeveloperAccount;
use App\Models\MarketplaceApp;
use App\Models\DeveloperEarning;
use App\Models\DeveloperPayout;
use Illuminate\Support\Str;

class DeveloperService
{
    /**
     * Register developer account
     */
    public function registerDeveloper(int $userId, array $data): DeveloperAccount
    {
        return DeveloperAccount::create([
            'user_id' => $userId,
            'company_name' => $data['company_name'] ?? null,
            'bio' => $data['bio'] ?? null,
            'website' => $data['website'] ?? null,
            'github_profile' => $data['github_profile'] ?? null,
            'skills' => $data['skills'] ?? [],
            'status' => 'active',
        ]);
    }

    /**
     * Submit new app
     */
    public function submitApp(int $developerId, array $data): MarketplaceApp
    {
        $slug = Str::slug($data['name']);

        // Check if slug exists
        $existingSlug = MarketplaceApp::where('slug', $slug)->first();
        if ($existingSlug) {
            $slug .= '-' . Str::random(5);
        }

        return MarketplaceApp::create([
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'version' => $data['version'] ?? '1.0.0',
            'developer_id' => $developerId,
            'category' => $data['category'],
            'screenshots' => $data['screenshots'] ?? [],
            'icon_url' => $data['icon_url'] ?? null,
            'price' => $data['price'] ?? 0.00,
            'pricing_model' => $data['pricing_model'] ?? 'one_time',
            'subscription_price' => $data['subscription_price'] ?? null,
            'subscription_period' => $data['subscription_period'] ?? null,
            'features' => $data['features'] ?? [],
            'requirements' => $data['requirements'] ?? [],
            'status' => 'pending',
            'documentation_url' => $data['documentation_url'] ?? null,
            'support_url' => $data['support_url'] ?? null,
            'repository_url' => $data['repository_url'] ?? null,
        ]);
    }

    /**
     * Update app
     */
    public function updateApp(int $appId, array $data): bool
    {
        try {
            $app = MarketplaceApp::findOrFail($appId);
            $app->update($data);

            return true;
        } catch (\Exception $e) {
            \Log::error('Update app failed', [
                'app_id' => $appId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Submit app for review
     */
    public function submitForReview(int $appId): bool
    {
        try {
            $app = MarketplaceApp::findOrFail($appId);
            $app->update(['status' => 'pending']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Submit for review failed', [
                'app_id' => $appId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Approve app (admin)
     */
    public function approveApp(int $appId): bool
    {
        try {
            $app = MarketplaceApp::findOrFail($appId);
            $app->update([
                'status' => 'published',
                'published_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Approve app failed', [
                'app_id' => $appId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Reject app (admin)
     */
    public function rejectApp(int $appId, string $reason): bool
    {
        try {
            $app = MarketplaceApp::findOrFail($appId);
            $app->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Reject app failed', [
                'app_id' => $appId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get developer apps
     */
    public function getDeveloperApps(int $developerId): array
    {
        return MarketplaceApp::where('developer_id', $developerId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get developer earnings summary
     */
    public function getEarningsSummary(int $developerId, ?string $period = null): array
    {
        $query = DeveloperEarning::where('developer_account_id', $developerId);

        if ($period) {
            if ($period === 'this_month') {
                $query->whereMonth('earned_date', now()->month)
                    ->whereYear('earned_date', now()->year);
            } elseif ($period === 'last_month') {
                $query->whereMonth('earned_date', now()->subMonth()->month)
                    ->whereYear('earned_date', now()->subMonth()->year);
            } elseif ($period === 'this_year') {
                $query->whereYear('earned_date', now()->year);
            }
        }

        $totalEarnings = $query->sum('amount');
        $platformFees = $query->sum('platform_fee');
        $netEarnings = $query->sum('net_earning');
        $transactionCount = $query->count();

        return [
            'total_earnings' => $totalEarnings,
            'platform_fees' => $platformFees,
            'net_earnings' => $netEarnings,
            'transaction_count' => $transactionCount,
            'period' => $period ?? 'all_time',
        ];
    }

    /**
     * Request payout
     */
    public function requestPayout(int $developerId, float $amount, string $payoutMethod, array $payoutDetails): DeveloperPayout
    {
        $developer = DeveloperAccount::where('user_id', $developerId)->firstOrFail();

        if ($amount > $developer->available_balance) {
            throw new \Exception('Insufficient balance');
        }

        $payout = DeveloperPayout::create([
            'developer_account_id' => $developer->id,
            'amount' => $amount,
            'currency' => 'IDR',
            'status' => 'pending',
            'payout_method' => $payoutMethod,
            'payout_details' => $payoutDetails,
        ]);

        // Deduct from available balance
        $developer->decrement('available_balance', $amount);

        return $payout;
    }

    /**
     * Process payout (admin)
     */
    public function processPayout(int $payoutId, string $referenceNumber): bool
    {
        try {
            $payout = DeveloperPayout::findOrFail($payoutId);
            $payout->update([
                'status' => 'completed',
                'reference_number' => $referenceNumber,
                'processed_at' => now(),
            ]);

            // Mark related earnings as paid
            DeveloperEarning::where('developer_account_id', $payout->developer_account_id)
                ->where('status', 'pending')
                ->update(['status' => 'paid']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Process payout failed', [
                'payout_id' => $payoutId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get developer dashboard data
     */
    public function getDashboardData(int $developerId): array
    {
        $developer = DeveloperAccount::where('user_id', $developerId)->firstOrFail();

        return [
            'profile' => $developer,
            'apps_count' => MarketplaceApp::where('developer_id', $developer->user_id)->count(),
            'total_downloads' => MarketplaceApp::where('developer_id', $developer->user_id)
                ->sum('download_count'),
            'average_rating' => MarketplaceApp::where('developer_id', $developer->user_id)
                ->avg('rating'),
            'earnings' => $this->getEarningsSummary($developer->id),
            'pending_payouts' => DeveloperPayout::where('developer_account_id', $developer->id)
                ->where('status', 'pending')
                ->count(),
        ];
    }
}
