<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessBankStatementJournals;
use App\Models\ActivityLog;
use App\Models\BankAccount;
use App\Models\BankStatement;
use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use App\Services\BankFormatParser;
use App\Services\BankReconciliationAiService;
use App\Services\BankStatementAutoJournalService;
use App\Services\BankStatementPdfParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BankReconciliationController extends Controller
{
    public function __construct(
        private BankReconciliationAiService $ai,
        private BankStatementAutoJournalService $journalService
    ) {}

    public function index(Request $request)
    {
        $tenantId = $this->tenantId();
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
            'total' => (clone $baseQuery)->count(),
            'matched' => (clone $baseQuery)->where('status', 'matched')->count(),
            'unmatched' => (clone $baseQuery)->where('status', 'unmatched')->count(),
            'credit' => (clone $baseQuery)->where('type', 'credit')->sum('amount'),
            'debit' => (clone $baseQuery)->where('type', 'debit')->sum('amount'),
        ];

        // Unmatched transactions from ERP (journal entries with kas/bank accounts) for manual matching
        $unmatchedErp = collect();
        if ($request->filled('account_id') || $statements->where('status', 'unmatched')->count() > 0) {
            $cashAccountIds = ChartOfAccount::where('tenant_id', $tenantId)
                ->whereIn('code', ['1101', '1102'])
                ->pluck('id');

            if ($cashAccountIds->isNotEmpty()) {
                $unmatchedErp = JournalEntryLine::whereIn('account_id', $cashAccountIds)
                    ->whereHas(
                        'journalEntry',
                        fn ($q) => $q
                            ->where('tenant_id', $tenantId)
                            ->where('status', 'posted')
                    )
                    ->with('journalEntry')
                    ->orderByDesc('id')
                    ->limit(100)
                    ->get()
                    ->map(fn ($line) => [
                        'id' => $line->journalEntry->id,
                        'number' => $line->journalEntry->number,
                        'date' => $line->journalEntry->date->format('Y-m-d'),
                        'description' => $line->journalEntry->description,
                        'amount' => $line->debit > 0 ? $line->debit : $line->credit,
                        'type' => $line->debit > 0 ? 'debit' : 'credit',
                    ]);
            }
        }

        return view('bank.reconciliation', compact('accounts', 'statements', 'summary', 'unmatchedErp'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'bank_account_id' => 'required|integer',
            'csv_file' => 'required|file|mimes:csv,txt,pdf,jpg,jpeg,png|max:10240', // Max 10MB, support PDF & images
            'bank_format' => 'nullable|string|in:bca,mandiri,bni,bri,generic',
        ]);

        $tenantId = $this->tenantId();
        $account = BankAccount::where('tenant_id', $tenantId)->findOrFail($request->bank_account_id);
        $file = $request->file('csv_file');
        $bankFormat = $request->input('bank_format');

        try {
            $parsedStatements = [];

            // Check file type
            $extension = strtolower($file->getClientOriginalExtension());

            if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'])) {
                // Use PDF/OCR Parser
                $pdfParser = new BankStatementPdfParser;
                $parsedStatements = $pdfParser->parse($file);

                $this->logInfo('PDF/OCR parsing completed', [
                    'statements_count' => count($parsedStatements),
                ]);
            } else {
                // Use CSV Parser
                $parser = new BankFormatParser;
                $parsedStatements = $parser->parse($file, $bankFormat);
            }

            if (empty($parsedStatements)) {
                return back()->with('error', 'Tidak ada data valid yang ditemukan dalam file.');
            }

            // Import parsed statements to database
            $imported = 0;
            $skipped = 0;

            foreach ($parsedStatements as $statement) {
                $created = BankStatement::firstOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'bank_account_id' => $account->id,
                        'transaction_date' => $statement['transaction_date'],
                        'description' => $statement['description'],
                        'amount' => $statement['amount'],
                    ],
                    [
                        'type' => $statement['type'],
                        'balance' => $statement['balance'] ?? null,
                        'reference' => $statement['reference'] ?? null,
                        'status' => 'unmatched',
                    ]
                );

                if ($created->wasRecentlyCreated) {
                    $imported++;
                } else {
                    $skipped++;
                }
            }

            // Update account last import timestamp
            $account->update(['last_import_at' => now()]);

            // Log activity
            $fileType = in_array($extension, ['pdf', 'jpg', 'jpeg', 'png']) ? 'PDF/OCR' : 'CSV';
            $message = "Import {$imported} mutasi rekening {$account->account_name} ({$fileType})";
            if ($skipped > 0) {
                $message .= " ({$skipped} duplikat dilewati)";
            }
            ActivityLog::record('bank_import', $message);

            $successMessage = "{$imported} baris berhasil diimpor.";
            if ($skipped > 0) {
                $successMessage .= " {$skipped} duplikat dilewati.";
            }

            return back()->with('success', $successMessage);

        } catch (\Exception $e) {
            Log::error('Bank statement import failed', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'account_id' => $account->id,
            ]);

            return back()->with('error', 'Gagal mengimpor file: '.$e->getMessage());
        }
    }

    /**
     * Safe logging helper
     */
    private function logInfo(string $message, array $context = []): void
    {
        try {
            Log::info($message, $context);
        } catch (\Throwable $e) {
            error_log("INFO: {$message} - ".json_encode($context));
        }
    }

    /**
     * Get supported bank formats info
     */
    public function getBankFormats()
    {
        $parser = new BankFormatParser;

        return response()->json([
            'banks' => $parser->getSupportedBanks(),
            'samples' => [
                'bca' => route('bank.sample', 'bca'),
                'mandiri' => route('bank.sample', 'mandiri'),
                'bni' => route('bank.sample', 'bni'),
                'bri' => route('bank.sample', 'bri'),
                'generic' => route('bank.sample', 'generic'),
            ],
        ]);
    }

    /**
     * Download sample CSV file for testing
     */
    public function downloadSample(string $bank)
    {
        $allowedBanks = ['bca', 'mandiri', 'bni', 'bri', 'generic'];

        if (! in_array($bank, $allowedBanks)) {
            abort(404, 'Sample tidak tersedia');
        }

        $samplePath = storage_path("app/bank_samples/{$bank}_sample.csv");

        if (! file_exists($samplePath)) {
            abort(404, 'File sample tidak ditemukan');
        }

        return response()->download($samplePath, "sample_{$bank}_format.csv");
    }

    public function match(Request $request, BankStatement $statement)
    {
        abort_if($statement->tenant_id !== $this->tenantId(), 403);
        $statement->update(['status' => 'matched', 'matched_transaction_id' => $request->transaction_id]);

        return back()->with('success', 'Transaksi berhasil dicocokkan.');
    }

    // ── AI endpoints ─────────────────────────────────────────────────

    public function aiMatchAll()
    {
        $results = $this->ai->matchAll($this->tenantId());

        return response()->json($results);
    }

    public function aiMatchOne(BankStatement $statement)
    {
        abort_if($statement->tenant_id !== $this->tenantId(), 403);
        $statement->load('bankAccount');

        return response()->json($this->ai->matchStatement($statement, $this->tenantId()));
    }

    public function aiApplyMatch(Request $request, BankStatement $statement)
    {
        abort_if($statement->tenant_id !== $this->tenantId(), 403);
        $request->validate(['transaction_id' => 'required|integer']);
        $this->ai->applyMatch($statement, $request->transaction_id);
        ActivityLog::record('bank_ai_match', "AI match: statement #{$statement->id} → transaksi #{$request->transaction_id}");

        return response()->json(['ok' => true]);
    }

    // ── AI Journal Generation endpoints ───────────────────────────────

    /**
     * 3.1 Generate single journal from statement
     * POST /bank/ai/generate-journal/{statement}
     */
    public function aiGenerateJournal(BankStatement $statement)
    {
        abort_if($statement->tenant_id !== $this->tenantId(), 403);

        try {
            $preview = $this->journalService->generateJournalFromStatement($statement);

            // Validate
            $errors = $preview->validate();
            if (! empty($errors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $errors,
                    'preview' => $preview->toArray(),
                ], 422);
            }

            ActivityLog::record('bank_ai_journal', "AI generate journal: statement #{$statement->id}");

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil digenerate',
                'preview' => $preview->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('AI journal generation failed', [
                'statement_id' => $statement->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal generate journal: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3.2 Batch generate multiple journals
     * POST /bank/ai/generate-journals/bulk
     */
    public function aiGenerateJournalsBulk(Request $request)
    {
        $request->validate([
            'statement_ids' => 'required|array',
            'statement_ids.*' => 'required|integer|exists:bank_statements,id',
            'auto_post' => 'boolean',
        ]);

        $tenantId = $this->tenantId();
        $userId = $this->authenticatedUserId();
        $autoPost = $request->input('auto_post', false);

        // Load statements with tenant check
        $statements = BankStatement::whereIn('id', $request->statement_ids)
            ->where('tenant_id', $tenantId)
            ->get();

        if ($statements->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada statement yang valid',
            ], 422);
        }

        try {
            $results = $this->journalService->generateJournalsFromStatements(
                $statements,
                $userId,
                $autoPost
            );

            ActivityLog::record(
                'bank_ai_journal_bulk',
                "AI bulk generate: {$results['summary']['success']} success, {$results['summary']['failed']} failed"
            );

            return response()->json([
                'success' => true,
                'message' => "Bulk generation completed: {$results['summary']['success']} success, {$results['summary']['failed']} failed",
                'summary' => $results['summary'],
                'journals' => $results['journals'],
                'failed' => $results['failed'],
            ]);

        } catch (\Exception $e) {
            Log::error('AI bulk journal generation failed', [
                'statement_ids' => $request->statement_ids,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk generate: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3.3 Preview journal without saving
     * POST /bank/ai/preview-journal/{statement}
     */
    public function aiPreviewJournal(BankStatement $statement)
    {
        abort_if($statement->tenant_id !== $this->tenantId(), 403);

        try {
            $preview = $this->journalService->previewJournal($statement);

            return response()->json([
                'success' => true,
                'preview' => $preview->toArray(),
            ]);

        } catch (\Exception $e) {
            Log::error('AI journal preview failed', [
                'statement_id' => $statement->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal preview journal: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3.4 Approve and post single journal
     * POST /bank/ai/approve-and-post/{statement}
     */
    public function aiApproveAndPost(BankStatement $statement)
    {
        abort_if($statement->tenant_id !== $this->tenantId(), 403);

        $userId = $this->authenticatedUserId();

        try {
            // Generate dan auto-post journal
            $results = $this->journalService->autoPostJournals(
                collect([$statement->id]),
                $userId
            );

            if (empty($results['success'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate journal',
                ], 422);
            }

            $journalId = $results['success'][0]['journal_id'] ?? null;
            $journalNumber = $results['success'][0]['journal_number'] ?? null;

            ActivityLog::record(
                'bank_ai_journal_approve',
                "AI journal approved & posted: statement #{$statement->id} → journal #{$journalId}"
            );

            return response()->json([
                'success' => true,
                'message' => 'Journal berhasil di-approve dan di-post',
                'journal_id' => $journalId,
                'journal_number' => $journalNumber,
            ]);

        } catch (\Exception $e) {
            Log::error('AI journal approve & post failed', [
                'statement_id' => $statement->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal approve & post: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * 3.5 Bulk approve and post journals
     * POST /bank/ai/approve-and-post/bulk
     */
    public function aiApproveAndPostBulk(Request $request)
    {
        $request->validate([
            'statement_ids' => 'required|array',
            'statement_ids.*' => 'required|integer|exists:bank_statements,id',
        ]);

        $tenantId = $this->tenantId();
        $userId = $this->authenticatedUserId();

        // Load statements with tenant check
        $statements = BankStatement::whereIn('id', $request->statement_ids)
            ->where('tenant_id', $tenantId)
            ->where('status', '!=', 'journalized') // Skip already journalized
            ->get();

        if ($statements->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada statement yang valid untuk di-approve',
            ], 422);
        }

        try {
            // Generate dan auto-post journals
            $results = $this->journalService->generateJournalsFromStatements(
                $statements,
                $userId,
                true // autoPost = true
            );

            $successCount = count($results['success'] ?? []);
            $failedCount = count($results['failed'] ?? []);

            ActivityLog::record(
                'bank_ai_journal_bulk_approve',
                "AI bulk approve: {$successCount} success, {$failedCount} failed"
            );

            return response()->json([
                'success' => true,
                'message' => "Bulk approve completed: {$successCount} success, {$failedCount} failed",
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'journals' => $results['success'] ?? [],
                'errors' => $results['failed'] ?? [],
            ]);

        } catch (\Exception $e) {
            Log::error('AI bulk journal approve failed', [
                'statement_ids' => $request->statement_ids,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal bulk approve: '.$e->getMessage(),
            ], 500);
        }
    }

    // ── Bulk Auto-Generate & Post with Background Job ─────────────

    /**
     * 5.1 Auto-generate all journals untuk unmatched statements
     * POST /bank/ai/auto-generate-all
     */
    public function aiAutoGenerateAll(Request $request)
    {
        $request->validate([
            'auto_post' => 'boolean',
            'account_id' => 'nullable|integer',
        ]);

        $tenantId = $this->tenantId();
        $userId = $this->authenticatedUserId();
        $autoPost = $request->input('auto_post', false);
        $accountId = $request->input('account_id');

        // Get all unmatched/matched statements (not journalized yet)
        $query = BankStatement::where('tenant_id', $tenantId)
            ->whereIn('status', ['unmatched', 'matched']);

        if ($accountId) {
            $query->where('bank_account_id', $accountId);
        }

        $statements = $query->get();

        if ($statements->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada statement yang perlu diproses',
            ], 422);
        }

        // Generate unique job ID
        $jobId = (string) Str::uuid();

        // Dispatch background job
        ProcessBankStatementJournals::dispatch(
            $statements->pluck('id')->toArray(),
            $userId,
            $tenantId,
            $jobId,
            $autoPost
        );

        ActivityLog::record(
            'bank_ai_auto_generate',
            "Auto generate all: {$statements->count()} statements, job_id: {$jobId}"
        );

        return response()->json([
            'success' => true,
            'message' => "Background job dimulai untuk {$statements->count()} statements",
            'job_id' => $jobId,
            'total_statements' => $statements->count(),
        ]);
    }

    /**
     * 5.3 Check progress background job
     * GET /bank/ai/job-progress/{jobId}
     */
    public function aiJobProgress(string $jobId)
    {
        $progress = ProcessBankStatementJournals::getProgress($jobId);

        if (! $progress) {
            return response()->json([
                'success' => false,
                'message' => 'Job tidak ditemukan atau sudah expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * 5.4 Get job results setelah selesai
     * GET /bank/ai/job-results/{jobId}
     */
    public function aiJobResults(string $jobId)
    {
        $results = ProcessBankStatementJournals::getResults($jobId);

        if (! $results) {
            return response()->json([
                'success' => false,
                'message' => 'Results tidak ditemukan atau sudah expired',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * 5.5 Cleanup job data
     * DELETE /bank/ai/job-cleanup/{jobId}
     */
    public function aiJobCleanup(string $jobId)
    {
        ProcessBankStatementJournals::cleanup($jobId);

        return response()->json([
            'success' => true,
            'message' => 'Job data cleaned up',
        ]);
    }
}
