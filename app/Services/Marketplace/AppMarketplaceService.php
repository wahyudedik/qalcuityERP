<?php

namespace App\Services\Marketplace;

use App\Models\MarketplaceApp;
use App\Models\AppInstallation;
use App\Models\AppReview;
use Illuminate\Support\Str;

class AppMarketplaceService
{
    /**
     * List all published apps with filters
     */
    public function listApps(array $filters = []): array
    {
        $query = MarketplaceApp::where('status', 'published')
            ->with(['developer', 'reviews']);

        // Apply filters
        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        if (!empty($filters['price_type'])) {
            if ($filters['price_type'] === 'free') {
                $query->where('price', 0);
            } elseif ($filters['price_type'] === 'paid') {
                $query->where('price', '>', 0);
            }
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'published_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $perPage = $filters['per_page'] ?? 20;
        $apps = $query->paginate($perPage);

        return $apps->toArray();
    }

    /**
     * Get app details by slug
     */
    public function getAppBySlug(string $slug): ?MarketplaceApp
    {
        return MarketplaceApp::where('slug', $slug)
            ->where('status', 'published')
            ->with(['developer', 'reviews.user'])
            ->first();
    }

    /**
     * Install app to tenant
     */
    public function installApp(int $appId, int $tenantId, int $userId): array
    {
        try {
            $app = MarketplaceApp::findOrFail($appId);

            // Check if already installed
            $existing = AppInstallation::where('marketplace_app_id', $appId)
                ->where('tenant_id', $tenantId)
                ->first();

            if ($existing) {
                return ['success' => false, 'message' => 'App already installed'];
            }

            // Create installation
            $installation = AppInstallation::create([
                'marketplace_app_id' => $appId,
                'tenant_id' => $tenantId,
                'installation_id' => Str::uuid()->toString(),
                'status' => 'active',
                'configuration' => [],
                'permissions' => $app->requirements ?? [],
                'installed_at' => now(),
                'expires_at' => $this->calculateExpiry($app),
            ]);

            // Increment download count
            $app->increment('download_count');

            // Record earning if paid app
            if ($app->price > 0) {
                $this->recordEarning($app, $installation, $app->price, 'sale');
            }

            return [
                'success' => true,
                'installation' => $installation,
                'message' => 'App installed successfully',
            ];
        } catch (\Exception $e) {
            \Log::error('Install app failed', [
                'app_id' => $appId,
                'tenant_id' => $tenantId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Uninstall app
     */
    public function uninstallApp(int $installationId): bool
    {
        try {
            $installation = AppInstallation::findOrFail($installationId);
            $installation->update(['status' => 'uninstalled']);

            return true;
        } catch (\Exception $e) {
            \Log::error('Uninstall app failed', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Configure app
     */
    public function configureApp(int $installationId, array $configuration): bool
    {
        try {
            $installation = AppInstallation::findOrFail($installationId);
            $installation->update(['configuration' => $configuration]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Configure app failed', [
                'installation_id' => $installationId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Submit review
     */
    public function submitReview(int $appId, int $userId, int $tenantId, int $rating, ?string $review = null, array $pros = [], array $cons = []): array
    {
        try {
            // Check if user has installed the app
            $hasInstalled = AppInstallation::where('marketplace_app_id', $appId)
                ->where('tenant_id', $tenantId)
                ->exists();

            $reviewModel = AppReview::updateOrCreate(
                [
                    'marketplace_app_id' => $appId,
                    'user_id' => $userId,
                ],
                [
                    'tenant_id' => $tenantId,
                    'rating' => $rating,
                    'review' => $review,
                    'pros' => $pros,
                    'cons' => $cons,
                    'verified_purchase' => $hasInstalled,
                    'is_approved' => true, // Auto-approve for now
                ]
            );

            // Recalculate average rating
            $this->recalculateRating($appId);

            return ['success' => true, 'review' => $reviewModel];
        } catch (\Exception $e) {
            \Log::error('Submit review failed', [
                'app_id' => $appId,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get tenant's installed apps
     */
    public function getTenantApps(int $tenantId): array
    {
        return AppInstallation::where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->with(['app.developer'])
            ->get()
            ->map(function ($installation) {
                return [
                    'installation_id' => $installation->installation_id,
                    'app' => $installation->app,
                    'configuration' => $installation->configuration,
                    'installed_at' => $installation->installed_at,
                    'expires_at' => $installation->expires_at,
                ];
            })
            ->toArray();
    }

    /**
     * Recalculate app rating
     */
    protected function recalculateRating(int $appId): void
    {
        $app = MarketplaceApp::find($appId);

        if ($app) {
            $averageRating = AppReview::where('marketplace_app_id', $appId)
                ->where('is_approved', true)
                ->avg('rating');

            $reviewCount = AppReview::where('marketplace_app_id', $appId)
                ->where('is_approved', true)
                ->count();

            $app->update([
                'rating' => round($averageRating ?? 0, 2),
                'review_count' => $reviewCount,
            ]);
        }
    }

    /**
     * Calculate subscription expiry
     */
    protected function calculateExpiry(MarketplaceApp $app): ?\Carbon\Carbon
    {
        if ($app->pricing_model === 'subscription' && $app->subscription_period) {
            if ($app->subscription_period === 'monthly') {
                return now()->addMonth();
            } elseif ($app->subscription_period === 'yearly') {
                return now()->addYear();
            }
        }

        return null; // One-time purchase or free
    }

    /**
     * Record developer earning
     */
    protected function recordEarning(MarketplaceApp $app, AppInstallation $installation, float $amount, string $type): void
    {
        $platformFeePercentage = 0.20; // 20% platform fee
        $platformFee = $amount * $platformFeePercentage;
        $netEarning = $amount - $platformFee;

        \App\Models\DeveloperEarning::create([
            'developer_account_id' => $app->developer_id,
            'marketplace_app_id' => $app->id,
            'installation_id' => $installation->id,
            'amount' => $amount,
            'platform_fee' => $platformFee,
            'net_earning' => $netEarning,
            'currency' => 'IDR',
            'type' => $type,
            'earned_date' => now(),
            'status' => 'pending',
        ]);

        // Update developer balance
        $developerAccount = \App\Models\DeveloperAccount::where('user_id', $app->developer_id)->first();
        if ($developerAccount) {
            $developerAccount->increment('total_earnings', $amount);
            $developerAccount->increment('available_balance', $netEarning);
        }
    }
}
