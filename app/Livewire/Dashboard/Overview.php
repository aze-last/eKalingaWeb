<?php

namespace App\Livewire\Dashboard;

use App\Enums\DistributionStatus;
use App\Enums\ProgramStatus;
use App\Models\ActivityLog;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Services\PerformanceCacheService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Dashboard - eKalinga+')]
class Overview extends Component
{
    public string $timeframe = 'all';

    public string $activeTab = 'radar';

    public function setTimeframe(string $tf): void
    {
        $this->timeframe = $tf;
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function render(PerformanceCacheService $cacheService)
    {
        // 1. Cached Budget & Demographic KPI Aggregates
        $metrics = $cacheService->getDashboardKpiMetrics();

        // 2. Barangay Ayuda Leaderboard
        $barangayLeaderboard = $cacheService->getBarangayLeaderboard();

        // 3. Disbursement Trends
        $disbursementTrends = $cacheService->getDisbursementTrends();

        // 4. Demographics Summary
        $demographics = $cacheService->getDemographicsSummary();

        // 5. Active Programs Snapshot
        $activePrograms = AyudaProgram::with('fundingSource')
            ->where('status', ProgramStatus::Active)
            ->withCount([
                'enrollments as pending_count' => fn ($q) => $q->where('status', DistributionStatus::PENDING),
                'enrollments as released_count' => fn ($q) => $q->where('status', DistributionStatus::RELEASED),
                'enrollments as unreleased_count' => fn ($q) => $q->where('status', DistributionStatus::UNRELEASED),
            ])
            ->latest()
            ->take(6)
            ->get();

        // 6. Recent Activity Stream
        $recentActivities = ActivityLog::with('user')
            ->latest()
            ->take(8)
            ->get();

        // 7. Latest Distribution Claims
        $recentClaims = AyudaProjectClaim::with(['beneficiary', 'ayudaProgram', 'releasingOfficer'])
            ->latest('claimed_at')
            ->take(8)
            ->get();

        return view('livewire.dashboard.overview', array_merge($metrics, [
            'barangayLeaderboard' => $barangayLeaderboard,
            'disbursementTrends' => $disbursementTrends,
            'demographics' => $demographics,
            'activePrograms' => $activePrograms,
            'recentActivities' => $recentActivities,
            'recentClaims' => $recentClaims,
        ]));
    }
}
