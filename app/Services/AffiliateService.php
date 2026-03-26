<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateAuditLog;
use App\Models\AffiliateCommission;
use App\Models\AffiliateReferral;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AffiliateService
{
    /**
     * Track referral when tenant registers with affiliate code.
     * Includes fraud checks.
     */
    public function trackReferral(Tenant $tenant, string $code): bool
    {
        // 1 tenant = max 1 affiliate
        if (AffiliateReferral::where('tenant_id', $tenant->id)->exists()) {
            return false;
        }

        $affiliate = Affiliate::where('code', $code)->where('is_active', true)->first();
        if (!$affiliate) return false;

        // FRAUD CHECK: affiliate cannot refer their own demo tenant
        if ($affiliate->demo_tenant_id === $tenant->id) {
            AffiliateAuditLog::log($affiliate->id, 'fraud_self_referral', "Attempted self-referral to own demo tenant #{$tenant->id}", 'fraud', [
                'tenant_id' => $tenant->id, 'tenant_name' => $tenant->name,
            ]);
            return false;
        }

        // FRAUD CHECK: same IP as affiliate user
        $affiliateIp = DB::table('activity_logs')->where('user_id', $affiliate->user_id)->latest()->value('ip_address');
        $currentIp = request()->ip();
        $sameIp = $affiliateIp && $affiliateIp === $currentIp;

        AffiliateReferral::create([
            'affiliate_id' => $affiliate->id,
            'tenant_id'    => $tenant->id,
            'referred_at'  => now(),
            'source'       => 'link',
        ]);

        $tenant->update(['referred_by_code' => $code]);

        $severity = $sameIp ? 'warning' : 'info';
        AffiliateAuditLog::log($affiliate->id, 'referral_created',
            "Referral: {$tenant->name} (#{$tenant->id})" . ($sameIp ? ' ⚠ SAME IP as affiliate' : ''),
            $severity, [
                'tenant_id' => $tenant->id, 'tenant_name' => $tenant->name,
                'ip' => $currentIp, 'same_ip' => $sameIp,
            ]);

        return true;
    }

    /**
     * Create commission when a referred tenant makes a payment.
     * Includes fraud checks.
     */
    public function createCommission(Tenant $tenant, SubscriptionPayment $payment): void
    {
        $referral = AffiliateReferral::where('tenant_id', $tenant->id)->first();
        if (!$referral) return;

        $affiliate = $referral->affiliate;
        if (!$affiliate || !$affiliate->is_active) return;

        // Prevent duplicate commission for same payment
        if (AffiliateCommission::where('subscription_payment_id', $payment->id)->exists()) return;

        // FRAUD CHECK: commission for demo tenant
        if ($affiliate->demo_tenant_id === $tenant->id) {
            AffiliateAuditLog::log($affiliate->id, 'fraud_demo_commission',
                "Blocked commission for own demo tenant #{$tenant->id}", 'fraud', [
                    'tenant_id' => $tenant->id, 'payment_id' => $payment->id,
                ]);
            return;
        }

        // FRAUD CHECK: rapid referrals (>5 in 24h)
        $recentReferrals = AffiliateReferral::where('affiliate_id', $affiliate->id)
            ->where('referred_at', '>=', now()->subDay())->count();

        $paymentAmount = (float) $payment->amount;
        $rate = (float) $affiliate->commission_rate;
        $commission = round($paymentAmount * $rate / 100, 2);

        if ($commission <= 0) return;

        $commissionRecord = AffiliateCommission::create([
            'affiliate_id'            => $affiliate->id,
            'tenant_id'               => $tenant->id,
            'subscription_payment_id' => $payment->id,
            'plan_name'               => $payment->plan->name ?? 'Unknown',
            'payment_amount'          => $paymentAmount,
            'commission_rate'         => $rate,
            'commission_amount'       => $commission,
            'status'                  => 'pending',
        ]);

        $severity = $recentReferrals > 5 ? 'warning' : 'info';
        AffiliateAuditLog::log($affiliate->id, 'commission_created',
            "Commission Rp " . number_format($commission, 0, ',', '.') . " from {$tenant->name}"
            . ($recentReferrals > 5 ? " ⚠ {$recentReferrals} referrals in 24h" : ''),
            $severity, [
                'commission_id' => $commissionRecord->id,
                'tenant_id' => $tenant->id, 'amount' => $commission,
                'recent_referrals_24h' => $recentReferrals,
            ]);
    }

    /**
     * Create demo tenant + user for affiliate.
     */
    public function createDemoTenant(Affiliate $affiliate): Tenant
    {
        $slug = 'demo-' . strtolower(Str::random(8));

        $tenant = Tenant::create([
            'name'                 => 'Demo - ' . ($affiliate->company_name ?: $affiliate->user->name),
            'slug'                 => $slug,
            'email'                => $affiliate->user->email,
            'phone'                => $affiliate->phone,
            'plan'                 => 'professional',
            'is_active'            => true,
            'trial_ends_at'        => null,
            'plan_expires_at'      => now()->addYears(10), // demo never expires
            'business_type'        => 'distributor',
            'business_description' => 'Akun demo untuk affiliate ' . $affiliate->code,
            'onboarding_completed' => true,
        ]);

        // Create admin user for demo tenant (linked to affiliate's email)
        $demoUser = User::create([
            'tenant_id'         => $tenant->id,
            'name'              => $affiliate->user->name . ' (Demo)',
            'email'             => 'demo-' . $slug . '@qalcuity.com',
            'password'          => Hash::make('demo123456'),
            'role'              => 'admin',
            'is_active'         => true,
            'email_verified_at' => now(),
        ]);

        $affiliate->update(['demo_tenant_id' => $tenant->id]);

        AffiliateAuditLog::log($affiliate->id, 'demo_created',
            "Demo tenant created: {$tenant->name} (#{$tenant->id})", 'info', [
                'tenant_id' => $tenant->id, 'demo_email' => $demoUser->email,
            ]);

        return $tenant;
    }
}
