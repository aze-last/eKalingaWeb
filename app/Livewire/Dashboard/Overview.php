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
    public function render(PerformanceCacheService $cacheService)
    {
        // 1. Cached Budget & Demographic KPI Aggregates
        $metrics = $cacheService->getDashboardKpiMetrics();

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

        return view('livewire.dashboard.overview', array_merge($metrics, [
            'activePrograms' => $activePrograms,
            'recentActivities' => $recentActivities,
            'recentClaims' => $recentClaims,
        ]));
    }
}
