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
    private function tid(): int { return auth()->user()->tenant_id; }

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
            'total_points'  => LoyaltyPoint::where('tenant_id', $this->tid())->sum('total_points'),
            'earned_month'  => LoyaltyTransaction::where('tenant_id', $this->tid())
                ->where('type', 'earn')->whereMonth('created_at', now()->month)->sum('points'),
            'redeemed_month'=> LoyaltyTransaction::where('tenant_id', $this->tid())
                ->where('type', 'redeem')->whereMonth('created_at', now()->month)->sum('points'),
        ];

        return view('loyalty.index', compact('program', 'points', 'tiers', 'stats'));
    }

    public function saveProgram(Request $request)
    {
        $data = $request->validate([
            'name'              => 'required|string|max:255',
            'points_per_idr'    => 'required|numeric|min:0',
            'idr_per_point'     => 'required|numeric|min:0',
            'min_redeem_points' => 'required|integer|min:1',
            'expiry_days'       => 'required|integer|min:0',
        ]);

        $program = LoyaltyProgram::updateOrCreate(
            ['tenant_id' => $this->tid()],
            $data + ['is_active' => true]
        );

        // Ensure default tiers exist
        if ($program->tiers()->count() === 0) {
            foreach ([
                ['name' => 'Bronze', 'min_points' => 0,    'multiplier' => 1.0, 'color' => '#cd7f32'],
                ['name' => 'Silver', 'min_points' => 1000, 'multiplier' => 1.5, 'color' => '#c0c0c0'],
                ['name' => 'Gold',   'min_points' => 5000, 'multiplier' => 2.0, 'color' => '#ffd700'],
            ] as $tier) {
                LoyaltyTier::create(['tenant_id' => $this->tid(), 'program_id' => $program->id] + $tier);
            }
        }

        return back()->with('success', 'Program loyalitas berhasil disimpan.');
    }

    public function addPoints(Request $request)
    {
        $data = $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'transaction_amount' => 'required|numeric|min:0',
            'points_override'    => 'nullable|integer|min:1',
            'reference'          => 'nullable|string|max:100',
        ]);

        $program = LoyaltyProgram::where('tenant_id', $this->tid())->where('is_active', true)->firstOrFail();
        $points  = $data['points_override'] ?? $program->calculatePoints($data['transaction_amount']);

        $lp = LoyaltyPoint::firstOrCreate(
            ['tenant_id' => $this->tid(), 'customer_id' => $data['customer_id'], 'program_id' => $program->id],
            ['total_points' => 0, 'lifetime_points' => 0, 'tier' => 'Bronze']
        );

        $lp->increment('total_points', $points);
        $lp->increment('lifetime_points', $points);
        $lp->refresh();

        // Recalculate tier
        $newTier = LoyaltyTier::where('program_id', $program->id)
            ->where('min_points', '<=', $lp->lifetime_points)
            ->orderByDesc('min_points')->value('name') ?? 'Bronze';
        $lp->update(['tier' => $newTier, 'tier_updated_at' => now()]);

        LoyaltyTransaction::create([
            'tenant_id'          => $this->tid(),
            'customer_id'        => $data['customer_id'],
            'program_id'         => $program->id,
            'type'               => 'earn',
            'points'             => $points,
            'transaction_amount' => $data['transaction_amount'],
            'reference'          => $data['reference'] ?? null,
        ]);

        return back()->with('success', "{$points} poin berhasil ditambahkan.");
    }

    public function redeemPoints(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'points'      => 'required|integer|min:1',
            'reference'   => 'nullable|string|max:100',
        ]);

        $program = LoyaltyProgram::where('tenant_id', $this->tid())->where('is_active', true)->firstOrFail();

        $lp = LoyaltyPoint::where('tenant_id', $this->tid())
            ->where('customer_id', $data['customer_id'])
            ->where('program_id', $program->id)
            ->firstOrFail();

        if ($lp->total_points < $data['points']) {
            return back()->withErrors(['points' => 'Poin tidak mencukupi.']);
        }
        if ($data['points'] < $program->min_redeem_points) {
            return back()->withErrors(['points' => "Minimum redeem {$program->min_redeem_points} poin."]);
        }

        $lp->decrement('total_points', $data['points']);

        LoyaltyTransaction::create([
            'tenant_id'   => $this->tid(),
            'customer_id' => $data['customer_id'],
            'program_id'  => $program->id,
            'type'        => 'redeem',
            'points'      => -$data['points'],
            'reference'   => $data['reference'] ?? null,
        ]);

        $value = $data['points'] * $program->idr_per_point;
        return back()->with('success', "{$data['points']} poin ditukar senilai Rp " . number_format($value, 0, ',', '.') . ".");
    }

    public function transactions(Request $request, Customer $customer)
    {
        abort_unless($customer->tenant_id === $this->tid(), 403);
        $txs = LoyaltyTransaction::where('tenant_id', $this->tid())
            ->where('customer_id', $customer->id)
            ->orderByDesc('created_at')->paginate(20);
        return response()->json($txs);
    }
}
