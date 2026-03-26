<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliateAuditLog;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\User;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AffiliateManagementController extends Controller
{
    public function index(Request $request)
    {
        $affiliates = Affiliate::with(['user', 'demoTenant'])
            ->withCount('referrals')
            ->when($request->search, fn($q, $s) => $q->whereHas('user',
                fn($u) => $u->where('name', 'like', "%$s%")))
            ->latest()->paginate(20)->withQueryString();

        $stats = [
            'total'            => Affiliate::count(),
            'active'           => Affiliate::where('is_active', true)->count(),
            'total_referrals'  => \App\Models\AffiliateReferral::count(),
            'total_earned'     => Affiliate::sum('total_earned'),
            'pending_withdraw' => AffiliatePayout::where('status', 'pending')->sum('amount'),
            'fraud_alerts'     => AffiliateAuditLog::where('severity', 'fraud')
                ->where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('super-admin.affiliates.index', compact('affiliates', 'stats'));
    }

    public function store(Request $request, AffiliateService $service)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email',
            'password'        => 'required|string|min:8',
            'phone'           => 'nullable|string|max:20',
            'company_name'    => 'nullable|string|max:255',
            'bank_name'       => 'nullable|string|max:50',
            'bank_account'    => 'nullable|string|max:30',
            'bank_holder'     => 'nullable|string|max:255',
            'commission_rate' => 'nullable|numeric|min:0|max:50',
        ]);

        DB::transaction(function () use ($data, $service) {
            $user = User::create([
                'name' => $data['name'], 'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'affiliate', 'tenant_id' => null, 'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $affiliate = Affiliate::create([
                'user_id'         => $user->id,
                'code'            => Affiliate::generateCode(),
                'company_name'    => $data['company_name'] ?? null,
                'phone'           => $data['phone'] ?? null,
                'bank_name'       => $data['bank_name'] ?? null,
                'bank_account'    => $data['bank_account'] ?? null,
                'bank_holder'     => $data['bank_holder'] ?? null,
                'commission_rate' => $data['commission_rate'] ?? 10,
                'is_active'       => true,
            ]);

            // Create demo tenant for affiliate
            $service->createDemoTenant($affiliate);

            AffiliateAuditLog::log($affiliate->id, 'account_created',
                "Affiliate account created by super admin", 'info');
        });

        return back()->with('success', "Affiliate {$data['name']} berhasil dibuat + akun demo ERP.");
    }

    public function toggleActive(Affiliate $affiliate)
    {
        $affiliate->update(['is_active' => !$affiliate->is_active]);
        $affiliate->user->update(['is_active' => $affiliate->is_active]);

        AffiliateAuditLog::log($affiliate->id,
            $affiliate->is_active ? 'account_activated' : 'account_deactivated',
            'Status changed by super admin', 'info');

        return back()->with('success', 'Status affiliate diperbarui.');
    }

    // ── Commissions ───────────────────────────────────────────────

    public function commissions(Request $request)
    {
        $commissions = AffiliateCommission::with(['affiliate.user', 'tenant'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(30)->withQueryString();

        return view('super-admin.affiliates.commissions', compact('commissions'));
    }

    public function approveCommission(AffiliateCommission $c)
    {
        if ($c->status !== 'pending') return back()->with('error', 'Hanya pending yang bisa di-approve.');

        $c->update(['status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);
        $c->affiliate->recalculateBalance();

        AffiliateAuditLog::log($c->affiliate_id, 'commission_approved',
            "Commission #{$c->id} approved: Rp " . number_format($c->commission_amount, 0, ',', '.'), 'info',
            ['commission_id' => $c->id, 'approved_by' => auth()->id()]);

        return back()->with('success', 'Komisi di-approve.');
    }

    public function rejectCommission(AffiliateCommission $c, Request $request)
    {
        if ($c->status !== 'pending') return back()->with('error', 'Hanya pending yang bisa di-reject.');

        $c->update(['status' => 'rejected', 'notes' => $request->reason ?? 'Rejected by admin']);
        $c->affiliate->recalculateBalance();

        AffiliateAuditLog::log($c->affiliate_id, 'commission_rejected',
            "Commission #{$c->id} rejected: " . ($request->reason ?? 'No reason'), 'warning',
            ['commission_id' => $c->id]);

        return back()->with('success', 'Komisi di-reject.');
    }

    // ── Withdrawals (admin side) ──────────────────────────────────

    public function payouts(Request $request)
    {
        $payouts = AffiliatePayout::with(['affiliate.user', 'requester', 'processor'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest()->paginate(30)->withQueryString();

        return view('super-admin.affiliates.payouts', compact('payouts'));
    }

    public function approvePayout(AffiliatePayout $affiliatePayout)
    {
        if ($affiliatePayout->status !== 'pending') return back()->with('error', 'Hanya pending yang bisa di-approve.');

        $affiliate = $affiliatePayout->affiliate;

        // Double-check balance
        $affiliate->recalculateBalance();
        if ($affiliatePayout->amount > $affiliate->balance + 0.01) {
            return back()->with('error', 'Saldo tidak cukup. Balance: Rp ' . number_format($affiliate->balance, 0, ',', '.'));
        }

        DB::transaction(function () use ($affiliatePayout, $affiliate) {
            $affiliatePayout->update([
                'status'       => 'completed',
                'processed_by' => auth()->id(),
                'processed_at' => now(),
            ]);

            // Mark approved commissions as paid up to this amount
            $remaining = (float) $affiliatePayout->amount;
            $approvedCommissions = AffiliateCommission::where('affiliate_id', $affiliate->id)
                ->where('status', 'approved')->orderBy('created_at')->get();

            foreach ($approvedCommissions as $comm) {
                if ($remaining <= 0) break;
                $comm->update(['status' => 'paid', 'paid_at' => now()]);
                $remaining -= (float) $comm->commission_amount;
            }

            $affiliate->recalculateBalance();
        });

        AffiliateAuditLog::log($affiliate->id, 'withdraw_approved',
            "Withdraw Rp " . number_format($affiliatePayout->amount, 0, ',', '.') . " approved", 'info',
            ['payout_id' => $affiliatePayout->id, 'approved_by' => auth()->id()]);

        return back()->with('success', 'Withdraw di-approve dan saldo dikurangi.');
    }

    public function rejectPayout(AffiliatePayout $affiliatePayout, Request $request)
    {
        if ($affiliatePayout->status !== 'pending') return back()->with('error', 'Hanya pending yang bisa di-reject.');

        $affiliatePayout->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reason ?? 'Rejected by admin',
            'processed_by'  => auth()->id(),
            'processed_at'  => now(),
        ]);

        AffiliateAuditLog::log($affiliatePayout->affiliate_id, 'withdraw_rejected',
            "Withdraw rejected: " . ($request->reason ?? 'No reason'), 'warning',
            ['payout_id' => $affiliatePayout->id]);

        return back()->with('success', 'Withdraw di-reject.');
    }

    // ── Fraud Monitoring ──────────────────────────────────────────

    public function auditLogs(Request $request)
    {
        $logs = AffiliateAuditLog::with('affiliate.user')
            ->when($request->severity, fn($q, $s) => $q->where('severity', $s))
            ->when($request->affiliate_id, fn($q, $id) => $q->where('affiliate_id', $id))
            ->latest()->paginate(50)->withQueryString();

        $fraudCount = AffiliateAuditLog::where('severity', 'fraud')->count();
        $warningCount = AffiliateAuditLog::where('severity', 'warning')->count();

        return view('super-admin.affiliates.audit-logs', compact('logs', 'fraudCount', 'warningCount'));
    }
}
