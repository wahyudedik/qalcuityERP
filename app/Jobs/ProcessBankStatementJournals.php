<?php

namespace App\Jobs;

use App\DTOs\JournalPreviewDTO;
use App\Models\AccountingPeriod;
use App\Models\BankStatement;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\BankStatementAutoJournalService;
use App\Services\DocumentNumberService;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ProcessBankStatementJournals
 *
 * Background job untuk generate dan post journals dari bank statements
 *
 * Features:
 * - Batch processing dengan progress tracking
 * - Retry mechanism untuk failures
 * - Real-time progress updates via cache
 * - Detailed error logging
 * - Summary report generation
 */
class ProcessBankStatementJournals implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum retry attempts
     */
    public int $tries = 3;

    /**
     * Maximum execution time (seconds)
     */
    public int $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     *
     * @param  array  $statementIds  Array of bank statement IDs
     * @param  int  $userId  User ID yang trigger job
     * @param  int  $tenantId  Tenant ID
     * @param  string  $jobId  Unique job ID untuk tracking
     * @param  bool  $autoPost  Auto post setelah generate
     */
    public function __construct(
        public array $statementIds,
        public int $userId,
        public int $tenantId,
        public string $jobId,
        public bool $autoPost = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BankStatementAutoJournalService $journalService): void
    {
        Log::info('ProcessBankStatementJournals started', [
            'job_id' => $this->jobId,
            'total_statements' => count($this->statementIds),
            'auto_post' => $this->autoPost,
        ]);

        // Initialize progress tracking
        $this->updateProgress(0, count($this->statementIds), 'Initializing...');

        $results = [
            'success' => 0,
            'failed' => 0,
            'journals' => [],
            'errors' => [],
        ];

        $total = count($this->statementIds);

        // Load all statements
        $statements = BankStatement::whereIn('id', $this->statementIds)
            ->where('tenant_id', $this->tenantId)
            ->get();

        if ($statements->isEmpty()) {
            Log::warning('No statements found for job', ['job_id' => $this->jobId]);
            $this->updateProgress(0, 0, 'No statements found', 'failed');

            return;
        }

        // Process each statement individually for better error handling
        foreach ($statements as $index => $statement) {
            try {
                // Update progress
                $currentProgress = $index + 1;
                $this->updateProgress(
                    $currentProgress,
                    $statements->count(),
                    "Processing statement {$currentProgress}/{$statements->count()}..."
                );

                // Skip if already journalized
                if ($statement->status === 'journalized') {
                    Log::warning("Statement #{$statement->id} sudah journalized, skip");

                    continue;
                }

                // Use service method untuk generate dan post
                // Kita perlu process satu per satu untuk tracking
                $preview = $journalService->generateJournalFromStatement($statement);

                // Validate preview
                $errors = $preview->validate();
                if (! empty($errors)) {
                    throw new \Exception('Validation failed: '.implode(', ', $errors));
                }

                // Create journal via DB transaction manual
                DB::transaction(function () use ($statement, $preview, &$results) {
                    // Create journal entry
                    $journal = $this->createJournalEntry($statement, $preview);

                    // Auto post if enabled
                    if ($this->autoPost) {
                        $journal->post($this->userId);
                    }

                    // Update statement status
                    $statement->update([
                        'status' => 'journalized',
                        'matched_transaction_id' => $journal->id,
                    ]);

                    // Track success
                    $results['success']++;
                    $results['journals'][] = [
                        'statement_id' => $statement->id,
                        'journal_id' => $journal->id,
                        'journal_number' => $journal->number ?? null,
                    ];

                    Log::info("Statement #{$statement->id} processed successfully", [
                        'journal_id' => $journal->id,
                    ]);
                });

            } catch (\Exception $e) {
                // Track failure
                $results['failed']++;
                $results['errors'][] = [
                    'statement_id' => $statement->id,
                    'error' => $e->getMessage(),
                    'trace' => config('app.debug') ? $e->getTraceAsString() : null,
                ];

                Log::error("Statement #{$statement->id} failed", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Don't throw - continue processing other statements
            }
        }

        // Save final results to cache
        $this->saveResults($results);

        // Update final progress
        $this->updateProgress(
            $statements->count(),
            $statements->count(),
            "Completed: {$results['success']} success, {$results['failed']} failed"
        );

        Log::info('ProcessBankStatementJournals completed', [
            'job_id' => $this->jobId,
            'success' => $results['success'],
            'failed' => $results['failed'],
        ]);
    }

    /**
     * Create journal entry from preview
     */
    private function createJournalEntry(BankStatement $statement, JournalPreviewDTO $preview): JournalEntry
    {
        $docNumberService = app(DocumentNumberService::class);

        // Generate journal number
        $periodKey = $statement->transaction_date->format('Y');
        $journalNumber = $docNumberService->generate($statement->tenant_id, 'journal_entry', 'JE', $periodKey);

        // Find accounting period
        $period = AccountingPeriod::findForDate($statement->tenant_id, $statement->transaction_date);

        // Create journal entry
        $journal = JournalEntry::create([
            'tenant_id' => $statement->tenant_id,
            'period_id' => $period?->id,
            'user_id' => $this->userId,
            'number' => $journalNumber,
            'date' => $statement->transaction_date,
            'description' => $preview->description,
            'reference' => $preview->reference,
            'currency_code' => 'IDR',
            'currency_rate' => 1,
            'status' => 'draft',
            'notes' => "Auto-generated from bank statement via AI ({$preview->confidence} confidence)",
        ]);

        // Create journal lines
        foreach ($preview->lines as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $journal->id,
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? $preview->description,
            ]);
        }

        return $journal;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessBankStatementJournals failed completely', [
            'job_id' => $this->jobId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Update progress dengan error
        $this->updateProgress(
            0,
            count($this->statementIds),
            'FAILED: '.$exception->getMessage(),
            'failed'
        );

        // Save error results
        $this->saveResults([
            'success' => 0,
            'failed' => count($this->statementIds),
            'journals' => [],
            'errors' => [
                [
                    'statement_id' => 'all',
                    'error' => $exception->getMessage(),
                    'trace' => config('app.debug') ? $exception->getTraceAsString() : null,
                ],
            ],
        ]);
    }

    /**
     * Update progress tracking in cache
     */
    private function updateProgress(
        int $processed,
        int $total,
        string $message,
        string $status = 'processing'
    ): void {
        $percentage = $total > 0 ? round(($processed / $total) * 100, 2) : 0;

        $progress = [
            'job_id' => $this->jobId,
            'status' => $status,
            'processed' => $processed,
            'total' => $total,
            'percentage' => $percentage,
            'message' => $message,
            'updated_at' => now()->toIso8601String(),
        ];

        // Cache dengan TTL 1 jam
        Cache::put(
            "bank_journal_progress:{$this->jobId}",
            $progress,
            now()->addHour()
        );
    }

    /**
     * Save job results to cache
     */
    private function saveResults(array $results): void
    {
        $data = [
            'job_id' => $this->jobId,
            'status' => $results['failed'] > 0 ? 'completed_with_errors' : 'completed',
            'summary' => [
                'total' => count($this->statementIds),
                'success' => $results['success'],
                'failed' => $results['failed'],
            ],
            'journals' => $results['journals'],
            'errors' => $results['errors'],
            'completed_at' => now()->toIso8601String(),
        ];

        // Cache dengan TTL 24 jam
        Cache::put(
            "bank_journal_results:{$this->jobId}",
            $data,
            now()->addDay()
        );
    }

    /**
     * Get progress data
     */
    public static function getProgress(string $jobId): ?array
    {
        return Cache::get("bank_journal_progress:{$jobId}");
    }

    /**
     * Get job results
     */
    public static function getResults(string $jobId): ?array
    {
        return Cache::get("bank_journal_results:{$jobId}");
    }

    /**
     * Clean up old progress data
     */
    public static function cleanup(string $jobId): void
    {
        Cache::forget("bank_journal_progress:{$jobId}");
        Cache::forget("bank_journal_results:{$jobId}");
    }
}
