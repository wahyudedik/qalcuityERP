<?php

namespace App\Http\Controllers;

use App\Models\AccountingPeriod;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Services\FinancialStatementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\DefaultCoaSeeder;
use Illuminate\Http\Request;

class AccountingController extends Controller
{
    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    // ── Chart of Accounts ─────────────────────────────────────────

    public function coa(Request $request)
    {
        $accounts = ChartOfAccount::where('tenant_id', $this->tid())
            ->with('parent')
            ->when($request->filled('type'),   fn($q) => $q->where('type', $request->type))
            ->when($request->filled('search'), fn($q) => $q->where(fn($q2) =>
                $q2->where('code', 'like', '%' . $request->search . '%')
                   ->orWhere('name', 'like', '%' . $request->search . '%')
            ))
            ->when($request->status === 'active',   fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderBy('code')
            ->get();

        $headers = ChartOfAccount::where('tenant_id', $this->tid())
            ->where('is_header', true)
            ->orderBy('code')
            ->get();

        return view('accounting.coa', compact('accounts', 'headers'));
    }

    public function storeCoa(Request $request)
    {
        $tid = $this->tid();

        $data = $request->validate([
            'code'           => 'required|string|max:20',
            'name'           => 'required|string|max:100',
            'type'           => 'required|in:asset,liability,equity,revenue,expense',
            'normal_balance' => 'required|in:debit,credit',
            // FIX BUG-015: Validasi parent_id harus filter tenant_id agar tidak bisa pakai COA tenant lain
            'parent_id'      => ['nullable', \Illuminate\Validation\Rule::exists('chart_of_accounts', 'id')->where('tenant_id', $tid)],
            'level'          => 'required|integer|min:1|max:5',
            'is_header'      => 'boolean',
            'description'    => 'nullable|string|max:255',
        ]);

        if (ChartOfAccount::where('tenant_id', $tid)->where('code', $data['code'])->exists()) {
            return back()->withErrors(['code' => 'Kode akun sudah digunakan.'])->withInput();
        }

        ChartOfAccount::create(array_merge($data, ['tenant_id' => $tid, 'is_active' => true]));

        return back()->with('success', 'Akun berhasil ditambahkan.');
    }

    public function updateCoa(Request $request, ChartOfAccount $account)
    {
        abort_if($account->tenant_id !== $this->tid(), 403);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'is_active'   => 'boolean',
            'description' => 'nullable|string|max:255',
        ]);

        $account->update($data);

        return back()->with('success', 'Akun berhasil diperbarui.');
    }

    public function destroyCoa(ChartOfAccount $account)
    {
        abort_if($account->tenant_id !== $this->tid(), 403);

        if ($account->journalLines()->exists()) {
            return back()->with('error', 'Akun tidak bisa dihapus karena sudah digunakan dalam jurnal.');
        }
        if ($account->children()->exists()) {
            return back()->with('error', 'Akun tidak bisa dihapus karena memiliki sub-akun.');
        }

        $account->delete();
        return back()->with('success', 'Akun berhasil dihapus.');
    }

    public function seedDefaultCoa()
    {
        DefaultCoaSeeder::seedForTenant($this->tid());
        return back()->with('success', 'COA default Indonesia berhasil dimuat.');
    }

    // ── Accounting Periods ────────────────────────────────────────

    public function periods()
    {
        $periods = AccountingPeriod::where('tenant_id', $this->tid())
            ->orderByDesc('start_date')
            ->get();

        return view('accounting.periods', compact('periods'));
    }

    public function storePeriod(Request $request)
    {
        $tid = $this->tid();

        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);

        // FIX BUG-018: Cegah periode yang overlap dengan periode yang sudah ada
        $overlap = AccountingPeriod::where('tenant_id', $tid)
            ->where('start_date', '<=', $data['end_date'])
            ->where('end_date', '>=', $data['start_date'])
            ->exists();

        if ($overlap) {
            return back()->withErrors(['start_date' => 'Periode ini tumpang tindih dengan periode akuntansi yang sudah ada.'])->withInput();
        }

        AccountingPeriod::create(array_merge($data, [
            'tenant_id' => $tid,
            'status'    => 'open',
        ]));

        return back()->with('success', 'Periode akuntansi berhasil dibuat.');
    }

    public function closePeriod(AccountingPeriod $period)
    {
        abort_if($period->tenant_id !== $this->tid(), 403);
        abort_if(! $period->isOpen(), 403, 'Periode sudah ditutup.');

        $period->update([
            'status'    => 'closed',
            'closed_by' => auth()->id(),
            'closed_at' => now(),
        ]);

        return back()->with('success', "Periode {$period->name} berhasil ditutup.");
    }

    public function lockPeriod(AccountingPeriod $period)
    {
        abort_if($period->tenant_id !== $this->tid(), 403);
        abort_if($period->isLocked(), 403, 'Periode sudah dikunci.');

        $period->update(['status' => 'locked']);

        return back()->with('success', "Periode {$period->name} berhasil dikunci. Tidak ada jurnal yang bisa diposting ke periode ini.");
    }

    // ── Trial Balance ─────────────────────────────────────────────

    public function trialBalance(Request $request)
    {
        $tid  = $this->tid();
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());

        $accounts = ChartOfAccount::where('tenant_id', $tid)
            ->where('is_header', false)
            ->where('is_active', true)
            ->with(['journalLines' => fn($q) => $q->whereHas('journalEntry', fn($je) =>
                $je->where('tenant_id', $tid)
                   ->where('status', 'posted')
                   ->whereBetween('date', [$from, $to])
            )])
            ->orderBy('code')
            ->get()
            ->map(function ($acc) {
                $debit  = $acc->journalLines->sum('debit');
                $credit = $acc->journalLines->sum('credit');
                return [
                    'code'   => $acc->code,
                    'name'   => $acc->name,
                    'type'   => $acc->getTypeLabel(),
                    'debit'  => $debit,
                    'credit' => $credit,
                    'balance'=> $acc->normal_balance === 'debit' ? $debit - $credit : $credit - $debit,
                ];
            })
            ->filter(fn($a) => $a['debit'] > 0 || $a['credit'] > 0);

        return view('accounting.trial-balance', compact('accounts', 'from', 'to'));
    }

    // ── Balance Sheet (Neraca) ────────────────────────────────────

    public function balanceSheet(Request $request)
    {
        $asOf = $request->get('as_of', now()->toDateString());
        $data = app(FinancialStatementService::class)->balanceSheet($this->tid(), $asOf);
        return view('accounting.balance-sheet', compact('data', 'asOf'));
    }

    public function balanceSheetPdf(Request $request)
    {
        $request->validate(['as_of' => 'required|date']);
        $asOf     = $request->as_of;
        $data     = app(FinancialStatementService::class)->balanceSheet($this->tid(), $asOf);
        $tenant   = auth()->user()->tenant;
        $pdf      = Pdf::loadView('accounting.pdf.balance-sheet', compact('data', 'asOf', 'tenant'))
                       ->setPaper('a4', 'portrait');
        return $pdf->download('neraca-' . $asOf . '.pdf');
    }

    // ── Income Statement (Laba Rugi) ──────────────────────────────

    public function incomeStatement(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());
        $data = app(FinancialStatementService::class)->incomeStatement($this->tid(), $from, $to);
        return view('accounting.income-statement', compact('data', 'from', 'to'));
    }

    public function incomeStatementPdf(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);
        $from   = $request->from;
        $to     = $request->to;
        $data   = app(FinancialStatementService::class)->incomeStatement($this->tid(), $from, $to);
        $tenant = auth()->user()->tenant;
        $pdf    = Pdf::loadView('accounting.pdf.income-statement', compact('data', 'from', 'to', 'tenant'))
                     ->setPaper('a4', 'portrait');
        return $pdf->download('laba-rugi-' . $from . '-sd-' . $to . '.pdf');
    }

    // ── Cash Flow Statement (Arus Kas) ────────────────────────────

    public function cashFlow(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to   = $request->get('to',   now()->toDateString());
        $data = app(FinancialStatementService::class)->cashFlowStatement($this->tid(), $from, $to);
        return view('accounting.cash-flow', compact('data', 'from', 'to'));
    }

    public function cashFlowPdf(Request $request)
    {
        $request->validate(['from' => 'required|date', 'to' => 'required|date|after_or_equal:from']);
        $from   = $request->from;
        $to     = $request->to;
        $data   = app(FinancialStatementService::class)->cashFlowStatement($this->tid(), $from, $to);
        $tenant = auth()->user()->tenant;
        $pdf    = Pdf::loadView('accounting.pdf.cash-flow', compact('data', 'from', 'to', 'tenant'))
                     ->setPaper('a4', 'portrait');
        return $pdf->download('arus-kas-' . $from . '-sd-' . $to . '.pdf');
    }
}
