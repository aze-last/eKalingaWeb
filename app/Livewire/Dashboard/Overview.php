<?php

namespace App\Livewire\Dashboard;

use App\Enums\DistributionStatus;
use App\Enums\FundingType;
use App\Enums\ProgramStatus;
use App\Models\ActivityLog;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard - eKalinga+')]
class Overview extends Component
{
    public function render()
    {
        // 1. Budget Financial Aggregates
        $govAllocated = FundingSource::where('funding_type', FundingType::Government)->sum('allocated_amount');
        $govSpent = FundingSource::where('funding_type', FundingType::Government)->sum('spent_amount');
        $govBalance = FundingSource::where('funding_type', FundingType::Government)->sum('remaining_balance');

        $privateAllocated = FundingSource::where('funding_type', FundingType::Private)->sum('allocated_amount');
        $privateSpent = FundingSource::where('funding_type', FundingType::Private)->sum('spent_amount');
        $privateBalance = FundingSource::where('funding_type', FundingType::Private)->sum('remaining_balance');

        $totalDisbursed = AyudaProjectClaim::sum('unit_amount');
        $totalClaims = AyudaProjectClaim::count();

        // 2. Active Programs Snapshot
        $activePrograms = AyudaProgram::with('fundingSource')
            ->where('status', ProgramStatus::Active)
            ->withCount([
                'enrollments as pending_count' => fn ($q) => $q->where('status', DistributionStatus::PENDING),
                'enrollments as released_count' => fn ($q) => $q->where('status', DistributionStatus::RELEASED),
                'enrollments as unreleased_count' => fn ($q) => $q->where('status', DistributionStatus::UNRELEASED),
            ])
            ->latest()
            ->take(5)
            ->get();

        // 3. Recent Activity Stream
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(6)
            ->get();

        // 4. Latest Distribution Claims
        $recentClaims = AyudaProjectClaim::with(['beneficiary', 'ayudaProgram', 'releasingOfficer'])
            ->latest('claimed_at')
            ->take(6)
            ->get();

        // 5. GGMS Sync Status
        $ggmsCount = GgmsConsolidatedTransaction::count();

        try {
            $totalBeneficiaries = Beneficiary::count();
            $barangayCount = DB::connection('crs')->table('barangays')->count();
        } catch (\Throwable) {
            $totalBeneficiaries = GgmsConsolidatedTransaction::distinct('civil_registry_id')->count('civil_registry_id');
            $barangayCount = GgmsConsolidatedTransaction::distinct('barangay')->whereNotNull('barangay')->count('barangay');
        }

        if (! $barangayCount) {
            $barangayCount = 25;
        }

        return view('livewire.dashboard.overview', [
            'govAllocated' => $govAllocated,
            'govSpent' => $govSpent,
            'govBalance' => $govBalance,
            'privateAllocated' => $privateAllocated,
            'privateSpent' => $privateSpent,
            'privateBalance' => $privateBalance,
            'totalDisbursed' => $totalDisbursed,
            'totalBeneficiaries' => $totalBeneficiaries,
            'totalClaims' => $totalClaims,
            'activePrograms' => $activePrograms,
            'recentActivities' => $recentActivities,
            'recentClaims' => $recentClaims,
            'ggmsCount' => $ggmsCount,
            'barangayCount' => $barangayCount,
        ]);
    }
}
