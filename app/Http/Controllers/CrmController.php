<?php

namespace App\Http\Controllers;

use App\Models\CrmActivity;
use App\Models\CrmLead;
use Illuminate\Http\Request;

class CrmController extends Controller
{
    private function tenantId(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $tid   = $this->tenantId();
        $query = CrmLead::where('tenant_id', $tid)->with('assignedUser');

        if ($request->stage) {
            $query->where('stage', $request->stage);
        }
        if ($request->search) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('company', 'like', "%$s%"));
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
            ->where('stage', 'won')
            ->whereMonth('updated_at', now()->month)
            ->sum('estimated_value');

        $followUpToday = CrmLead::where('tenant_id', $tid)
            ->whereHas('activities', fn($q) => $q->where('next_follow_up', '<=', today()))
            ->whereNotIn('stage', ['won', 'lost'])
            ->count();

        return view('crm.index', compact('leads', 'pipeline', 'wonThisMonth', 'followUpToday'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'company'             => 'nullable|string|max:255',
            'phone'               => 'nullable|string|max:20',
            'email'               => 'nullable|email|max:255',
            'source'              => 'nullable|in:referral,website,cold_call,social_media,exhibition',
            'product_interest'    => 'nullable|string|max:255',
            'estimated_value'     => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'notes'               => 'nullable|string',
        ]);

        CrmLead::create([
            'tenant_id'   => $this->tenantId(),
            'assigned_to' => auth()->id(),
            'stage'       => 'new',
            'probability' => 10,
            'last_contact_at' => now(),
        ] + $data);

        return back()->with('success', "Lead {$data['name']} berhasil ditambahkan.");
    }

    public function updateStage(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'stage'       => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'probability' => 'nullable|integer|min:0|max:100',
            'notes'       => 'nullable|string',
        ]);

        $prob = $data['probability'] ?? match ($data['stage']) {
            'new' => 10, 'contacted' => 20, 'qualified' => 40,
            'proposal' => 60, 'negotiation' => 80, 'won' => 100, 'lost' => 0, default => 10,
        };

        $lead->update([
            'stage'           => $data['stage'],
            'probability'     => $prob,
            'notes'           => $data['notes'] ?? $lead->notes,
            'last_contact_at' => now(),
        ]);

        return back()->with('success', "Stage lead {$lead->name} diperbarui.");
    }

    public function logActivity(Request $request, CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);

        $data = $request->validate([
            'type'           => 'required|in:call,email,meeting,whatsapp,demo,proposal',
            'description'    => 'required|string',
            'outcome'        => 'nullable|in:interested,not_interested,follow_up,closed',
            'next_follow_up' => 'nullable|date',
        ]);

        CrmActivity::create([
            'tenant_id' => $this->tenantId(),
            'lead_id'   => $lead->id,
            'user_id'   => auth()->id(),
        ] + $data);

        $lead->update(['last_contact_at' => now()]);

        return back()->with('success', 'Aktivitas berhasil dicatat.');
    }

    public function destroy(CrmLead $lead)
    {
        abort_unless($lead->tenant_id === $this->tenantId(), 403);
        $lead->delete();
        return back()->with('success', 'Lead berhasil dihapus.');
    }
}
