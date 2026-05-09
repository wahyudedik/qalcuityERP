<?php

namespace App\Console\Commands;

use App\Services\NotificationDigestService;
use Illuminate\Console\Command;

class SendNotificationDigest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-digest 
                           {--daily : Send daily digest}
                           {--weekly : Send weekly digest}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification digest emails to users';

    protected NotificationDigestService $digestService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationDigestService $digestService)
    {
        parent::__construct();
        $this->digestService = $digestService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('daily')) {
            return $this->sendDailyDigest();
        }

        if ($this->option('weekly')) {
            return $this->sendWeeklyDigest();
        }

        // Send both if no option specified
        $dailyResult = $this->sendDailyDigest();
        $weeklyResult = $this->sendWeeklyDigest();

        return ($dailyResult === Command::SUCCESS && $weeklyResult === Command::SUCCESS)
            ? Command::SUCCESS
            : Command::FAILURE;
    }

    /**
     * Send daily digest.
     */
    protected function sendDailyDigest(): int
    {
        $this->info('Sending daily notification digest...');

        try {
            $sent = $this->digestService->sendDailyDigest();
            $this->info("✅ Daily digest sent to {$sent} user(s).");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Failed to send daily digest: '.$e->getMessage());

            return Command::FAILURE;
        }
    }

    /**
     * Send weekly digest.
     */
    protected function sendWeeklyDigest(): int
    {
        $this->info('Sending weekly notification digest...');

        try {
            $sent = $this->digestService->sendWeeklyDigest();
            $this->info("✅ Weekly digest sent to {$sent} user(s).");

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Failed to send weekly digest: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
