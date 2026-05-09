<?php

namespace App\View\Composers;

use App\Models\AffiliateAuditLog;
use App\Models\AffiliateCommission;
use App\Models\ApprovalRequest;
use App\Models\DisciplinaryLetter;
use App\Models\EmployeeCertification;
use App\Models\ErpNotification;
use App\Models\ErrorLog;
use App\Models\OvertimeRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class SidebarBadgeComposer
{
    /**
     * Compose the view with sidebar badge counts
     *
     * This replaces 7+ individual database queries with 1 cached query
     */
    public function compose(View $view): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            $view->with('sidebarBadges', []);

            return;
        }

        // Cache badge counts for 60 seconds to prevent N+1 queries
        $cacheKey = "sidebar_badges_{$user->tenant_id}_{$user->id}";
        $badges = Cache::remember($cacheKey, 60, function () use ($user) {
            $badges = [];

            // Super Admin badges
            if ($user->isSuperAdmin()) {
                // Error logs (unresolved)
                $badges['error_logs'] = ErrorLog::where('is_resolved', false)->count();

                // Affiliate commissions (pending)
                $badges['affiliate_commissions'] = AffiliateCommission::where('status', 'pending')->count();

                // Affiliate fraud alerts (last 7 days)
                $badges['affiliate_fraud'] = AffiliateAuditLog::where('severity', 'fraud')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->count();
            }

            // Tenant badges
            if ($user->tenant_id) {
                // Approval requests (pending)
                $badges['approvals'] = ApprovalRequest::where('tenant_id', $user->tenant_id)
                    ->where('status', 'pending')
                    ->count();

                // Overtime requests (pending)
                $badges['overtime'] = OvertimeRequest::where('tenant_id', $user->tenant_id)
                    ->where('status', 'pending')
                    ->count();

                // Certifications expiring (next 90 days)
                $badges['certifications'] = EmployeeCertification::where('tenant_id', $user->tenant_id)
                    ->where('status', 'active')
                    ->whereNotNull('expiry_date')
                    ->where('expiry_date', '<=', now()->addDays(90))
                    ->count();

                // Disciplinary letters (active)
                $badges['disciplinary'] = DisciplinaryLetter::where('tenant_id', $user->tenant_id)
                    ->whereIn('status', ['issued', 'acknowledged'])
                    ->count();

                // Notifications (unread)
                $badges['notifications'] = ErpNotification::where('tenant_id', $user->tenant_id)
                    ->whereNull('read_at')
                    ->count();
            }

            return $badges;
        });

        $view->with('sidebarBadges', $badges);
    }

    /**
     * Clear sidebar badges cache
     * Call this when data changes
     */
    public static function clearCache(?int $tenantId = null, ?int $userId = null): void
    {
        if ($tenantId && $userId) {
            Cache::forget("sidebar_badges_{$tenantId}_{$userId}");
        } elseif ($tenantId) {
            // Clear all caches for tenant (pattern matching not supported, so we use tags)
            Cache::tags(["sidebar_badges_tenant_{$tenantId}"])->flush();
        }
    }
}
