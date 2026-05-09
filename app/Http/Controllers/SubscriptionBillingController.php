<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSubscription;
use App\Models\CustomerSubscriptionPlan;
use App\Models\Invoice;
use App\Models\SubscriptionInvoice;
use App\Services\GlPostingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionBillingController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Dashboard ─────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = CustomerSubscription::with(['customer', 'plan'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('subscription_number', 'like', "%$s%")
                ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%")));
        }

        $subscriptions = $query->latest()->paginate(20)->withQueryString();

        $activeSubs = CustomerSubscription::where('tenant_id', $this->tid())->where('status', 'active');
        $stats = [
            'active' => (clone $activeSubs)->count(),
            'trial' => CustomerSubscription::where('tenant_id', $this->tid())->where('status', 'trial')->count(),
            'mrr' => (clone $activeSubs)->get()->sum(fn ($s) => $s->mrr()),
            'past_due' => CustomerSubscription::where('tenant_id', $this->tid())->where('status', 'past_due')->count(),
            'due_today' => CustomerSubscription::where('tenant_id', $this->tid())
                ->whereIn('status', ['active', 'trial'])
                ->where('next_billing_date', '<=', today())->count(),
        ];

        $plans = CustomerSubscriptionPlan::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();
        $customers = Customer::where('tenant_id', $this->tid())->orderBy('name')->get();

        return view('subscription-billing.index', compact('subscriptions', 'stats', 'plans', 'customers'));
    }

    // ── Plans ─────────────────────────────────────────────────────

    public function plans()
    {
        $plans = CustomerSubscriptionPlan::where('tenant_id', $this->tid())
            ->withCount('subscriptions')->latest()->paginate(20);

        return view('subscription-billing.plans', compact('plans'));
    }

    public function storePlan(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:30',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annual,annual',
            'trial_days' => 'nullable|integer|min:0',
            'features' => 'nullable|string',
        ]);

        CustomerSubscriptionPlan::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'is_active' => true,
            'features' => $data['features'] ? array_map('trim', explode("\n", $data['features'])) : null,
        ]));

        return back()->with('success', 'Plan berhasil dibuat.');
    }

    public function destroyPlan(CustomerSubscriptionPlan $customerSubscriptionPlan)
    {
        abort_if($customerSubscriptionPlan->tenant_id !== $this->tid(), 403);
        $customerSubscriptionPlan->delete();

        return back()->with('success', 'Plan berhasil dihapus.');
    }

    // ── Subscriptions ─────────────────────────────────────────────

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'plan_id' => 'required|exists:customer_subscription_plans,id',
            'start_date' => 'required|date',
            'price_override' => 'nullable|numeric|min:0',
            'discount_pct' => 'nullable|numeric|min:0|max:100',
            'auto_renew' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $plan = CustomerSubscriptionPlan::findOrFail($data['plan_id']);
        $startDate = Carbon::parse($data['start_date']);
        $trialEnds = $plan->trial_days > 0 ? $startDate->copy()->addDays($plan->trial_days) : null;
        $nextBilling = $trialEnds ?? $startDate;

        CustomerSubscription::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'subscription_number' => CustomerSubscription::generateNumber($this->tid()),
            'trial_ends_at' => $trialEnds,
            'next_billing_date' => $nextBilling,
            'status' => $trialEnds ? 'trial' : 'active',
            'auto_renew' => $data['auto_renew'] ?? true,
            'discount_pct' => $data['discount_pct'] ?? 0,
            'user_id' => auth()->id(),
        ]));

        return back()->with('success', 'Subscription berhasil dibuat.');
    }

    public function cancel(Request $request, CustomerSubscription $customerSubscription)
    {
        abort_if($customerSubscription->tenant_id !== $this->tid(), 403);

        $customerSubscription->update([
            'status' => 'cancelled',
            'cancel_reason' => $request->reason ?? null,
            'end_date' => today(),
        ]);

        return back()->with('success', 'Subscription dibatalkan.');
    }

    // ── Generate Billing ──────────────────────────────────────────

    public function generateBilling(CustomerSubscription $customerSubscription, GlPostingService $glService)
    {
        abort_if($customerSubscription->tenant_id !== $this->tid(), 403);
        if (! in_array($customerSubscription->status, ['active', 'trial'])) {
            return back()->with('error', 'Subscription harus aktif.');
        }

        // Check trial → activate
        if ($customerSubscription->isTrialing()) {
            return back()->with('error', 'Masih dalam masa trial sampai '.$customerSubscription->trial_ends_at->format('d/m/Y'));
        }
        if ($customerSubscription->status === 'trial' && $customerSubscription->trial_ends_at?->isPast()) {
            $customerSubscription->update(['status' => 'active']);
        }

        $plan = $customerSubscription->plan;
        $basePrice = $customerSubscription->price_override ?? $plan->price;
        $discount = round($basePrice * $customerSubscription->discount_pct / 100, 2);
        $netAmount = $basePrice - $discount;

        $periodStart = $customerSubscription->next_billing_date;
        $periodEnd = match ($plan->billing_cycle) {
            'monthly' => $periodStart->copy()->addMonth()->subDay(),
            'quarterly' => $periodStart->copy()->addMonths(3)->subDay(),
            'semi_annual' => $periodStart->copy()->addMonths(6)->subDay(),
            'annual' => $periodStart->copy()->addYear()->subDay(),
            default => $periodStart->copy()->addMonth()->subDay(),
        };

        DB::transaction(function () use ($customerSubscription, $plan, $basePrice, $discount, $netAmount, $periodStart, $periodEnd, $glService) {
            // Create real Invoice
            $invNumber = 'INV-SUB-'.date('Ymd').'-'.strtoupper(Str::random(4));
            $invoice = Invoice::create([
                'tenant_id' => $this->tid(),
                'customer_id' => $customerSubscription->customer_id,
                'number' => $invNumber,
                'subtotal_amount' => $netAmount,
                'tax_amount' => 0,
                'total_amount' => $netAmount,
                'paid_amount' => 0,
                'remaining_amount' => $netAmount,
                'status' => 'unpaid',
                'due_date' => $periodStart->copy()->addDays(14),
                'notes' => "Subscription: {$customerSubscription->subscription_number} — {$plan->name} ({$periodStart->format('d/m/Y')} - {$periodEnd->format('d/m/Y')})",
            ]);

            $subInv = SubscriptionInvoice::create([
                'tenant_id' => $this->tid(),
                'subscription_id' => $customerSubscription->id,
                'invoice_id' => $invoice->id,
                'billing_date' => today(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'amount' => $basePrice,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'status' => 'invoiced',
            ]);

            // Advance next billing date
            $nextDate = match ($plan->billing_cycle) {
                'monthly' => $periodStart->copy()->addMonth(),
                'quarterly' => $periodStart->copy()->addMonths(3),
                'semi_annual' => $periodStart->copy()->addMonths(6),
                'annual' => $periodStart->copy()->addYear(),
                default => $periodStart->copy()->addMonth(),
            };
            $customerSubscription->update(['next_billing_date' => $nextDate]);

            // GL posting
            $glResult = $glService->postInvoiceCreated(
                $this->tid(), auth()->id(), $invNumber, $invoice->id, $netAmount, 0, $netAmount
            );
            if ($glResult->isSuccess()) {
                $subInv->update(['journal_entry_id' => $glResult->journal->id]);
            }
            if ($glResult->isFailed()) {
                session()->flash('gl_warning', $glResult->warningMessage());
            }
        });

        return back()->with('success', 'Invoice subscription berhasil di-generate.');
    }

    // ── Bulk Generate (all due subscriptions) ─────────────────────

    public function bulkGenerate(GlPostingService $glService)
    {
        $dueSubs = CustomerSubscription::with('plan')
            ->where('tenant_id', $this->tid())
            ->where('status', 'active')
            ->where('next_billing_date', '<=', today())
            ->get();

        $generated = 0;
        foreach ($dueSubs as $sub) {
            // Reuse single generate logic inline
            $plan = $sub->plan;
            $basePrice = $sub->price_override ?? $plan->price;
            $discount = round($basePrice * $sub->discount_pct / 100, 2);
            $netAmount = $basePrice - $discount;
            if ($netAmount <= 0) {
                continue;
            }

            $periodStart = $sub->next_billing_date;
            $periodEnd = match ($plan->billing_cycle) {
                'monthly' => $periodStart->copy()->addMonth()->subDay(),
                'quarterly' => $periodStart->copy()->addMonths(3)->subDay(),
                'semi_annual' => $periodStart->copy()->addMonths(6)->subDay(),
                'annual' => $periodStart->copy()->addYear()->subDay(),
                default => $periodStart->copy()->addMonth()->subDay(),
            };

            DB::transaction(function () use ($sub, $plan, $basePrice, $discount, $netAmount, $periodStart, $periodEnd, $glService, &$generated) {
                $invNumber = 'INV-SUB-'.date('Ymd').'-'.strtoupper(Str::random(4));
                $invoice = Invoice::create([
                    'tenant_id' => $this->tid(), 'customer_id' => $sub->customer_id,
                    'number' => $invNumber, 'subtotal_amount' => $netAmount, 'tax_amount' => 0,
                    'total_amount' => $netAmount, 'paid_amount' => 0, 'remaining_amount' => $netAmount,
                    'status' => 'unpaid', 'due_date' => $periodStart->copy()->addDays(14),
                    'notes' => "Subscription: {$sub->subscription_number} — {$plan->name}",
                ]);

                $subInv = SubscriptionInvoice::create([
                    'tenant_id' => $this->tid(), 'subscription_id' => $sub->id,
                    'invoice_id' => $invoice->id, 'billing_date' => today(),
                    'period_start' => $periodStart, 'period_end' => $periodEnd,
                    'amount' => $basePrice, 'discount' => $discount, 'net_amount' => $netAmount,
                    'status' => 'invoiced',
                ]);

                $nextDate = match ($plan->billing_cycle) {
                    'monthly' => $periodStart->copy()->addMonth(),
                    'quarterly' => $periodStart->copy()->addMonths(3),
                    'semi_annual' => $periodStart->copy()->addMonths(6),
                    'annual' => $periodStart->copy()->addYear(),
                    default => $periodStart->copy()->addMonth(),
                };
                $sub->update(['next_billing_date' => $nextDate]);

                $glResult = $glService->postInvoiceCreated($this->tid(), auth()->id(), $invNumber, $invoice->id, $netAmount, 0, $netAmount);
                if ($glResult->isSuccess()) {
                    $subInv->update(['journal_entry_id' => $glResult->journal->id]);
                }
                if ($glResult->isFailed()) {
                    session()->flash('gl_warning', $glResult->warningMessage());
                }

                $generated++;
            });
        }

        return back()->with('success', "{$generated} invoice subscription berhasil di-generate.");
    }

    public function show(CustomerSubscription $customerSubscription)
    {
        abort_if($customerSubscription->tenant_id !== $this->tid(), 403);
        $customerSubscription->load(['customer', 'plan', 'invoices.invoice', 'user']);

        return view('subscription-billing.show', compact('customerSubscription'));
    }
}
