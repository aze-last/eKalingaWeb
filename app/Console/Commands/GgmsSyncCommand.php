<?php

namespace App\Console\Commands;

use App\Services\GgmsBudgetSyncService;
use App\Services\GgmsProjectSyncService;
use App\Services\GgmsTransactionService;
use Illuminate\Console\Command;

class GgmsSyncCommand extends Command
{
    protected $signature = 'ggms:sync {--office=OFF-2026-0006 : Office code to sync} {--budget-only : Only sync budget and project envelopes}';

    protected $description = 'Synchronize GGMS budget allocations, project envelopes, and flush offline transactions';

    public function handle(
        GgmsTransactionService $txService,
        GgmsBudgetSyncService $budgetSync,
        GgmsProjectSyncService $projectSync
    ): int {
        $office = (string) $this->option('office');

        $this->info("1. Synchronizing GGMS Office Budget Snapshot for {$office}...");
        $snapshot = $budgetSync->syncOfficeBudget($office);
        $this->line('   Allocated: ₱'.number_format((float) $snapshot->allocated_amount, 2).' | Balance: ₱'.number_format((float) $snapshot->remaining_balance, 2)." (Source: {$snapshot->source_table})");

        $this->info("2. Synchronizing GGMS Project Envelopes for {$office}...");
        $projCount = $projectSync->syncProjects($office);
        $this->line("   Mirrored {$projCount} sub-project envelope(s) into local cache.");

        if (! $this->option('budget-only')) {
            $this->info('3. Flushing offline pending transactions to central GGMS hub...');
            $flushed = $txService->flushPendingTransactions();
            $this->line("   Flushed and synchronized {$flushed} transaction(s).");
        }

        $this->info('GGMS Synchronization completed successfully.');

        return Command::SUCCESS;
    }
}
