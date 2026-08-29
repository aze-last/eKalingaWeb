<?php

namespace App\Services;

use App\Enums\FundingType;
use App\Enums\ProgramStatus;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PerformanceCacheService
{
    public const KEY_BARANGAYS = 'app_barangays_list';

    public const KEY_BARANGAY_COUNT = 'app_barangay_count';

    public const KEY_DASHBOARD_KPI = 'app_dashboard_kpi_metrics';

    public const KEY_FUNDING_OVERVIEW = 'app_budget_funding_overview';

    public const KEY_ACTIVE_PROGRAMS = 'app_active_ayuda_programs';

    /**
     * Get cached list of Sulop barangays.
     *
     * @return array<int, string>
     */
    public function getBarangays(): array
    {
        return Cache::remember(self::KEY_BARANGAYS, 86400, function (): array {
            try {
                $list = DB::connection('crs')
                    ->table('barangays')
                    ->orderBy('name')
                    ->pluck('name')
                    ->toArray();

                if (! empty($list)) {
                    return $list;
                }
            } catch (\Throwable) {
            }

            try {
                $list = GgmsConsolidatedTransaction::distinct()
                    ->whereNotNull('barangay')
                    ->where('barangay', '!=', '')
                    ->orderBy('barangay')
                    ->pluck('barangay')
                    ->toArray();

                if (! empty($list)) {
                    return $list;
                }
            } catch (\Throwable) {
            }

            return [
                'Balasinon', 'Buguis', 'Carre', 'Clib', 'Harada Yano', 'Ibo', 'Inayagan',
                'Kiblagon', 'Labon', 'Lapediche', 'Luparan', 'Mckinley', 'New Cebu',
                'Osmeña', 'Palili', 'Parame', 'Poblacion', 'Roxas', 'Solongvale',
                'Tagolilong', 'Tala-o', 'Talas', 'Tanwalang', 'Waterfall',
            ];
        });
    }

    /**
     * Get cached total barangay count.
     */
    public function getBarangayCount(): int
    {
        return Cache::remember(self::KEY_BARANGAY_COUNT, 86400, function (): int {
            try {
                $count = DB::connection('crs')->table('barangays')->count();
                if ($count > 0) {
                    return $count;
                }
            } catch (\Throwable) {
            }

            return count($this->getBarangays());
        });
    }

    /**
     * Get cached Dashboard KPI aggregate statistics (TTL 60s).
     *
     * @return array<string, mixed>
     */
    public function getDashboardKpiMetrics(): array
    {
        return Cache::remember(self::KEY_DASHBOARD_KPI, 60, function (): array {
            $govAllocated = (float) FundingSource::where('funding_type', FundingType::Government)->sum('allocated_amount');
            $govSpent = (float) FundingSource::where('funding_type', FundingType::Government)->sum('spent_amount');
            $govBalance = (float) FundingSource::where('funding_type', FundingType::Government)->sum('remaining_balance');

            $privateAllocated = (float) FundingSource::where('funding_type', FundingType::Private)->sum('allocated_amount');
            $privateSpent = (float) FundingSource::where('funding_type', FundingType::Private)->sum('spent_amount');
            $privateBalance = (float) FundingSource::where('funding_type', FundingType::Private)->sum('remaining_balance');

            $totalDisbursed = (float) AyudaProjectClaim::sum('unit_amount');
            $totalClaims = AyudaProjectClaim::count();
            $ggmsCount = GgmsConsolidatedTransaction::count();

            try {
                $totalBeneficiaries = Beneficiary::count();
            } catch (\Throwable) {
                $totalBeneficiaries = GgmsConsolidatedTransaction::distinct('civil_registry_id')->count('civil_registry_id');
            }

            return [
                'govAllocated' => $govAllocated,
                'govSpent' => $govSpent,
                'govBalance' => $govBalance,
                'privateAllocated' => $privateAllocated,
                'privateSpent' => $privateSpent,
                'privateBalance' => $privateBalance,
                'totalDisbursed' => $totalDisbursed,
                'totalBeneficiaries' => $totalBeneficiaries,
                'totalClaims' => $totalClaims,
                'ggmsCount' => $ggmsCount,
                'barangayCount' => $this->getBarangayCount(),
            ];
        });
    }

    /**
     * Get cached Funding & Budget overview aggregate figures (TTL 120s).
     *
     * @return array<string, mixed>
     */
    public function getFundingOverview(): array
    {
        return Cache::remember(self::KEY_FUNDING_OVERVIEW, 120, function (): array {
            $sources = FundingSource::withCount('ayudaPrograms')->latest()->get();
            $govSources = $sources->where('funding_type', FundingType::Government);
            $privateSources = $sources->where('funding_type', FundingType::Private);

            $govAllocated = (float) $govSources->sum('allocated_amount');
            $govSpent = (float) $govSources->sum('spent_amount');
            $govBalance = (float) $govSources->sum('remaining_balance');

            $privateAllocated = (float) $privateSources->sum('allocated_amount');
            $privateSpent = (float) $privateSources->sum('spent_amount');
            $privateBalance = (float) $privateSources->sum('remaining_balance');

            return [
                'sources' => $sources,
                'govSources' => $govSources,
                'privateSources' => $privateSources,
                'govAllocated' => $govAllocated,
                'govSpent' => $govSpent,
                'govBalance' => $govBalance,
                'privateAllocated' => $privateAllocated,
                'privateSpent' => $privateSpent,
                'privateBalance' => $privateBalance,
                'totalAllocated' => $govAllocated + $privateAllocated,
                'totalDisbursed' => $govSpent + $privateSpent,
                'totalAvailable' => $govBalance + $privateBalance,
            ];
        });
    }

    /**
     * Get cached active Ayuda programs for quick pickers.
     *
     * @return Collection<int, AyudaProgram>
     */
    public function getActivePrograms(): Collection
    {
        return Cache::remember(self::KEY_ACTIVE_PROGRAMS, 60, function () {
            return AyudaProgram::where('status', ProgramStatus::Active)
                ->orderBy('title')
                ->get();
        });
    }

    /**
     * Clear budget and funding caches on mutate events.
     */
    public function clearFundingCache(): void
    {
        Cache::forget(self::KEY_FUNDING_OVERVIEW);
        Cache::forget(self::KEY_DASHBOARD_KPI);
        Cache::forget(self::KEY_ACTIVE_PROGRAMS);
    }

    /**
     * Clear dashboard specific cache.
     */
    public function clearDashboardCache(): void
    {
        Cache::forget(self::KEY_DASHBOARD_KPI);
    }

    /**
     * Clear all performance cache keys.
     */
    public function clearAllPerformanceCache(): void
    {
        Cache::forget(self::KEY_BARANGAYS);
        Cache::forget(self::KEY_BARANGAY_COUNT);
        Cache::forget(self::KEY_DASHBOARD_KPI);
        Cache::forget(self::KEY_FUNDING_OVERVIEW);
        Cache::forget(self::KEY_ACTIVE_PROGRAMS);
    }
}
