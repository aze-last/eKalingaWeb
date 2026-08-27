<?php

namespace App\Services;

use App\Enums\FundingType;
use App\Models\AyudaProgram;
use App\Models\FundingSource;
use App\Models\GgmsProjectCache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GgmsProjectSyncService
{
    /**
     * Per-project sync:
     * 1. Separately mirrors GGMS's `project_details` sub-allocations.
     * 2. Upserts into local `ggms_project_caches` keyed by `project_details_id`.
     * 3. Soft-archives any project missing in upstream GGMS.
     * 4. Updates linked FundingSources and executes live budget-cap following on linked AyudaPrograms:
     *    budget_cap = max(cached.total_budget, already_disbursed).
     */
    public function syncProjects(string $officeCode = 'OFF-2026-0006'): int
    {
        $syncedCount = 0;
        $activeGgmsProjectIds = [];

        try {
            $remoteProjects = DB::connection('ggms')->table('project_details')
                ->where('office_code', $officeCode)
                ->whereNotNull('project_details_id')
                ->get();

            foreach ($remoteProjects as $p) {
                $projCode = $p->project_details_id ?: ('OPP-2026-'.str_pad($p->id, 4, '0', STR_PAD_LEFT));
                $activeGgmsProjectIds[] = $projCode;

                $totalBudget = (float) ($p->total_budget ?? 0);
                $title = $p->project ?? 'GGMS Grant Project';
                $description = $p->description ?? '';
                $voucherCode = $p->voucher_code ?? null;
                $status = strtolower($p->status ?? 'active');

                // Upsert local cache
                $cache = GgmsProjectCache::updateOrCreate(
                    ['project_details_id' => $projCode],
                    [
                        'title' => $title,
                        'description' => $description,
                        'office' => $officeCode,
                        'fiscal_year' => 2026,
                        'total_allocation' => $totalBudget,
                        'allocated_budget' => $totalBudget,
                        'available_balance' => $totalBudget,
                        'voucher_code' => $voucherCode,
                        'status' => $status,
                        'last_synced_at' => now(),
                    ]
                );

                // Upsert corresponding FundingSource envelope
                $fundingSource = FundingSource::updateOrCreate(
                    ['project_details_id' => $projCode],
                    [
                        'funding_type' => FundingType::Government,
                        'title' => "[GGMS] {$title}",
                        'source_code' => $projCode,
                        'office' => $officeCode,
                        'fiscal_year' => 2026,
                        'allocated_amount' => $totalBudget,
                        'remaining_balance' => $totalBudget,
                        'status' => $totalBudget > 0 ? 'Active' : 'Depleted',
                        'description' => "Earmarked GGMS Grant Envelope (Voucher: {$voucherCode})",
                    ]
                );

                // Live budget-cap following for any linked local programs
                $linkedPrograms = AyudaProgram::where('source_project_details_id', $projCode)
                    ->orWhere('funding_source_id', $fundingSource->id)
                    ->get();

                foreach ($linkedPrograms as $prog) {
                    $alreadyDisbursed = (float) $prog->total_disbursed_amount;
                    $newCap = max($totalBudget, $alreadyDisbursed);

                    if ($prog->budget_cap != $newCap) {
                        $prog->update([
                            'budget_cap' => $newCap,
                            'title' => $prog->title ?: $title,
                        ]);
                    }
                }

                $syncedCount++;
            }

            // Soft-archive any local cached projects that no longer exist on the GGMS side
            if (! empty($activeGgmsProjectIds)) {
                GgmsProjectCache::whereNotIn('project_details_id', $activeGgmsProjectIds)
                    ->update(['status' => 'archived']);
            }
        } catch (\Throwable $e) {
            Log::error("GGMS Per-Project Sync Error: {$e->getMessage()}");
        }

        return $syncedCount;
    }
}
