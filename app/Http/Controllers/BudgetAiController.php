<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BudgetAiController extends Controller
{
    public function __construct(private BudgetAiService $ai)
    {
    }

    private function tid(): int
    {
        return auth()->user()->tenant_id;
    }

    /**
     * GET /budget/ai/overrun-prediction?period=YYYY-MM
     * Prediksi overrun untuk semua budget di periode tersebut.
     */
    public function overrunPrediction(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));

        $budgets = Budget::where('tenant_id', $this->tid())
            ->where('period', $period)
            ->where('status', 'active')
            ->get();

        try {
            $predictions = $this->ai->predictOverrun($this->tid(), $period, $budgets);
            return response()->json(['predictions' => $predictions]);
        } catch (\Throwable $e) {
            Log::error("Budget AI error: " . $e->getMessage());
            return response()->json(['error' => 'AI analysis temporarily unavailable. Please try again later.'], 500);
        }
    }

    /**
     * GET /budget/ai/suggest-allocation?period=YYYY-MM
     * Suggest alokasi budget berdasarkan histori tahun lalu.
     */
    public function suggestAllocation(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));

        try {
            $suggestions = $this->ai->suggestAllocation($this->tid(), $period);
            return response()->json(['suggestions' => $suggestions]);
        } catch (\Throwable $e) {
            Log::error("Budget AI error: " . $e->getMessage());
            return response()->json(['error' => 'AI analysis temporarily unavailable. Please try again later.'], 500);
        }
    }
}
