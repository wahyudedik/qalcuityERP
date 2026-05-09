<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractBilling;
use App\Models\ContractSlaLog;
use App\Models\ContractTemplate;
use App\Models\Customer;
use App\Models\Supplier;
use App\Services\GlPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Contract List ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = Contract::with(['customer', 'supplier'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('party_type')) {
            $query->where('party_type', $request->party_type);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('contract_number', 'like', "%$s%")
                ->orWhere('title', 'like', "%$s%"));
        }

        $contracts = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'active' => Contract::where('tenant_id', $this->tid())->where('status', 'active')->count(),
            'expiring' => Contract::where('tenant_id', $this->tid())->where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays(30)])->count(),
            'value' => Contract::where('tenant_id', $this->tid())->where('status', 'active')->sum('value'),
            'pending_billing' => ContractBilling::where('tenant_id', $this->tid())->where('status', 'pending')->count(),
        ];

        $customers = Customer::where('tenant_id', $this->tid())->orderBy('name')->get();
        $suppliers = Supplier::where('tenant_id', $this->tid())->orderBy('name')->get();
        $templates = ContractTemplate::where('tenant_id', $this->tid())->where('is_active', true)->orderBy('name')->get();

        return view('contracts.index', compact('contracts', 'stats', 'customers', 'suppliers', 'templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'template_id' => 'nullable|exists:contract_templates,id',
            'party_type' => 'required|in:customer,supplier',
            'customer_id' => 'nullable|exists:customers,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'category' => 'required|in:service,lease,supply,maintenance,subscription',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'value' => 'required|numeric|min:0',
            'billing_cycle' => 'required|in:one_time,monthly,quarterly,semi_annual,annual',
            'billing_amount' => 'nullable|numeric|min:0',
            'auto_renew' => 'nullable|boolean',
            'renewal_days_before' => 'nullable|integer|min:1|max:365',
            'sla_response_hours' => 'nullable|integer|min:1',
            'sla_resolution_hours' => 'nullable|integer|min:1',
            'sla_uptime_pct' => 'nullable|numeric|min:0|max:100',
            'sla_terms' => 'nullable|string|max:2000',
            'terms' => 'nullable|string|max:5000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $nextBilling = $data['billing_cycle'] !== 'one_time' ? $data['start_date'] : null;

        Contract::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'contract_number' => Contract::generateNumber($this->tid()),
            'user_id' => auth()->id(),
            'status' => 'draft',
            'billing_amount' => $data['billing_amount'] ?? 0,
            'next_billing_date' => $nextBilling,
            'auto_renew' => $data['auto_renew'] ?? false,
            'renewal_days_before' => $data['renewal_days_before'] ?? 30,
        ]));

        return back()->with('success', 'Kontrak berhasil dibuat.');
    }

    public function show(Contract $contract)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);
        $contract->load(['customer', 'supplier', 'template', 'user', 'billings.invoice', 'slaLogs']);

        return view('contracts.show', compact('contract'));
    }

    public function activate(Contract $contract)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);
        if ($contract->status !== 'draft') {
            return back()->with('error', 'Hanya kontrak draft yang bisa diaktifkan.');
        }

        $contract->update(['status' => 'active']);

        return back()->with('success', 'Kontrak diaktifkan.');
    }

    public function terminate(Request $request, Contract $contract)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);
        if (! in_array($contract->status, ['active', 'draft'])) {
            return back()->with('error', 'Kontrak tidak bisa diterminasi.');
        }

        $contract->update([
            'status' => 'terminated',
            'notes' => $contract->notes."\n[Terminated ".now()->format('d/m/Y').'] '.($request->reason ?? ''),
        ]);

        return back()->with('success', 'Kontrak diterminasi.');
    }

    public function renew(Contract $contract)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);
        if ($contract->status !== 'active' && $contract->status !== 'expired') {
            return back()->with('error', 'Hanya kontrak aktif/expired yang bisa di-renew.');
        }

        DB::transaction(function () use ($contract) {
            $contract->update(['status' => 'renewed']);

            $duration = $contract->start_date->diffInDays($contract->end_date);
            $newStart = $contract->end_date->addDay();
            $newEnd = $newStart->copy()->addDays($duration);

            Contract::create([
                'tenant_id' => $contract->tenant_id,
                'contract_number' => Contract::generateNumber($contract->tenant_id),
                'title' => $contract->title.' (Renewal)',
                'template_id' => $contract->template_id,
                'customer_id' => $contract->customer_id,
                'supplier_id' => $contract->supplier_id,
                'party_type' => $contract->party_type,
                'category' => $contract->category,
                'start_date' => $newStart,
                'end_date' => $newEnd,
                'value' => $contract->value,
                'currency_code' => $contract->currency_code,
                'billing_cycle' => $contract->billing_cycle,
                'billing_amount' => $contract->billing_amount,
                'next_billing_date' => $contract->billing_cycle !== 'one_time' ? $newStart : null,
                'auto_renew' => $contract->auto_renew,
                'renewal_days_before' => $contract->renewal_days_before,
                'status' => 'active',
                'sla_response_hours' => $contract->sla_response_hours,
                'sla_resolution_hours' => $contract->sla_resolution_hours,
                'sla_uptime_pct' => $contract->sla_uptime_pct,
                'sla_terms' => $contract->sla_terms,
                'terms' => $contract->terms,
                'user_id' => auth()->id(),
            ]);
        });

        return back()->with('success', 'Kontrak berhasil di-renew. Kontrak baru telah dibuat.');
    }

    // ── Billing ───────────────────────────────────────────────────

    public function generateBilling(Contract $contract, GlPostingService $glService)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);
        if ($contract->status !== 'active') {
            return back()->with('error', 'Kontrak harus aktif.');
        }
        if (! $contract->next_billing_date) {
            return back()->with('error', 'Tidak ada jadwal billing.');
        }

        $periodStart = $contract->next_billing_date;
        $periodEnd = match ($contract->billing_cycle) {
            'monthly' => $periodStart->copy()->addMonth()->subDay(),
            'quarterly' => $periodStart->copy()->addMonths(3)->subDay(),
            'semi_annual' => $periodStart->copy()->addMonths(6)->subDay(),
            'annual' => $periodStart->copy()->addYear()->subDay(),
            default => $contract->end_date,
        };

        DB::transaction(function () use ($contract, $periodStart, $periodEnd, $glService) {
            $billing = ContractBilling::create([
                'contract_id' => $contract->id,
                'tenant_id' => $this->tid(),
                'billing_date' => now(),
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'amount' => $contract->billing_amount,
                'status' => 'pending',
            ]);

            // Advance next billing date
            $nextDate = match ($contract->billing_cycle) {
                'monthly' => $periodStart->copy()->addMonth(),
                'quarterly' => $periodStart->copy()->addMonths(3),
                'semi_annual' => $periodStart->copy()->addMonths(6),
                'annual' => $periodStart->copy()->addYear(),
                default => null,
            };

            if ($nextDate && $nextDate->lte($contract->end_date)) {
                $contract->update(['next_billing_date' => $nextDate]);
            } else {
                $contract->update(['next_billing_date' => null]);
            }

            // GL: Dr Piutang (customer) or Dr Beban (supplier) / Cr Pendapatan or Cr Hutang
            if ($contract->billing_amount > 0) {
                $ref = $contract->contract_number.'-B'.$billing->id;
                $glResult = $contract->party_type === 'customer'
                    ? $glService->postContractBillingCustomer($this->tid(), auth()->id(), $ref, $billing->id, $contract->billing_amount, now()->toDateString())
                    : $glService->postContractBillingSupplier($this->tid(), auth()->id(), $ref, $billing->id, $contract->billing_amount, now()->toDateString());

                if ($glResult->isSuccess()) {
                    $billing->update(['journal_entry_id' => $glResult->journal->id]);
                }
                if ($glResult->isFailed()) {
                    session()->flash('gl_warning', $glResult->warningMessage());
                }
            }
        });

        return back()->with('success', 'Billing berhasil di-generate.');
    }

    // ── SLA Logs ──────────────────────────────────────────────────

    public function storeSlaLog(Request $request, Contract $contract)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'incident_type' => 'required|in:support,downtime,delivery_delay',
            'description' => 'required|string|max:255',
            'reported_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        ContractSlaLog::create(array_merge($data, [
            'contract_id' => $contract->id,
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
        ]));

        return back()->with('success', 'Insiden SLA berhasil dicatat.');
    }

    public function resolveSlaLog(Request $request, ContractSlaLog $contractSlaLog)
    {
        abort_if($contractSlaLog->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'responded_at' => 'nullable|date',
            'resolved_at' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $contract = $contractSlaLog->contract;
        $slaMet = true;

        if ($data['responded_at'] && $contract->sla_response_hours) {
            $responseH = $contractSlaLog->reported_at->diffInMinutes($data['responded_at']) / 60;
            if ($responseH > $contract->sla_response_hours) {
                $slaMet = false;
            }
        }
        if ($contract->sla_resolution_hours) {
            $resolveH = $contractSlaLog->reported_at->diffInMinutes($data['resolved_at']) / 60;
            if ($resolveH > $contract->sla_resolution_hours) {
                $slaMet = false;
            }
        }

        $contractSlaLog->update(array_merge($data, ['sla_met' => $slaMet]));

        return back()->with('success', 'Insiden resolved. SLA '.($slaMet ? '✅ terpenuhi' : '❌ tidak terpenuhi').'.');
    }

    // ── Templates ─────────────────────────────────────────────────

    public function templates(Request $request)
    {
        $templates = ContractTemplate::where('tenant_id', $this->tid())->latest()->paginate(20);

        return view('contracts.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:service,lease,supply,maintenance,subscription',
            'body_template' => 'nullable|string|max:10000',
            'default_terms' => 'nullable|string|max:5000',
        ]);

        ContractTemplate::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'is_active' => true,
        ]));

        return back()->with('success', 'Template kontrak berhasil dibuat.');
    }

    public function destroyTemplate(ContractTemplate $contractTemplate)
    {
        abort_if($contractTemplate->tenant_id !== $this->tid(), 403);
        $contractTemplate->delete();

        return back()->with('success', 'Template berhasil dihapus.');
    }

    public function destroy(Contract $contract)
    {
        abort_if($contract->tenant_id !== $this->tid(), 403);
        if ($contract->status === 'active') {
            return back()->with('error', 'Kontrak aktif tidak bisa dihapus. Terminasi dulu.');
        }
        $contract->delete();

        return back()->with('success', 'Kontrak berhasil dihapus.');
    }
}
