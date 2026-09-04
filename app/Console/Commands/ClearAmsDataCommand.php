<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Signature('ams:clear')]
#[Description('Safely clear out AMS operational data while strictly preserving users, settings, GGMS, and CRS databases.')]
class ClearAmsDataCommand extends Command
{
    /**
     * Tables that must NEVER be wiped or deleted under any circumstances.
     */
    protected const PROTECTED_TABLES = [
        'users',
        'settings',
        'migrations',
        'sessions',
        'beneficiaries',
        'val_beneficiaries',
        'barangays',
        'ggms_consolidated_transactions',
        'ggms_pending_transactions',
        'ggms_project_caches',
        'government_budget_snapshots',
    ];

    /**
     * AMS tables targeted for clearing.
     */
    protected const AMS_TABLES = [
        'ayuda_project_claims',
        'distribution_enrollments',
        'budget_ledger_entries',
        'ayuda_programs',
        'goods_donations',
        'donations',
        'activity_logs',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting AMS database wipeout (preserving users, settings, GGMS, and CRS)...');

        DB::statement('PRAGMA foreign_keys = OFF;');

        try {
            foreach (self::AMS_TABLES as $table) {
                if (in_array($table, self::PROTECTED_TABLES, true)) {
                    $this->warn("Skipping protected table: {$table}");

                    continue;
                }

                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->truncate();
                    $this->line(" <info>✓</info> Cleared <comment>{$table}</comment> ({$count} records removed)");
                }
            }

            // Delete private funding sources and reset government funding balances
            if (Schema::hasTable('funding_sources')) {
                DB::table('funding_sources')->where('funding_type', 'Private')->delete();
                DB::table('funding_sources')->where('funding_type', 'Government')->update([
                    'spent_amount' => 0.00,
                    'remaining_balance' => DB::raw('allocated_amount'),
                ]);
                $this->line(' <info>✓</info> Reset government funding sources remaining balance and cleared private donation pools');
            }

            // Clear cache
            Cache::flush();
            $this->line(' <info>✓</info> Application cache flushed');

            $this->newLine();
            $this->info('Protected tables verified intact:');
            foreach (self::PROTECTED_TABLES as $protected) {
                if (Schema::hasTable($protected)) {
                    $count = DB::table($protected)->count();
                    $this->line(" • <comment>{$protected}</comment>: {$count} records preserved");
                }
            }

            $this->newLine();
            $this->info('AMS operational database successfully cleared!');

            return Command::SUCCESS;
        } finally {
            DB::statement('PRAGMA foreign_keys = ON;');
        }
    }
}
