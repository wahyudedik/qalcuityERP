<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\ActivityLog;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\RecurringJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    public function index(Request $request)
    {
        $query = JournalEntry::with(['user', 'period'])
            ->where('tenant_id', $this->tid());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('number', 'like', "%$s%")
                ->orWhere('description', 'like', "%$s%"));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $journals = $query->orderByDesc('date')->paginate(20)->withQueryString();

        return view('accounting.journals.index', compact('journals'));
    }

    public function create()
    {
        $tid = $this->tid();
        $accounts = ChartOfAccount::where('tenant_id', $tid)
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();
        $periods = AccountingPeriod::where('tenant_id', $tid)
            ->where('status', 'open')
            ->orderByDesc('start_date')
            ->get();

        return view('accounting.journals.create', compact('accounts', 'periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'description' => 'required|string|max:255',
            'period_id' => 'nullable|exists:accounting_periods,id',
            'currency_code' => 'nullable|string|size:3',
            'currency_rate' => 'nullable|numeric|min:0',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $tid = $this->tid();

        // Validasi balance
        $totalDebit = collect($data['lines'])->sum(fn($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = collect($data['lines'])->sum(fn($l) => (float) ($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => 'Jurnal tidak balance. Total debit harus sama dengan total kredit.'])->withInput();
        }

        // Cek period locking (period + fiscal year)
        app(\App\Services\PeriodLockService::class)->assertNotLocked($tid, $data['date'], 'Jurnal');

        if (!empty($data['period_id'])) {
            $period = AccountingPeriod::find($data['period_id']);
            if ($period && $period->isLocked()) {
                return back()->withErrors(['period_id' => 'Periode ini sudah dikunci.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $tid) {
            $journal = JournalEntry::create([
                'tenant_id' => $tid,
                'period_id' => $data['period_id'] ?? null,
                'user_id' => auth()->id(),
                'number' => JournalEntry::generateNumber($tid),
                'date' => $data['date'],
                'description' => $data['description'],
                'currency_code' => $data['currency_code'] ?? 'IDR',
                'currency_rate' => $data['currency_rate'] ?? 1,
                'status' => 'draft',
            ]);

            foreach ($data['lines'] as $line) {
                $journal->lines()->create([
                    'account_id' => $line['account_id'],
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                    'description' => $line['description'] ?? null,
                ]);
            }

            ActivityLog::record('journal_created', "Jurnal {$journal->number} dibuat", $journal);
        });

        return redirect()->route('journals.index')->with('success', 'Jurnal berhasil dibuat.');
    }

    public function show(JournalEntry $journal)
    {
        abort_if($journal->tenant_id !== $this->tid(), 403);
        $journal->load(['lines.account', 'user', 'period', 'postedBy']);
        return view('accounting.journals.show', compact('journal'));
    }

    public function post(JournalEntry $journal)
    {
        abort_if($journal->tenant_id !== $this->tid(), 403);
        abort_if($journal->status !== 'draft', 403, 'Hanya jurnal draft yang bisa diposting.');

        // BUG-FIN-002 FIX: Check period lock before posting journal
        // Prevent creating draft in open period, then posting after period closed
        $periodLockService = app(\App\Services\PeriodLockService::class);
        if ($periodLockService->isLocked($journal->tenant_id, $journal->date->toDateString())) {
            $lockInfo = $periodLockService->getLockInfo($journal->tenant_id, $journal->date->toDateString());
            return back()->with('error', "Periode {$lockInfo} sudah dikunci. Jurnal tidak dapat diposting.");
        }

        try {
            $journal->post(auth()->id());
            ActivityLog::record('journal_posted', "Jurnal {$journal->number} diposting", $journal);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "Jurnal {$journal->number} berhasil diposting.");
    }

    public function reverse(Request $request, JournalEntry $journal)
    {
        abort_if($journal->tenant_id !== $this->tid(), 403);
        abort_if($journal->status !== 'posted', 403, 'Hanya jurnal posted yang bisa dibalik.');

        $data = $request->validate(['date' => 'required|date']);

        $reversal = $journal->reverse(auth()->id(), $data['date']);
        $reversal->post(auth()->id());

        ActivityLog::record('journal_reversed', "Jurnal {$journal->number} dibalik → {$reversal->number}", $journal);

        return redirect()->route('journals.show', $reversal)->with('success', "Jurnal pembalik {$reversal->number} berhasil dibuat.");
    }

    // ── Recurring Journals ────────────────────────────────────────

    public function recurringIndex()
    {
        $recurring = RecurringJournal::where('tenant_id', $this->tid())
            ->orderByDesc('created_at')
            ->get();

        $accounts = ChartOfAccount::where('tenant_id', $this->tid())
            ->where('is_active', true)
            ->where('is_header', false)
            ->orderBy('code')
            ->get();

        return view('accounting.journals.recurring', compact('recurring', 'accounts'));
    }

    public function storeRecurring(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'frequency' => 'required|in:daily,weekly,monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $totalDebit = collect($data['lines'])->sum(fn($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = collect($data['lines'])->sum(fn($l) => (float) ($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->withErrors(['lines' => 'Jurnal tidak balance.'])->withInput();
        }

        RecurringJournal::create([
            'tenant_id' => $this->tid(),
            'user_id' => auth()->id(),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'frequency' => $data['frequency'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'next_run_date' => $data['start_date'],
            'is_active' => true,
            'lines' => $data['lines'],
        ]);

        return back()->with('success', 'Jurnal berulang berhasil disimpan.');
    }

    public function toggleRecurring(RecurringJournal $recurring)
    {
        abort_if($recurring->tenant_id !== $this->tid(), 403);
        $recurring->update(['is_active' => !$recurring->is_active]);
        return back()->with('success', 'Status jurnal berulang diperbarui.');
    }
}
