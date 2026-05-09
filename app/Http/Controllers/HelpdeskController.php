<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\HelpdeskReply;
use App\Models\HelpdeskTicket;
use App\Models\KbArticle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HelpdeskController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Tickets ───────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = HelpdeskTicket::with(['customer', 'assignee'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('ticket_number', 'like', "%$s%")
                ->orWhere('subject', 'like', "%$s%")
                ->orWhere('contact_name', 'like', "%$s%"));
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();

        $stats = [
            'open' => HelpdeskTicket::where('tenant_id', $this->tid())->where('status', 'open')->count(),
            'in_progress' => HelpdeskTicket::where('tenant_id', $this->tid())->where('status', 'in_progress')->count(),
            'overdue' => HelpdeskTicket::where('tenant_id', $this->tid())
                ->whereNotIn('status', ['resolved', 'closed'])
                ->where('sla_resolve_due', '<', now())->count(),
            'resolved_month' => HelpdeskTicket::where('tenant_id', $this->tid())
                ->where('status', 'resolved')
                ->whereMonth('resolved_at', now()->month)->count(),
        ];

        $customers = Customer::where('tenant_id', $this->tid())->orderBy('name')->get();
        $agents = User::where('tenant_id', $this->tid())->whereIn('role', ['admin', 'manager', 'staff'])->orderBy('name')->get();

        return view('helpdesk.index', compact('tickets', 'stats', 'customers', 'agents'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'customer_id' => 'nullable|exists:customers,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'priority' => 'required|in:low,medium,high,urgent',
            'category' => 'required|string|max:50',
            'assigned_to' => 'nullable|exists:users,id',
            'contract_id' => 'nullable|exists:contracts,id',
        ]);

        // SLA from contract or defaults
        $slaResponse = 24; // hours
        $slaResolve = 72;
        if (! empty($data['contract_id'])) {
            $contract = Contract::find($data['contract_id']);
            if ($contract) {
                $slaResponse = $contract->sla_response_hours ?? $slaResponse;
                $slaResolve = $contract->sla_resolution_hours ?? $slaResolve;
            }
        }
        // Priority multiplier
        $multiplier = match ($data['priority']) {
            'urgent' => 0.25, 'high' => 0.5, 'medium' => 1, 'low' => 2, default => 1,
        };

        HelpdeskTicket::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'ticket_number' => HelpdeskTicket::generateNumber($this->tid()),
            'created_by' => auth()->id(),
            'status' => 'open',
            'sla_response_due' => now()->addHours((int) ($slaResponse * $multiplier)),
            'sla_resolve_due' => now()->addHours((int) ($slaResolve * $multiplier)),
        ]));

        return back()->with('success', 'Tiket berhasil dibuat.');
    }

    public function show(HelpdeskTicket $helpdeskTicket)
    {
        abort_if($helpdeskTicket->tenant_id !== $this->tid(), 403);
        $helpdeskTicket->load(['customer', 'assignee', 'creator', 'contract', 'replies.user']);

        $agents = User::where('tenant_id', $this->tid())->whereIn('role', ['admin', 'manager', 'staff'])->orderBy('name')->get();
        $kbArticles = KbArticle::where('tenant_id', $this->tid())->where('is_published', true)
            ->where(fn ($q) => $q->where('category', $helpdeskTicket->category)
                ->orWhere('title', 'like', '%'.Str::limit($helpdeskTicket->subject, 20, '').'%'))
            ->limit(5)->get();

        return view('helpdesk.show', compact('helpdeskTicket', 'agents', 'kbArticles'));
    }

    public function reply(Request $request, HelpdeskTicket $helpdeskTicket)
    {
        abort_if($helpdeskTicket->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'body' => 'required|string|max:5000',
            'is_internal' => 'nullable|boolean',
        ]);

        HelpdeskReply::create([
            'ticket_id' => $helpdeskTicket->id,
            'user_id' => auth()->id(),
            'body' => $data['body'],
            'is_internal' => $data['is_internal'] ?? false,
        ]);

        // Track first response for SLA
        if (! $helpdeskTicket->first_responded_at) {
            $met = $helpdeskTicket->sla_response_due ? now()->lte($helpdeskTicket->sla_response_due) : true;
            $helpdeskTicket->update([
                'first_responded_at' => now(),
                'sla_response_met' => $met,
                'status' => $helpdeskTicket->status === 'open' ? 'in_progress' : $helpdeskTicket->status,
            ]);
        }

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    public function updateStatus(Request $request, HelpdeskTicket $helpdeskTicket)
    {
        abort_if($helpdeskTicket->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'status' => 'required|in:open,in_progress,waiting,resolved,closed',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $updates = ['status' => $data['status']];
        if (isset($data['assigned_to'])) {
            $updates['assigned_to'] = $data['assigned_to'];
        }

        if ($data['status'] === 'resolved' && ! $helpdeskTicket->resolved_at) {
            $updates['resolved_at'] = now();
            $updates['sla_resolve_met'] = $helpdeskTicket->sla_resolve_due
                ? now()->lte($helpdeskTicket->sla_resolve_due) : true;
        }

        $helpdeskTicket->update($updates);

        return back()->with('success', 'Status tiket diperbarui.');
    }

    public function rate(Request $request, HelpdeskTicket $helpdeskTicket)
    {
        abort_if($helpdeskTicket->tenant_id !== $this->tid(), 403);
        $data = $request->validate(['satisfaction_rating' => 'required|numeric|min:1|max:5']);
        $helpdeskTicket->update($data);

        return back()->with('success', 'Rating berhasil disimpan.');
    }

    // ── Knowledge Base ────────────────────────────────────────────

    public function knowledgeBase(Request $request)
    {
        $query = KbArticle::where('tenant_id', $this->tid());

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn ($q) => $q->where('title', 'like', "%$s%")->orWhere('body', 'like', "%$s%"));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $articles = $query->latest()->paginate(20)->withQueryString();
        $categories = KbArticle::where('tenant_id', $this->tid())->distinct()->pluck('category');

        return view('helpdesk.knowledge-base', compact('articles', 'categories'));
    }

    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:50',
            'body' => 'required|string|max:20000',
        ]);

        KbArticle::create(array_merge($data, [
            'tenant_id' => $this->tid(),
            'slug' => Str::slug($data['title']),
            'is_published' => true,
            'user_id' => auth()->id(),
        ]));

        return back()->with('success', 'Artikel berhasil dibuat.');
    }

    public function destroyArticle(KbArticle $kbArticle)
    {
        abort_if($kbArticle->tenant_id !== $this->tid(), 403);
        $kbArticle->delete();

        return back()->with('success', 'Artikel berhasil dihapus.');
    }
}
