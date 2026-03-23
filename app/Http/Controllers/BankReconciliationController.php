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

    public function index()
    {
        $tenantId = auth()->user()->tenant_id;
        $accounts = BankAccount::where('tenant_id', $tenantId)->get();
        $statements = BankStatement::where('tenant_id', $tenantId)
            ->with('bankAccount')
            ->latest('transaction_date')
            ->paginate(50);

        return view('bank.reconciliation', compact('accounts', 'statements'));
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
