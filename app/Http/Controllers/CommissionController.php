<?php

namespace App\Http\Controllers;

use App\Models\CommissionCalculation;
use App\Models\CommissionRule;
use App\Models\SalesOrder;
use App\Models\SalesTarget;
use App\Models\User;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    private function tid(): int { return auth()->user()->tenant_id; }

    // ── Dashboard ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $period = $request->period ?? now()->format('Y-m');

        $calculations = CommissionCalculation::with(['user', 'commissionRule'])
            ->where('tenant_id', $this->tid())
            ->where('period', $period)
            ->orderByDesc('total_sales')
            ->get();

        $targets = SalesTarget::with('user')
            ->where('tenant_id', $this->tid())
            ->where('period', $period)
            ->get()
            ->keyBy('user_id');

        $stats = [
            'total_commission' => $calculations->sum('total_payout'),
            'total_sales'      => $calculations->sum('total_sales'),
            'salespeople'      => $calculations->count(),
            'approved'         => $calculations->where('status', 'approved')->count(),
        ];

        $rules = CommissionRule::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $salespeople = User::where('tenant_id', $this->tid())->whereIn('role', ['admin', 'manager', 'staff'])->orderBy('name')->get();

        return view('commission.index', compact('calculations', 'targets', 'stats', 'period', 'rules', 'salespeople'));
    }

    // ── Rules ─────────────────────────────────────────────────────

    public function rules(Request $request)
    {
        $rules = CommissionRule::where('tenant_id', $this->tid())->latest()->paginate(20);
        return view('commission.rules', compact('rules'));
    }

    public function storeRule(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'type'  => 'required|in:flat_pct,tiered,flat_amount',
            'rate'  => 'nullable|numeric|min:0',
            'basis' => 'required|in:revenue,profit,quantity',
            'tiers' => 'nullable|json',
            'notes' => 'nullable|string|max:1000',
        ]);

        CommissionRule::create([
            'tenant_id' => $this->tid(),
            'name'      => $data['name'],
            'type'      => $data['type'],
            'rate'      => $data['rate'] ?? 0,
            'basis'     => $data['basis'],
            'tiers'     => $data['tiers'] ? json_decode($data['tiers'], true) : null,
            'is_active' => true,
            'notes'     => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Rule komisi berhasil dibuat.');
    }

    public function destroyRule(CommissionRule $commissionRule)
    {
        abort_if($commissionRule->tenant_id !== $this->tid(), 403);
        $commissionRule->delete();
        return back()->with('success', 'Rule berhasil dihapus.');
    }

    // ── Targets ───────────────────────────────────────────────────

    public function storeTarget(Request $request)
    {
        $data = $request->validate([
            'user_id'            => 'required|exists:users,id',
            'commission_rule_id' => 'nullable|exists:commission_rules,id',
            'period'             => 'required|string|size:7',
            'target_amount'      => 'required|numeric|min:0',
        ]);

        SalesTarget::updateOrCreate(
            ['tenant_id' => $this->tid(), 'user_id' => $data['user_id'], 'period' => $data['period']],
            ['commission_rule_id' => $data['commission_rule_id'], 'target_amount' => $data['target_amount']]
        );

        return back()->with('success', 'Target berhasil disimpan.');
    }

    // ── Calculate ─────────────────────────────────────────────────

    public function calculate(Request $request)
    {
        $period = $request->validate(['period' => 'required|string|size:7'])['period'];
        [$year, $month] = explode('-', $period);

        // Get all salespeople with SO in this period
        $salesData = SalesOrder::where('tenant_id', $this->tid())
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereYear('date', $year)->whereMonth('date', $month)
            ->selectRaw('user_id, SUM(total) as total_sales, COUNT(*) as total_orders')
            ->groupBy('user_id')
            ->get();

        $targets = SalesTarget::where('tenant_id', $this->tid())
            ->where('period', $period)
            ->get()->keyBy('user_id');

        // Default rule
        $defaultRule = CommissionRule::where('tenant_id', $this->tid())
            ->where('is_active', true)->first();

        DB::transaction(function () use ($salesData, $targets, $defaultRule, $period) {
            foreach ($salesData as $sd) {
                $target = $targets[$sd->user_id] ?? null;
                $rule = $target?->commissionRule ?? $defaultRule;

                $totalSales = (float) $sd->total_sales;
                $commission = $rule ? $rule->calculate($totalSales) : 0;

                // Bonus: extra 1% for exceeding target by 20%+
                $bonus = 0;
                if ($target && $target->target_amount > 0) {
                    $achievementPct = round($totalSales / $target->target_amount * 100, 2);
                    $target->update([
                        'achieved_amount' => $totalSales,
                        'achievement_pct' => $achievementPct,
                    ]);

                    if ($achievementPct >= 120) {
                        $excess = $totalSales - $target->target_amount;
                        $bonus = round($excess * 1 / 100, 2); // 1% bonus on excess
                    }
                }

                CommissionCalculation::updateOrCreate(
                    ['tenant_id' => $this->tid(), 'user_id' => $sd->user_id, 'period' => $period],
                    [
                        'commission_rule_id' => $rule?->id,
                        'total_sales'        => $totalSales,
                        'total_orders'       => $sd->total_orders,
                        'commission_amount'  => $commission,
                        'bonus_amount'       => $bonus,
                        'total_payout'       => $commission + $bonus,
                        'status'             => 'draft',
                    ]
                );
            }
        });

        return back()->with('success', "Komisi periode {$period} berhasil dihitung untuk {$salesData->count()} salesperson.");
    }

    // ── Approve & Pay ─────────────────────────────────────────────

    public function approve(CommissionCalculation $commissionCalculation)
    {
        abort_if($commissionCalculation->tenant_id !== $this->tid(), 403);
        if ($commissionCalculation->status !== 'draft') return back()->with('error', 'Hanya draft yang bisa di-approve.');

        $commissionCalculation->update([
            'status'      => 'approved',
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', 'Komisi di-approve.');
    }

    public function pay(CommissionCalculation $commissionCalculation, GlPostingService $glService)
    {
        abort_if($commissionCalculation->tenant_id !== $this->tid(), 403);
        if ($commissionCalculation->status !== 'approved') return back()->with('error', 'Approve dulu sebelum bayar.');

        $amount = (float) $commissionCalculation->total_payout;
        if ($amount <= 0) return back()->with('error', 'Total payout = 0.');

        $user = $commissionCalculation->user;
        $ref = 'COM-' . $commissionCalculation->period . '-' . ($user->id ?? 0);

        // GL: Dr Beban Komisi Sales (5205) / Cr Kas (1101)
        $glResult = $glService->postSalesCommission(
            $this->tid(), auth()->id(), $ref, $commissionCalculation->id, $amount
        );

        if ($glResult->isSuccess()) {
            $commissionCalculation->update([
                'status'           => 'paid',
                'journal_entry_id' => $glResult->journal->id,
            ]);
            return back()->with('success', 'Komisi dibayar. Jurnal: ' . $glResult->journal->number);
        }

        if ($glResult->isFailed()) {
            session()->flash('gl_warning', $glResult->warningMessage());
        }
        return back()->with('error', 'Gagal posting jurnal.');
    }
}
