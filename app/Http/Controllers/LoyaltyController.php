<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use App\Models\LoyaltyProgram;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $program = LoyaltyProgram::where('tenant_id', $this->tid())->where('is_active', true)->first();

        $query = LoyaltyPoint::where('tenant_id', $this->tid())
            ->with('customer')
            ->orderByDesc('total_points');

        if ($request->search) {
            $s = $request->search;
            $query->whereHas('customer', fn($q) => $q->where('name', 'like', "%$s%"));
        }
        if ($request->tier) {
            $query->where('tier', $request->tier);
        }

        $points = $query->paginate(20)->withQueryString();

        $tiers = $program ? LoyaltyTier::where('program_id', $program->id)->orderBy('min_points')->get() : collect();

        $stats = [
            'total_members' => LoyaltyPoint::where('tenant_id', $this->tid())->count(),
            'total_points' => LoyaltyPoint::where('tenant_id', $this->tid())->sum('total_points'),
            'earned_month' => LoyaltyTransaction::where('tenant_id', $this->tid())
                ->where('type', 'earn')->whereMonth('created_at', now()->month)->sum('points'),
            'redeemed_month' => LoyaltyTransaction::where('tenant_id', $this->tid())
                ->where('type', 'redeem')->whereMonth('created_at', now()->month)->sum('points'),
        ];

        return view('loyalty.index', compact('program', 'points', 'tiers', 'stats'));
    }

    public function saveProgram(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'points_per_idr' => 'required|numeric|min:0',
            'idr_per_point' => 'required|numeric|min:0',
            'min_redeem_points' => 'required|integer|min:1',
            'expiry_days' => 'required|integer|min:0',
        ]);

        $program = LoyaltyProgram::updateOrCreate(
            ['tenant_id' => $this->tid()],
            $data + ['is_active' => true]
        );

        // Ensure default tiers exist
        if ($program->tiers()->count() === 0) {
            foreach ([
                ['name' => 'Bronze', 'min_points' => 0, 'multiplier' => 1.0, 'color' => '#cd7f32'],
                ['name' => 'Silver', 'min_points' => 1000, 'multiplier' => 1.5, 'color' => '#c0c0c0'],
                ['name' => 'Gold', 'min_points' => 5000, 'multiplier' => 2.0, 'color' => '#ffd700'],
            ] as $tier) {
                LoyaltyTier::create(['tenant_id' => $this->tid(), 'program_id' => $program->id] + $tier);
            }
        }

        return back()->with('success', 'Program loyalitas berhasil disimpan.');
    }

    public function addPoints(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'transaction_amount' => 'required|numeric|min:0',
            'points_override' => 'nullable|integer|min:1',
            'reference' => 'nullable|string|max:100',
        ]);

        // BUG-CRM-003 FIX: Use atomic service instead of increment()
        $result = app(\App\Services\LoyaltyPointService::class)->earnPoints(
            $this->tid(),
            $data['customer_id'],
            $data['transaction_amount'],
            $data['points_override'] ?? null,
            $data['reference'] ?? null
        );

        if (!$result['success']) {
            return back()->withErrors(['points' => $result['message']]);
        }

        return back()->with(
            'success',
            "{$result['points_earned']} poin berhasil ditambahkan. Balance: {$result['new_balance']}"
        );
    }

    public function redeemPoints(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:100',
        ]);

        // BUG-CRM-003 FIX: Use atomic service instead of decrement()
        $result = app(\App\Services\LoyaltyPointService::class)->redeemPoints(
            $this->tid(),
            $data['customer_id'],
            $data['points'],
            $data['reference'] ?? null
        );

        if (!$result['success']) {
            return back()->withErrors(['points' => $result['message']]);
        }

        return back()->with(
            'success',
            "{$result['points_redeemed']} poin ditukar senilai Rp " . number_format($result['value'], 0, ',', '.') . ". Balance: {$result['new_balance']}"
        );
    }

    public function transactions(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tid(), 403);
        $txs = LoyaltyTransaction::where('tenant_id', $this->tid())
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')->paginate(20);
        return response()->json($txs);
    }

    // BUG-CRM-003 FIX: API endpoint to check balance
    public function getBalance(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tid(), 403);

        $program = LoyaltyProgram::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->firstOrFail();

        $result = app(\App\Services\LoyaltyPointService::class)->getBalance(
            $this->tid(),
            $customer->id,
            $program->id
        );

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    // BUG-CRM-003 FIX: API endpoint to recalculate balance (repair tool)
    public function recalculateBalance(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tid(), 403);

        $program = LoyaltyProgram::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->firstOrFail();

        $result = app(\App\Services\LoyaltyPointService::class)->recalculateBalance(
            $this->tid(),
            $customer->id,
            $program->id
        );

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }
}
