<?php

namespace App\Services;

use App\Enums\FundingType;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use App\Models\GovernmentBudgetSnapshot;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GgmsBudgetSyncService
{
    /**
     * Office-level allocation sync:
     * 1. Pulls office's total allocated amount from GGMS by office_code.
     * 2. Checks `officeallocations` (primary in Sulop GGMS) and falls back/merges `budget_allocations`.
     * 3. Computes spent amount live from local ledger/transactions.
     * 4. Appends a GovernmentBudgetSnapshot row for audit history.
     * 5. Syncs/updates the general Government Funding Source pool.
     */
    public function syncOfficeBudget(string $officeCode = 'OFF-2026-0006'): GovernmentBudgetSnapshot
    {
        $allocatedAmount = 0.00;
        $sourceTable = 'officeallocations';
        $rawRecord = null;

        try {
            // Check officeallocations (standard legacy table with office_code column)
            $legacyAlloc = DB::connection('ggms')->table('officeallocations')
                ->where('office_code', $officeCode)
                ->first();

            if ($legacyAlloc && isset($legacyAlloc->AllocatedAmount)) {
                $allocatedAmount = (float) $legacyAlloc->AllocatedAmount;
                $sourceTable = 'officeallocations';
                $rawRecord = (array) $legacyAlloc;
            } else {
                // Fallback: Check new-style budget_allocations by office_id or office_code
                $numOfficeId = (int) str_replace(['OFF-2026-', 'OFF-'], '', $officeCode);
                $newAlloc = DB::connection('ggms')->table('budget_allocations')
                    ->where(function ($q) use ($numOfficeId) {
                        $q->where('office_id', $numOfficeId);
                    })
                    ->first();

                if ($newAlloc) {
                    $allocatedAmount = (float) ($newAlloc->amount ?? $newAlloc->allocated_amount ?? 0);
                    $sourceTable = 'budget_allocations';
                    $rawRecord = (array) $newAlloc;
                }
            }
        } catch (\Throwable $e) {
            Log::warning("GGMS Office Budget Query Notice: {$e->getMessage()}");
        }

        // Compute spent amount live from local transactions
        $computedSpent = (float) GgmsConsolidatedTransaction::where('sync_status', 'Synced')->sum('amount');

        // Also incorporate local claimed releases
        $localClaims = (float) DB::table('ayuda_project_claims')->sum('unit_amount');
        $computedSpent = max($computedSpent, $localClaims);

        $remainingBalance = max(0.00, $allocatedAmount - $computedSpent);

        // Record append-only snapshot audit trail
        $snapshot = GovernmentBudgetSnapshot::create([
            'office_code' => $officeCode,
            'fiscal_year' => 2026,
            'allocated_amount' => $allocatedAmount,
            'computed_spent_amount' => $computedSpent,
            'remaining_balance' => $remainingBalance,
            'source_table' => $sourceTable,
            'raw_payload' => $rawRecord,
            'synced_at' => now(),
        ]);

        // Maintain primary General Fund pool in funding_sources
        FundingSource::updateOrCreate(
            ['source_code' => "GGMS-{$officeCode}"],
            [
                'funding_type' => FundingType::Government,
                'title' => 'GGMS Municipal Government General Fund',
                'office' => 'Municipal Office',
                'fiscal_year' => 2026,
                'allocated_amount' => $allocatedAmount,
                'spent_amount' => $computedSpent,
                'remaining_balance' => $remainingBalance,
                'status' => $remainingBalance > 0 ? 'Active' : 'Depleted',
                'description' => "Synchronized from GGMS master allocation ({$sourceTable}) for {$officeCode}",
            ]
        );

        return $snapshot;
    }
}
