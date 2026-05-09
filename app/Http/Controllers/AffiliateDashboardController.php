<?php

namespace App\Http\Controllers;

use App\Models\AffiliateAuditLog;
use App\Models\AffiliateCommission;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use Illuminate\Http\Request;

class AffiliateDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $affiliate = $user->affiliate;
        if (! $affiliate) {
            abort(403, 'Akun affiliate tidak ditemukan.');
        }

        $affiliate->recalculateBalance();

        $referrals = AffiliateReferral::with('tenant')
            ->where('affiliate_id', $affiliate->id)
            ->latest('referred_at')->get();

        $commissions = AffiliateCommission::with('tenant')
            ->where('affiliate_id', $affiliate->id)
            ->latest()->paginate(20);

        $payouts = $affiliate->payouts()->latest()->limit(10)->get();
        $pendingWithdraw = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->where('status', 'pending')->sum('amount');

        $monthlyEarnings = AffiliateCommission::where('affiliate_id', $affiliate->id)
            ->whereIn('status', ['approved', 'paid'])
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, SUM(commission_amount) as total")
            ->groupBy('month')->orderBy('month', 'desc')->limit(6)->get();

        return view('affiliate.dashboard', compact(
            'affiliate', 'referrals', 'commissions', 'payouts',
            'monthlyEarnings', 'pendingWithdraw'
        ));
    }

    public function requestWithdraw(Request $request)
    {
        $affiliate = auth()->user()->affiliate;
        if (! $affiliate) {
            abort(403);
        }

        $data = $request->validate([
            'amount' => 'required|numeric|min:50000', // min withdraw 50k
        ]);

        $affiliate->recalculateBalance();
        $pendingWithdraw = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->where('status', 'pending')->sum('amount');
        $available = $affiliate->balance - $pendingWithdraw;

        if ($data['amount'] > $available + 0.01) {
            return back()->with('error', 'Saldo tersedia tidak cukup (Rp '.number_format($available, 0, ',', '.').').');
        }

        // FRAUD: max 1 pending withdraw at a time
        if (AffiliatePayout::where('affiliate_id', $affiliate->id)->where('status', 'pending')->exists()) {
            return back()->with('error', 'Anda masih memiliki pengajuan withdraw yang pending.');
        }

        // FRAUD: must have bank info
        if (! $affiliate->bank_name || ! $affiliate->bank_account || ! $affiliate->bank_holder) {
            return back()->with('error', 'Lengkapi data rekening bank terlebih dahulu.');
        }

        AffiliatePayout::create([
            'affiliate_id' => $affiliate->id,
            'requested_by' => auth()->id(),
            'amount' => $data['amount'],
            'payment_method' => 'transfer',
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        AffiliateAuditLog::log($affiliate->id, 'withdraw_requested',
            'Withdraw requested: Rp '.number_format($data['amount'], 0, ',', '.'),
            'info', ['amount' => $data['amount'], 'ip' => request()->ip()]);

        return back()->with('success', 'Pengajuan withdraw Rp '.number_format($data['amount'], 0, ',', '.').' berhasil. Menunggu persetujuan admin.');
    }

    public function updateProfile(Request $request)
    {
        $affiliate = auth()->user()->affiliate;
        if (! $affiliate) {
            abort(403);
        }

        $data = $request->validate([
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'bank_holder' => 'nullable|string|max:255',
        ]);

        $affiliate->update($data);

        return back()->with('success', 'Profil affiliate diperbarui.');
    }
}
