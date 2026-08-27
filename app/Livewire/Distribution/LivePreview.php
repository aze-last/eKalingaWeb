<?php

namespace App\Livewire\Distribution;

use App\Enums\DistributionStatus;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\DistributionEnrollment;
use App\Models\Setting;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.guest')]
#[Title('Live Queue Display - eKalinga+')]
class LivePreview extends Component
{
    public int $projectId;

    public function mount(int $project): void
    {
        $this->projectId = $project;
    }

    public function render()
    {
        $program = AyudaProgram::findOrFail($this->projectId);

        // Recent Claims (Latest 4 announced)
        $recentClaims = AyudaProjectClaim::with('beneficiary')
            ->where('ayuda_program_id', $this->projectId)
            ->latest('claimed_at')
            ->take(4)
            ->get();

        $latestClaim = $recentClaims->first();

        // Upcoming Queued Beneficiaries
        $upcomingQueue = DistributionEnrollment::with('beneficiary')
            ->where('ayuda_program_id', $this->projectId)
            ->where('status', DistributionStatus::PENDING)
            ->oldest('enrolled_at')
            ->take(6)
            ->get();

        $totalReleased = DistributionEnrollment::where('ayuda_program_id', $this->projectId)
            ->where('status', DistributionStatus::RELEASED)
            ->count();

        $totalQueued = DistributionEnrollment::where('ayuda_program_id', $this->projectId)
            ->where('status', DistributionStatus::PENDING)
            ->count();

        $municipalSeal = Setting::get('municipal_seal_url', '/images/Site_logo.png');
        $municipalityName = Setting::get('municipality_name', 'Municipality of Sulop');

        return view('livewire.distribution.live-preview', [
            'program' => $program,
            'recentClaims' => $recentClaims,
            'latestClaim' => $latestClaim,
            'upcomingQueue' => $upcomingQueue,
            'totalReleased' => $totalReleased,
            'totalQueued' => $totalQueued,
            'municipalSeal' => $municipalSeal,
            'municipalityName' => $municipalityName,
        ]);
    }
}
