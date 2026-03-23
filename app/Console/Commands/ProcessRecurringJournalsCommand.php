<?php

namespace App\Console\Commands;

use App\Jobs\ProcessRecurringJournals;
use Illuminate\Console\Command;

class ProcessRecurringJournalsCommand extends Command
{
    protected $signature   = 'journals:process-recurring';
    protected $description = 'Proses dan generate jurnal berulang yang jatuh tempo hari ini';

    public function handle(): int
    {
        $this->info('Memproses jurnal berulang...');

        ProcessRecurringJournals::dispatchSync();

        $this->info('Selesai.');
        return self::SUCCESS;
    }
}
