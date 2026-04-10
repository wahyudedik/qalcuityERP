<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Notifications\DocumentExpiryNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiringDocuments extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'documents:check-expiring
                            {--days=30 : Number of days to check for expiring documents}
                            {--notify : Send notifications to document owners}
                            {--report : Generate report of expiring documents}';

    /**
     * The console command description.
     */
    protected $description = 'Check for expiring documents and send notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $notify = $this->option('notify');
        $report = $this->option('report');

        $this->info("Checking for documents expiring within {$days} days...");

        // Get expiring documents
        $expiringDocuments = Document::whereNotNull('expires_at')
            ->where('expires_at', '>', now())
            ->where('expires_at', '<', now()->addDays($days))
            ->with('uploader')
            ->get();

        // Get expired documents
        $expiredDocuments = Document::whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->with('uploader')
            ->get();

        $this->newLine();
        $this->info("📊 Summary:");
        $this->line("  • Expiring soon: {$expiringDocuments->count()} documents");
        $this->line("  • Already expired: {$expiredDocuments->count()} documents");

        // Send notifications for expiring documents
        if ($notify && $expiringDocuments->isNotEmpty()) {
            $this->newLine();
            $this->info("📧 Sending notifications for expiring documents...");

            $notificationCount = 0;
            foreach ($expiringDocuments as $document) {
                if ($document->uploader) {
                    $daysUntilExpiry = now()->diffInDays($document->expires_at, false);
                    $document->uploader->notify(
                        new DocumentExpiryNotification($document, $daysUntilExpiry, false)
                    );
                    $notificationCount++;
                }
            }

            $this->info("✅ Sent {$notificationCount} notifications");
        }

        // Send notifications for expired documents
        if ($notify && $expiredDocuments->isNotEmpty()) {
            $this->newLine();
            $this->warn("⚠️ Sending notifications for expired documents...");

            $notificationCount = 0;
            foreach ($expiredDocuments as $document) {
                if ($document->uploader) {
                    $daysExpired = $document->expires_at->diffInDays(now());
                    $document->uploader->notify(
                        new DocumentExpiryNotification($document, $daysExpired, true)
                    );
                    $notificationCount++;
                }
            }

            $this->warn("✅ Sent {$notificationCount} notifications for expired documents");
        }

        // Generate report
        if ($report) {
            $this->newLine();
            $this->info("📋 Document Expiry Report:");
            $this->newLine();

            if ($expiringDocuments->isNotEmpty()) {
                $this->info("Expiring Documents (within {$days} days):");
                $this->table(
                    ['ID', 'Title', 'Category', 'Expires At', 'Days Left', 'Owner'],
                    $expiringDocuments->map(function ($doc) {
                        return [
                            $doc->id,
                            $doc->title,
                            $doc->category ?? 'General',
                            $doc->expires_at->format('d M Y'),
                            now()->diffInDays($doc->expires_at, false),
                            $doc->uploader?->name ?? 'Unknown',
                        ];
                    })->toArray()
                );
            }

            if ($expiredDocuments->isNotEmpty()) {
                $this->error("Expired Documents:");
                $this->table(
                    ['ID', 'Title', 'Category', 'Expired On', 'Days Expired', 'Owner'],
                    $expiredDocuments->map(function ($doc) {
                        return [
                            $doc->id,
                            $doc->title,
                            $doc->category ?? 'General',
                            $doc->expires_at->format('d M Y'),
                            $doc->expires_at->diffInDays(now()),
                            $doc->uploader?->name ?? 'Unknown',
                        ];
                    })->toArray()
                );
            }
        }

        // Log results
        Log::info('Document expiry check completed', [
            'expiring_count' => $expiringDocuments->count(),
            'expired_count' => $expiredDocuments->count(),
            'notifications_sent' => $notify ? ($expiringDocuments->count() + $expiredDocuments->count()) : 0,
        ]);

        $this->newLine();
        $this->info("✅ Document expiry check completed successfully!");

        return Command::SUCCESS;
    }
}
