<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Services\DocumentOcrService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessOcrQueue extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'documents:process-ocr
                            {--batch=50 : Number of documents to process per batch}
                            {--tenant= : Process documents for specific tenant}
                            {--dry-run : Show what would be processed without actually processing}';

    /**
     * The console command description.
     */
    protected $description = 'Process OCR for documents that haven\'t been processed yet';

    protected DocumentOcrService $ocrService;

    /**
     * Create a new command instance.
     */
    public function __construct(DocumentOcrService $ocrService)
    {
        parent::__construct();
        $this->ocrService = $ocrService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $batchSize = (int) $this->option('batch');
        $tenantId = $this->option('tenant');
        $dryRun = $this->option('dry-run');

        $this->info('🔍 Starting OCR processing...');
        $this->line("   Batch size: {$batchSize}");
        $this->line('   Tenant: '.($tenantId ?? 'All'));
        $this->line('   Dry run: '.($dryRun ? 'Yes' : 'No'));

        // Build query for documents needing OCR
        $query = Document::where('has_ocr', false)
            ->whereIn('file_type', ['pdf', 'image/jpeg', 'image/png', 'tiff', 'jpg', 'png']);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $documents = $query->limit($batchSize)->get();

        if ($documents->isEmpty()) {
            $this->info('✅ No documents found that need OCR processing');

            return Command::SUCCESS;
        }

        $this->newLine();
        $this->info("📄 Found {$documents->count()} documents to process");

        if ($dryRun) {
            $this->newLine();
            $this->info('📋 Documents that would be processed:');
            $this->table(
                ['ID', 'Title', 'File Type', 'Tenant', 'Created At'],
                $documents->map(function ($doc) {
                    return [
                        $doc->id,
                        $doc->title,
                        $doc->file_type,
                        $doc->tenant_id,
                        $doc->created_at->format('d M Y H:i'),
                    ];
                })->toArray()
            );

            return Command::SUCCESS;
        }

        // Process documents
        $this->newLine();
        $this->info('⚙️ Processing OCR...');

        $bar = $this->output->createProgressBar($documents->count());
        $bar->start();

        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        foreach ($documents as $document) {
            try {
                $success = $this->ocrService->processDocument($document);

                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = "Document {$document->id}: OCR processing returned false";
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = "Document {$document->id}: {$e->getMessage()}";

                Log::error("OCR processing failed for document {$document->id}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->newLine();

        // Display results
        $this->info('📊 OCR Processing Results:');
        $this->line("  ✅ Success: {$results['success']}");

        if ($results['failed'] > 0) {
            $this->error("  ❌ Failed: {$results['failed']}");

            $this->newLine();
            $this->warn('Error details:');
            foreach ($results['errors'] as $error) {
                $this->line("  • {$error}");
            }
        }

        // Log results
        Log::info('OCR batch processing completed', [
            'total' => $documents->count(),
            'success' => $results['success'],
            'failed' => $results['failed'],
        ]);

        $this->newLine();
        $this->info('✅ OCR processing completed!');

        return Command::SUCCESS;
    }
}
