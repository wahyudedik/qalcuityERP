<?php

namespace App\Console\Commands;

use App\Models\AiModelSwitchLog;
use App\Models\SystemSetting;
use Illuminate\Console\Command;

class PruneModelSwitchLogs extends Command
{
    protected $signature = 'ai:prune-switch-logs';

    protected $description = 'Delete AI model switch log records older than the configured retention period';

    public function handle(): void
    {
        $retentionDays = (int) SystemSetting::get('gemini_log_retention_days', 30);

        $cutoff = now()->subDays($retentionDays);

        $deleted = AiModelSwitchLog::where('switched_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} AI model switch log record(s) older than {$retentionDays} days.");
    }
}
