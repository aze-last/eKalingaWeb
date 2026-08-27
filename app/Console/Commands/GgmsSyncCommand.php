<?php

namespace App\Console\Commands;

use App\Services\GgmsTransactionService;
use Illuminate\Console\Command;

class GgmsSyncCommand extends Command
{
    protected $signature = 'ggms:sync';

    protected $description = 'Flush and synchronize offline pending transactions to central GGMS hub';

    public function handle(GgmsTransactionService $service): int
    {
        $this->info('Starting GGMS offline queue flush...');
        $flushed = $service->flushPendingTransactions();
        $this->info("Flushed and synchronized {$flushed} transactions successfully.");

        return Command::SUCCESS;
    }
}
