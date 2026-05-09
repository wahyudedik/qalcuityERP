<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CrmActivity;
use App\Models\CrmLead;
use App\Services\LeadConversionService;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    // tenantId() inherited from parent Controller

    public function index(Request $request)
    {
        $tid = $this->tenantId();
        $query = CrmLead::where('tenant_id', $tid)->with(['assignedUser', 'convertedCustomer']);

        if ($request->stage) {
            $query->where('stage', $request->stage);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
        }

        $leads = $query->orderByRaw("FIELD(stage,'new','contacted','qualified','proposal','negotiation','won','lost')")
            ->paginate(20)->withQueryString();

        // Pipeline stats
        $pipeline = CrmLead::where('tenant_id', $tid)
            ->whereNotIn('stage', ['won', 'lost'])
            ->selectRaw('stage, count(*) as count, sum(estimated_value) as total_value')
            ->groupBy('stage')
            ->get()
            ->keyBy('stage');

        $wonThisMonth = CrmLead::where('tenant_id', $tid)
            ->where('stage', CrmLead::STAGE_WON)
            ->whereMonth('updated_at', now()->month)
            ->sum('estimated_value');

        $followUpToday = CrmLead::where('tenant_id', $tid)
            ->whereHas('activities', fn ($q) => $q->where('next_follow_up', '<=', today()))
            ->whereNotIn('stage', [CrmLead::STAGE_WON, CrmLead::STAGE_LOST])
            ->count();

        return view('crm.index', compact('leads', 'pipeline', 'wonThisMonth', 'followUpToday'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'source' => 'nullable|in:referral,website,cold_call,social_media,exhibition',
            'product_interest' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $lead = CrmLead::create([
            'tenant_id' => $this->tenantId(),
            'assigned_to' => auth()->id(),
            'stage' => 'new',
            'probability' => 10,
            'last_contact_at' => now(),
        ] + $data);

        ActivityLog::record('lead_created', "Lead baru: {$lead->name}".($lead->company ? " ({$lead->company})" : ''), $lead, [], $lead->toArray());

        return back()->with('success', "Lead {$data['name']} berhasil ditambahkan.");
    }

    public function updateStage(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'stage' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'probability' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $prob = $data['probability'] ?? match ($data['stage']) {
            'new' => 10,
            'contacted' => 20,
            'qualified' => 40,
            'proposal' => 60,
            'negotiation' => 80,
            'won' => 100,
            'lost' => 0,
            default => 10,
        };

        $lead->update([
            'stage' => $data['stage'],
            'probability' => $prob,
            'notes' => $data['notes'] ?? $lead->notes,
            'last_contact_at' => now(),
        ]);

        return back()->with('success', "Stage lead {$lead->name} diperbarui.");
    }

    public function logActivity(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'type' => 'required|in:call,email,meeting,whatsapp,demo,proposal',
            'description' => 'required|string',
            'outcome' => 'nullable|in:interested,not_interested,follow_up,closed',
            'next_follow_up' => 'nullable|date',
        ]);

        CrmActivity::create([
            'tenant_id' => $this->tenantId(),
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
        ] + $data);

        $lead->update(['last_contact_at' => now()]);

        return back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function convertToCustomer(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'force_create' => 'nullable|boolean',
            'link_to_customer_id' => 'nullable|exists:customers,id',
        ]);

        $conversionService = app(LeadConversionService::class);

        $result = $conversionService->convertLead(
            $lead,
            $data['force_create'] ?? false,
            $data['link_to_customer_id'] ?? null
        );

        if (! $result['success']) {
            if (isset($result['already_converted'])) {
                return back()->with('error', $result['message']);
            }

            if (isset($result['has_duplicates']) && $result['has_duplicates']) {
                // Return with duplicates info for user review
                return back()
                    ->with('warning', $result['message'])
                    ->with('duplicates', $result['duplicates'])
                    ->with('lead_id', $lead->id);
            }

            return back()->with('error', $result['message']);
        }

        ActivityLog::record(
            'lead_converted',
            $result['message'],
            $result['customer'],
            [],
            $result['customer']->toArray()
        );

        return back()->with('success', $result['message']);
    }

    // BUG-CRM-001 FIX: API endpoint to check for duplicates before conversion
    public function checkLeadDuplicates(CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $result = app(LeadConversionService::class)->checkForDuplicates($lead);

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function destroy(CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);
        ActivityLog::record('lead_deleted', "Lead dihapus: {$lead->name}".($lead->company ? " ({$lead->company})" : ''), $lead, $lead->toArray());
        $lead->delete();

        return back()->with('success', 'Lead berhasil dihapus.');
    }

    public function kanban()
    {
        $tid = $this->tenantId();

        $stages = CrmLead::STAGES;
        $leads = CrmLead::where('tenant_id', $tid)
            ->with(['activities' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('estimated_value')
            ->get()
            ->groupBy('stage');

        $stageStats = CrmLead::where('tenant_id', $tid)
            ->whereNotIn('stage', [CrmLead::STAGE_WON, CrmLead::STAGE_LOST])
            ->selectRaw('stage, count(*) as count, sum(estimated_value) as total_value')
            ->groupBy('stage')->get()->keyBy('stage');

        $wonThisMonth = CrmLead::where('tenant_id', $tid)->where('stage', CrmLead::STAGE_WON)
            ->whereMonth('updated_at', now()->month)->sum('estimated_value');
        $followUpToday = CrmLead::where('tenant_id', $tid)
            ->whereHas('activities', fn ($q) => $q->where('next_follow_up', '<=', today()))
            ->whereNotIn('stage', [CrmLead::STAGE_WON, CrmLead::STAGE_LOST])->count();

        return view('crm.kanban', compact('leads', 'stages', 'stageStats', 'wonThisMonth', 'followUpToday'));
    }

    public function updateStageDrag(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $request->validate(['stage' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost']);

        $prob = match ($request->stage) {
            'new' => 10,
            'contacted' => 20,
            'qualified' => 40,
            'proposal' => 60,
            'negotiation' => 80,
            'won' => 100,
            'lost' => 0,
            default => 10,
        };

        $lead->update(['stage' => $request->stage, 'probability' => $prob, 'last_contact_at' => now()]);

        return response()->json(['ok' => true]);
    }
}
