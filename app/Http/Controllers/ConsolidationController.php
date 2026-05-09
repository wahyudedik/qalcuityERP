<?php

namespace App\Http\Controllers;

use App\Models\CompanyGroup;
use App\Models\ConsolidationAccountMapping;
use App\Models\ConsolidationElimination;
use App\Models\ConsolidationMasterAccount;
use App\Models\ConsolidationOwnership;
use App\Models\ConsolidationReport;
use App\Services\ConsolidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConsolidationController extends Controller
{
    public function __construct(private ConsolidationService $consolidationService) {}

    public function index()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        if ($tenant->plan !== 'enterprise' && $tenant->plan !== 'ultimate') {
            return redirect()->route('dashboard')
                ->with('error', 'Fitur Multi-Company Consolidation hanya tersedia untuk paket Enterprise dan Ultimate.');
        }

        $groups = CompanyGroup::where('owner_user_id', $user->id)
            ->orWhereHas('members', fn ($q) => $q->where('tenant_id', $tenant->id))
            ->with('members')
            ->get();

        return view('consolidation.index', compact('groups'));
    }

    public function createGroup()
    {
        $user = auth()->user();
        $tenant = $user->tenant;

        if ($tenant->plan !== 'enterprise' && $tenant->plan !== 'ultimate') {
            abort(403, 'Fitur ini hanya tersedia untuk paket Enterprise dan Ultimate.');
        }

        return view('consolidation.create-group');
    }

    public function storeGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'currency_code' => 'required|string|size:3',
        ]);

        $user = auth()->user();

        $group = CompanyGroup::create([
            'owner_user_id' => $user->id,
            'name' => $request->name,
            'currency_code' => $request->currency_code,
        ]);

        // Auto-add current tenant as first member
        $group->members()->attach($user->tenant_id, ['role' => 'owner']);

        // Setup account mapping for this tenant
        $this->consolidationService->setupAccountMapping($group, $user->tenant_id);

        return redirect()->route('consolidation.show', $group)
            ->with('success', 'Company Group berhasil dibuat.');
    }

    public function show(CompanyGroup $group)
    {
        $this->authorize('view', $group);

        $group->load(['members', 'intercompanyTransactions']);

        $reports = ConsolidationReport::where('company_group_id', $group->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $masterAccounts = ConsolidationMasterAccount::where('company_group_id', $group->id)
            ->where('is_header', true)
            ->with('children')
            ->get();

        $ownerships = ConsolidationOwnership::where('company_group_id', $group->id)
            ->with(['parentTenant', 'subsidiaryTenant'])
            ->get();

        return view('consolidation.show', compact('group', 'reports', 'masterAccounts', 'ownerships'));
    }

    public function addMember(Request $request, CompanyGroup $group)
    {
        $this->authorize('update', $group);

        $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'role' => 'required|in:owner,member',
        ]);

        if ($group->members()->where('tenant_id', $request->tenant_id)->exists()) {
            return back()->with('error', 'Tenant sudah menjadi member group ini.');
        }

        $group->members()->attach($request->tenant_id, ['role' => $request->role]);

        // Setup account mapping for new member
        $this->consolidationService->setupAccountMapping($group, $request->tenant_id);

        return back()->with('success', 'Member berhasil ditambahkan ke group.');
    }

    public function removeMember(CompanyGroup $group, int $tenantId)
    {
        $this->authorize('update', $group);

        $group->members()->detach($tenantId);

        return back()->with('success', 'Member berhasil dihapus dari group.');
    }

    public function generateReport(Request $request, CompanyGroup $group)
    {
        $this->authorize('view', $group);

        $request->validate([
            'report_type' => 'required|in:balance_sheet,income_statement,cash_flow',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'tenant_ids' => 'required|array|min:1',
            'tenant_ids.*' => 'exists:tenants,id',
        ]);

        try {
            $report = $this->consolidationService->generateConsolidationReport(
                $group,
                $request->report_type,
                $request->period_start,
                $request->period_end,
                $request->tenant_ids,
                auth()->id()
            );

            return redirect()->route('consolidation.report.show', [$group, $report])
                ->with('success', 'Laporan konsolidasi berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal membuat laporan: '.$e->getMessage());
        }
    }

    public function showReport(CompanyGroup $group, ConsolidationReport $report)
    {
        $this->authorize('view', $group);

        if ($report->company_group_id !== $group->id) {
            abort(404);
        }

        $report->load(['eliminations.lines.masterAccount', 'adjustments.lines.masterAccount']);

        return view('consolidation.report', compact('group', 'report'));
    }

    public function finalizeReport(CompanyGroup $group, ConsolidationReport $report)
    {
        $this->authorize('update', $group);

        if ($report->company_group_id !== $group->id) {
            abort(404);
        }

        if ($report->status === 'finalized') {
            return back()->with('error', 'Laporan sudah di-finalize.');
        }

        $report->finalize(auth()->id());

        return back()->with('success', 'Laporan berhasil di-finalize.');
    }

    public function masterAccounts(CompanyGroup $group)
    {
        $this->authorize('view', $group);

        $accounts = ConsolidationMasterAccount::where('company_group_id', $group->id)
            ->orderBy('code')
            ->get();

        return view('consolidation.master-accounts', compact('group', 'accounts'));
    }

    public function storeMasterAccount(Request $request, CompanyGroup $group)
    {
        $this->authorize('update', $group);

        $request->validate([
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            'parent_id' => 'nullable|exists:consolidation_master_accounts,id',
            'is_header' => 'boolean',
        ]);

        $level = 1;
        if ($request->parent_id) {
            $parent = ConsolidationMasterAccount::find($request->parent_id);
            $level = $parent->level + 1;
        }

        ConsolidationMasterAccount::create([
            'company_group_id' => $group->id,
            'parent_id' => $request->parent_id,
            'code' => $request->code,
            'name' => $request->name,
            'type' => $request->type,
            'normal_balance' => $request->normal_balance,
            'level' => $level,
            'is_header' => $request->boolean('is_header'),
            'is_active' => true,
        ]);

        return back()->with('success', 'Master account berhasil dibuat.');
    }

    public function accountMappings(CompanyGroup $group)
    {
        $this->authorize('view', $group);

        $mappings = ConsolidationAccountMapping::where('company_group_id', $group->id)
            ->with(['sourceTenant', 'sourceAccount', 'consolidatedAccount'])
            ->get()
            ->groupBy('source_tenant_id');

        $masterAccounts = ConsolidationMasterAccount::where('company_group_id', $group->id)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('consolidation.account-mappings', compact('group', 'mappings', 'masterAccounts'));
    }

    public function updateMapping(Request $request, CompanyGroup $group, ConsolidationAccountMapping $mapping)
    {
        $this->authorize('update', $group);

        $request->validate([
            'consolidated_account_id' => 'nullable|exists:consolidation_master_accounts,id',
            'mapping_type' => 'required|in:direct,aggregate,eliminate',
        ]);

        $mapping->update([
            'consolidated_account_id' => $request->consolidated_account_id,
            'mapping_type' => $request->mapping_type,
        ]);

        return back()->with('success', 'Mapping berhasil diupdate.');
    }

    public function eliminations(CompanyGroup $group)
    {
        $this->authorize('view', $group);

        $eliminations = ConsolidationElimination::where('company_group_id', $group->id)
            ->with(['consolidationReport', 'relatedTransaction', 'lines.masterAccount'])
            ->orderBy('date', 'desc')
            ->paginate(20);

        return view('consolidation.eliminations', compact('group', 'eliminations'));
    }

    public function storeElimination(Request $request, CompanyGroup $group)
    {
        $this->authorize('update', $group);

        $request->validate([
            'type' => 'required|string',
            'date' => 'required|date',
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'lines' => 'required|array|min:2',
            'lines.*.master_account_id' => 'required|exists:consolidation_master_accounts,id',
            'lines.*.debit' => 'required|numeric|min:0',
            'lines.*.credit' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $elimination = ConsolidationElimination::create([
                'company_group_id' => $group->id,
                'type' => $request->type,
                'date' => $request->date,
                'description' => $request->description,
                'amount' => $request->amount,
                'status' => 'draft',
            ]);

            foreach ($request->lines as $line) {
                $elimination->lines()->create([
                    'master_account_id' => $line['master_account_id'],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $line['description'] ?? null,
                ]);
            }

            if (! $elimination->isBalanced()) {
                throw new \Exception('Elimination entry tidak balance.');
            }

            DB::commit();

            return back()->with('success', 'Elimination entry berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal membuat elimination: '.$e->getMessage());
        }
    }

    public function ownerships(CompanyGroup $group)
    {
        $this->authorize('view', $group);

        $ownerships = ConsolidationOwnership::where('company_group_id', $group->id)
            ->with(['parentTenant', 'subsidiaryTenant'])
            ->get();

        $members = $group->members;

        return view('consolidation.ownerships', compact('group', 'ownerships', 'members'));
    }

    public function storeOwnership(Request $request, CompanyGroup $group)
    {
        $this->authorize('update', $group);

        $request->validate([
            'parent_tenant_id' => 'required|exists:tenants,id',
            'subsidiary_tenant_id' => 'required|exists:tenants,id|different:parent_tenant_id',
            'ownership_percentage' => 'required|numeric|min:0|max:100',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'consolidation_method' => 'required|in:full,proportional,equity',
        ]);

        ConsolidationOwnership::create([
            'company_group_id' => $group->id,
            'parent_tenant_id' => $request->parent_tenant_id,
            'subsidiary_tenant_id' => $request->subsidiary_tenant_id,
            'ownership_percentage' => $request->ownership_percentage,
            'effective_from' => $request->effective_from,
            'effective_to' => $request->effective_to,
            'consolidation_method' => $request->consolidation_method,
            'notes' => $request->notes,
        ]);

        return back()->with('success', 'Ownership structure berhasil ditambahkan.');
    }

    public function exportReport(CompanyGroup $group, ConsolidationReport $report)
    {
        $this->authorize('view', $group);

        if ($report->company_group_id !== $group->id) {
            abort(404);
        }

        // Export to PDF or Excel
        $pdf = \PDF::loadView('consolidation.export.pdf', compact('group', 'report'));

        return $pdf->download("consolidation-{$report->report_type}-{$report->period_start}.pdf");
    }
}
