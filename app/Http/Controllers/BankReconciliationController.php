<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\ActivityLog;
use App\Services\BankReconciliationAiService;
use Illuminate\Http\Request;

class BankReconciliationController extends Controller
{
    public function __construct(private BankReconciliationAiService $ai) {}

    public function index(Request $request)
    {
        $tenantId = auth()->user()->tenant_id;
        $accounts = BankAccount::where('tenant_id', $tenantId)->where('is_active', true)->get();

        $query = BankStatement::where('tenant_id', $tenantId)
            ->with('bankAccount');

        // Filter by bank account
        if ($request->filled('account_id')) {
            $query->where('bank_account_id', $request->account_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('from')) {
            $query->whereDate('transaction_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('transaction_date', '<=', $request->to);
        }

        $statements = $query->latest('transaction_date')->paginate(50)->withQueryString();

        // Summary stats
        $baseQuery = BankStatement::where('tenant_id', $tenantId);
        if ($request->filled('account_id')) {
            $baseQuery->where('bank_account_id', $request->account_id);
        }

        $summary = [
            'total'     => (clone $baseQuery)->count(),
            'matched'   => (clone $baseQuery)->where('status', 'matched')->count(),
            'unmatched' => (clone $baseQuery)->where('status', 'unmatched')->count(),
            'credit'    => (clone $baseQuery)->where('type', 'credit')->sum('amount'),
            'debit'     => (clone $baseQuery)->where('type', 'debit')->sum('amount'),
        ];

        // Unmatched transactions from ERP (journal entries with kas/bank accounts) for manual matching
        $unmatchedErp = collect();
        if ($request->filled('account_id') || $statements->where('status', 'unmatched')->count() > 0) {
            $cashAccountIds = \App\Models\ChartOfAccount::where('tenant_id', $tenantId)
                ->whereIn('code', ['1101', '1102'])
                ->pluck('id');

            if ($cashAccountIds->isNotEmpty()) {
                $unmatchedErp = \App\Models\JournalEntryLine::whereIn('account_id', $cashAccountIds)
                    ->whereHas('journalEntry', fn($q) => $q
                        ->where('tenant_id', $tenantId)
                        ->where('status', 'posted')
                    )
                    ->with('journalEntry')
                    ->orderByDesc('id')
                    ->limit(100)
                    ->get()
                    ->map(fn($line) => [
                        'id'          => $line->journalEntry->id,
                        'number'      => $line->journalEntry->number,
                        'date'        => $line->journalEntry->date->format('Y-m-d'),
                        'description' => $line->journalEntry->description,
                        'amount'      => $line->debit > 0 ? $line->debit : $line->credit,
                        'type'        => $line->debit > 0 ? 'debit' : 'credit',
                    ]);
            }
        }

        return view('bank.reconciliation', compact('accounts', 'statements', 'summary', 'unmatchedErp'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|integer',
            'csv_file'        => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $tenantId  = auth()->user()->tenant_id;
        $account   = BankAccount::where('tenant_id', $tenantId)->findOrFail($request->bank_account_id);
        $file      = $request->file('csv_file');
        $rows      = array_map('str_getcsv', file($file->getRealPath()));
        $header    = array_shift($rows);
        $imported  = 0;

        foreach ($rows as $row) {
            if (count($row) < 4) continue;
            [$date, $description, $type, $amount] = $row;
            BankStatement::firstOrCreate(
                [
                    'tenant_id'       => $tenantId,
                    'bank_account_id' => $account->id,
                    'transaction_date'=> date('Y-m-d', strtotime($date)),
                    'description'     => trim($description),
                    'amount'          => (float) str_replace([',', ' '], '', $amount),
                ],
                ['type' => strtolower(trim($type)) === 'kredit' ? 'credit' : 'debit', 'status' => 'unmatched']
            );
            $imported++;
        }

        ActivityLog::record('bank_import', "Import {$imported} mutasi rekening {$account->account_name}");

        return back()->with('success', "{$imported} baris berhasil diimpor.");
    }

    public function match(Request $request, BankStatement $statement)
    {
        abort_if($statement->tenant_id !== auth()->user()->tenant_id, 403);
        $statement->update(['status' => 'matched', 'matched_transaction_id' => $request->transaction_id]);
        return back()->with('success', 'Transaksi berhasil dicocokkan.');
    }

    // ── AI endpoints ─────────────────────────────────────────────────

    public function aiMatchAll()
    {
        $results = $this->ai->matchAll(auth()->user()->tenant_id);
        return response()->json($results);
    }

    public function aiMatchOne(BankStatement $statement)
    {
        abort_if($statement->tenant_id !== auth()->user()->tenant_id, 403);
        $statement->load('bankAccount');
        return response()->json($this->ai->matchStatement($statement, auth()->user()->tenant_id));
    }

    public function aiApplyMatch(Request $request, BankStatement $statement)
    {
        abort_if($statement->tenant_id !== auth()->user()->tenant_id, 403);
        $request->validate(['transaction_id' => 'required|integer']);
        $this->ai->applyMatch($statement, $request->transaction_id);
        ActivityLog::record('bank_ai_match', "AI match: statement #{$statement->id} → transaksi #{$request->transaction_id}");
        return response()->json(['ok' => true]);
    }
}
