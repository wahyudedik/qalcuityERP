<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroup;
use App\Models\InterCompanyTransaction;
use App\Models\Tenant;
use App\Services\ConsolidationService;
use Illuminate\Http\Request;

class CompanyGroupController extends Controller
{
    public function __construct(protected ConsolidationService $consolidation) {}

    private function authorizeGroup(CompanyGroup $group): void
    {
        $user = auth()->user();
        // Owner or super_admin can access
        if ($group->owner_user_id === $user->id || $user->isSuperAdmin()) {
            return;
        }
        // Member tenant admin can also access
        if ($user->tenant_id && $group->members()->where('tenant_id', $user->tenant_id)->exists()) {
            return;
        }
        abort(403);
    }

    public function index()
    {
        $user = auth()->user();
        $groups = CompanyGroup::where('owner_user_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('tenant_id', $user->tenant_id))
            ->withCount(['members' => fn ($q) => $q->where('tenant_id', $user->tenant_id)])
            ->latest()
            ->get();

        return view('company-groups.index', compact('groups'));
    }

    public function create()
    {
        return view('company-groups.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'currency_code' => 'required|string|max:10',
        ]);

        $group = CompanyGroup::create([
            'owner_user_id' => auth()->id(),
            'name' => $data['name'],
            'currency_code' => $data['currency_code'],
        ]);

        if (auth()->user()->tenant_id) {
            $group->members()->create([
                'tenant_id' => auth()->user()->tenant_id,
                'role' => 'owner',
            ]);
        }

        return redirect()->route('company-groups.show', $group)
            ->with('success', 'Grup perusahaan berhasil dibuat.');
    }

    public function show(CompanyGroup $companyGroup)
    {
        $this->authorizeGroup($companyGroup);
        $companyGroup->load('members');

        $period = request('period', now()->format('Y-m'));
        $report = $this->consolidation->consolidatedReport($companyGroup, $period);
        $cashFlow = $this->consolidation->consolidatedCashFlow($companyGroup, $period);
        $trend = $this->consolidation->consolidatedTrend($companyGroup, 6);

        $transactions = InterCompanyTransaction::where('company_group_id', $companyGroup->id)
            ->with(['fromTenant', 'toTenant'])
            ->latest('transaction_date')
            ->paginate(30);

        $availableTenants = Tenant::where('is_active', true)
            ->whereNotIn('id', $companyGroup->members->pluck('id'))
            ->orderBy('name')
            ->get();

        return view('company-groups.show', compact(
            'companyGroup', 'report', 'cashFlow', 'trend',
            'transactions', 'period', 'availableTenants'
        ));
    }

    public function addMember(Request $request, CompanyGroup $companyGroup)
    {
        $this->authorizeGroup($companyGroup);

        $data = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        if (! $companyGroup->members()->where('tenant_id', $data['tenant_id'])->exists()) {
            $companyGroup->members()->create([
                'tenant_id' => $data['tenant_id'],
                'role' => 'member',
            ]);
        }

        return back()->with('success', 'Perusahaan berhasil ditambahkan ke grup.');
    }

    public function removeMember(CompanyGroup $companyGroup, Tenant $tenant)
    {
        $this->authorizeGroup($companyGroup);
        $companyGroup->members()->where('tenant_id', $tenant->id)->delete();

        return back()->with('success', 'Perusahaan dihapus dari grup.');
    }

    public function storeTransaction(Request $request, CompanyGroup $companyGroup)
    {
        $this->authorizeGroup($companyGroup);

        $data = $request->validate([
            'from_tenant_id' => 'required|exists:tenants,id',
            'to_tenant_id' => 'required|exists:tenants,id|different:from_tenant_id',
            'type' => 'required|in:sale,loan,expense_allocation,dividend,management_fee',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
            'date' => 'required|date',
        ]);

        $this->consolidation->createIntercompanyTransaction(
            $companyGroup,
            $data['from_tenant_id'],
            $data['to_tenant_id'],
            $data['type'],
            $data['amount'],
            $data['description'] ?? '',
            $data['date']
        );

        return back()->with('success', 'Transaksi intercompany berhasil dicatat.');
    }

    public function postTransaction(InterCompanyTransaction $transaction)
    {
        $group = CompanyGroup::find($transaction->company_group_id);
        abort_if(! $group, 404);
        $this->authorizeGroup($group);
        $transaction->update(['status' => 'posted']);

        return back()->with('success', 'Transaksi diposting.');
    }

    public function voidTransaction(InterCompanyTransaction $transaction)
    {
        $group = CompanyGroup::find($transaction->company_group_id);
        abort_if(! $group, 404);
        $this->authorizeGroup($group);
        abort_if($transaction->status !== 'pending', 422, 'Hanya transaksi pending yang bisa di-void.');
        $transaction->update(['status' => 'voided']);

        return back()->with('success', 'Transaksi di-void.');
    }

    /**
     * Export consolidated report as CSV.
     */
    public function exportCsv(CompanyGroup $companyGroup)
    {
        $this->authorizeGroup($companyGroup);
        $companyGroup->load('members');

        $period = request('period', now()->format('Y-m'));
        $report = $this->consolidation->consolidatedReport($companyGroup, $period);

        $filename = "konsolidasi-{$companyGroup->name}-{$period}.csv";
        $csv = "\xEF\xBB\xBF"; // BOM
        $csv .= "Laporan Konsolidasi — {$companyGroup->name} — Periode {$period}\n\n";

        // P&L
        $csv .= "LABA RUGI KONSOLIDASI\n";
        $csv .= "Perusahaan,Omzet,Biaya,Laba\n";
        foreach ($report['revenues'] as $tid => $rev) {
            $exp = $report['expenses'][$tid]['amount'] ?? 0;
            $csv .= "\"{$rev['name']}\",{$rev['amount']},{$exp},".($rev['amount'] - $exp)."\n";
        }
        $csv .= "\"TOTAL\",{$report['total_revenue']},{$report['total_expense']},{$report['consolidated_profit']}\n";
        $csv .= "\"Eliminasi Intercompany\",,,{$report['elimination']['total']}\n";
        $csv .= "\"LABA KONSOLIDASI (setelah eliminasi)\",,,{$report['consolidated_profit']}\n\n";

        // Balance Sheet
        if (! empty($report['balance_sheet'])) {
            $csv .= "NERACA KONSOLIDASI\n";
            foreach ($report['balance_sheet'] as $type => $data) {
                $csv .= "\"{$data['label']}\",,,{$data['total']}\n";
                foreach ($data['per_member'] as $m) {
                    $csv .= "  \"{$m['name']}\",,,{$m['amount']}\n";
                }
            }
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
