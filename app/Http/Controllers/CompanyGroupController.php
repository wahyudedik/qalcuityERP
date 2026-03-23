<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroup;
use App\Models\IntercompanyTransaction;
use App\Models\Tenant;
use App\Services\ConsolidationService;
use Illuminate\Http\Request;

class CompanyGroupController extends Controller
{
    public function __construct(protected ConsolidationService $consolidation) {}

    public function index()
    {
        $groups = CompanyGroup::where('owner_user_id', auth()->id())
            ->withCount('members')
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
            'name'          => 'required|string|max:100',
            'currency_code' => 'required|string|max:10',
        ]);

        $group = CompanyGroup::create([
            'owner_user_id' => auth()->id(),
            'name'          => $data['name'],
            'currency_code' => $data['currency_code'],
        ]);

        // Tambahkan tenant user saat ini sebagai owner member
        if (auth()->user()->tenant_id) {
            $group->members()->attach(auth()->user()->tenant_id, ['role' => 'owner']);
        }

        return redirect()->route('company-groups.show', $group)
            ->with('success', 'Grup perusahaan berhasil dibuat.');
    }

    public function show(CompanyGroup $companyGroup)
    {
        abort_if($companyGroup->owner_user_id !== auth()->id(), 403);

        $companyGroup->load('members');
        $period = request('period', now()->format('Y-m'));
        $report = $this->consolidation->consolidatedReport($companyGroup, $period);

        $transactions = IntercompanyTransaction::where('company_group_id', $companyGroup->id)
            ->with(['fromTenant', 'toTenant'])
            ->latest()
            ->limit(20)
            ->get();

        return view('company-groups.show', compact('companyGroup', 'report', 'transactions', 'period'));
    }

    public function addMember(Request $request, CompanyGroup $companyGroup)
    {
        abort_if($companyGroup->owner_user_id !== auth()->id(), 403);

        $data = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        if (!$companyGroup->members()->where('tenant_id', $data['tenant_id'])->exists()) {
            $companyGroup->members()->attach($data['tenant_id'], ['role' => 'member']);
        }

        return back()->with('success', 'Perusahaan berhasil ditambahkan ke grup.');
    }

    public function removeMember(CompanyGroup $companyGroup, Tenant $tenant)
    {
        abort_if($companyGroup->owner_user_id !== auth()->id(), 403);
        $companyGroup->members()->detach($tenant->id);
        return back()->with('success', 'Perusahaan dihapus dari grup.');
    }

    public function storeTransaction(Request $request, CompanyGroup $companyGroup)
    {
        abort_if($companyGroup->owner_user_id !== auth()->id(), 403);

        $data = $request->validate([
            'from_tenant_id' => 'required|exists:tenants,id',
            'to_tenant_id'   => 'required|exists:tenants,id|different:from_tenant_id',
            'type'           => 'required|in:sale,loan,expense_allocation',
            'amount'         => 'required|numeric|min:1',
            'description'    => 'nullable|string|max:255',
            'date'           => 'required|date',
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

    public function postTransaction(IntercompanyTransaction $transaction)
    {
        $group = CompanyGroup::find($transaction->company_group_id);
        abort_if(!$group || $group->owner_user_id !== auth()->id(), 403);
        $transaction->update(['status' => 'posted']);
        return back()->with('success', 'Transaksi diposting.');
    }
}
