<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\DeferredItem;
use App\Models\DeferredItemSchedule;
use App\Services\DeferredItemService;
use Illuminate\Http\Request;

class DeferredItemController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = DeferredItem::where('tenant_id', $this->tid())
            ->with(['deferredAccount', 'recognitionAccount'])
            ->when($request->filled('type'), fn($q) => $q->where('type', $request->type))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->latest();

        $items = $query->paginate(20)->withQueryString();

        return view('deferred.index', compact('items'));
    }

    public function create()
    {
        $tid      = $this->tid();
        $accounts = ChartOfAccount::where('tenant_id', $tid)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('deferred.create', compact('accounts'));
    }

    public function store(Request $request, DeferredItemService $service)
    {
        $data = $request->validate([
            'type'                   => 'required|in:deferred_revenue,prepaid_expense',
            'description'            => 'required|string|max:255',
            'total_amount'           => 'required|numeric|min:1',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date|after:start_date',
            'deferred_account_id'    => 'required|exists:chart_of_accounts,id',
            'recognition_account_id' => 'required|exists:chart_of_accounts,id',
            'reference_number'       => 'nullable|string|max:50',
        ]);

        $item = $service->create($data, $this->tid(), auth()->id());

        return redirect()->route('deferred.show', $item)
            ->with('success', "{$item->typeLabel()} berhasil dibuat dengan {$item->total_periods} jadwal amortisasi.");
    }

    public function show(DeferredItem $deferredItem)
    {
        abort_if($deferredItem->tenant_id !== $this->tid(), 403);
        $deferredItem->load(['schedules.journalEntry', 'deferredAccount', 'recognitionAccount', 'user']);
        return view('deferred.show', compact('deferredItem'));
    }

    public function postSchedule(DeferredItemSchedule $schedule, DeferredItemService $service)
    {
        abort_if($schedule->deferredItem->tenant_id !== $this->tid(), 403);
        abort_if($schedule->isPosted(), 403, 'Jadwal sudah diposting.');

        $success = $service->postSchedule($schedule, auth()->id());

        if ($success) {
            return back()->with('success', "Jurnal amortisasi periode {$schedule->period_number} berhasil diposting.");
        }

        return back()->with('error', 'Gagal memposting jurnal. Periksa log untuk detail.');
    }

    public function cancel(DeferredItem $deferredItem)
    {
        abort_if($deferredItem->tenant_id !== $this->tid(), 403);
        abort_if(! $deferredItem->isActive(), 403, 'Hanya item aktif yang bisa dibatalkan.');

        $deferredItem->update(['status' => 'cancelled']);
        $deferredItem->schedules()->where('status', 'pending')->update(['status' => 'skipped']);

        return back()->with('success', 'Item deferred berhasil dibatalkan.');
    }
}
