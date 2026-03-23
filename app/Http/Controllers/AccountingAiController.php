<?php

namespace App\Http\Controllers;

use App\Services\AccountingAiService;
use Illuminate\Http\Request;

class AccountingAiController extends Controller
{
    public function __construct(private AccountingAiService $ai) {}

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    /**
     * GET /accounting/ai/suggest-accounts?description=...&amount=...
     */
    public function suggestAccounts(Request $request)
    {
        $description = $request->string('description')->trim()->value();
        $amount      = (float) $request->input('amount', 0);

        if (strlen($description) < 3) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = $this->ai->suggestAccounts($this->tid(), $description, $amount);

        return response()->json(['suggestions' => $suggestions]);
    }

    /**
     * POST /accounting/ai/check-journal
     * Body: { lines: [{account_id, debit, credit}], date, description, total_amount }
     */
    public function checkJournal(Request $request)
    {
        $data = $request->validate([
            'lines'        => 'required|array',
            'date'         => 'required|date',
            'description'  => 'nullable|string',
            'total_amount' => 'nullable|numeric|min:0',
        ]);

        $result = $this->ai->detectJournalAnomalies(
            $this->tid(),
            $data['lines'],
            $data['date'],
            $data['description'] ?? '',
            (float) ($data['total_amount'] ?? 0),
        );

        return response()->json($result);
    }

    /**
     * GET /accounting/ai/categorize-statement?description=...&type=...&amount=...
     */
    public function categorizeStatement(Request $request)
    {
        $description = $request->string('description')->trim()->value();
        $type        = $request->input('type', 'debit'); // debit | credit
        $amount      = (float) $request->input('amount', 0);

        if (empty($description)) {
            return response()->json(['result' => null]);
        }

        $result = $this->ai->categorizeStatement($this->tid(), $description, $type, $amount);

        return response()->json(['result' => $result]);
    }
}
