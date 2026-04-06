<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\WorkflowEngine;

class ProcessScheduledWorkflows extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workflows:process-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled workflows';

    protected $workflowEngine;

    public function __construct(WorkflowEngine $workflowEngine)
    {
        parent::__construct();
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled workflows...');

        try {
            $this->workflowEngine->executeScheduled();

            $this->info('Scheduled workflows processed successfully!');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing scheduled workflows: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
