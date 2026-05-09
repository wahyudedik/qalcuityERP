<?php

namespace App\Console\Commands;

use App\Services\NotificationEscalationService;
use Illuminate\Console\Command;

class ProcessNotificationEscalations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-escalations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending notification escalations and send alerts';

    protected NotificationEscalationService $escalationService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationEscalationService $escalationService)
    {
        parent::__construct();
        $this->escalationService = $escalationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing notification escalations...');

        try {
            $processed = $this->escalationService->processEscalations();

            if ($processed > 0) {
                $this->info("✅ Successfully processed {$processed} escalation(s).");
            } else {
                $this->info('ℹ️ No escalations to process.');
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('❌ Failed to process escalations: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}
