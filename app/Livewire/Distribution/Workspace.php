<?php

namespace App\Livewire\Distribution;

use App\Enums\DistributionStatus;
use App\Enums\ProgramStatus;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\DistributionEnrollment;
use App\Services\DigitalIdVerificationService;
use App\Services\DistributionReleaseService;
use App\Services\HouseholdVerificationService;
use App\Services\PerformanceCacheService;
use Exception;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Project Distribution - eKalinga+')]
class Workspace extends Component
{
    use WithPagination;

    // Selected Project
    public ?int $selectedProjectId = null;

    // Search filters per bucket
    public string $pendingSearch = '';

    public string $releasedSearch = '';

    public string $unreleasedSearch = '';

    // Scanner State
    public string $scannerInput = '';

    public bool $isScannerLocked = false;

    // Success Overlay State
    public bool $showSuccessOverlay = false;

    public array $lastReleasedClaim = [];

    // Duplicate Household Warning Modal State
    public bool $showDuplicateWarningModal = false;

    public array $duplicateWarningData = [];

    public ?int $pendingBeneficiaryIdForRelease = null;

    // Manual Beneficiary Picker Overlay
    public bool $showBeneficiaryPickerModal = false;

    public string $pickerSearch = '';

    public string $pickerBarangay = '';

    // Project Picker Modal State
    public bool $showProjectPickerModal = false;

    public string $projectPickerSearch = '';

    public ?int $projectPickerHighlightedId = null;

    public function mount(): void
    {
        // Show the project picker on first load rather than silently auto-selecting
        // the first active program (requirement: project-first workflow).
        if ($this->selectedProjectId === null) {
            $this->showProjectPickerModal = true;
        }
    }

    public function selectProject(int $id): void
    {
        $this->selectedProjectId = $id;
        $this->showProjectPickerModal = false;
        $this->projectPickerHighlightedId = null;
        $this->projectPickerSearch = '';
        $this->resetPage('pendingPage');
        $this->resetPage('releasedPage');
        $this->resetPage('unreleasedPage');
    }

    /**
     * Handle Barcode / QR Scanner Input submission.
     */
    public function handleScan(
        string $scannedPayload,
        DigitalIdVerificationService $verificationService,
        DistributionReleaseService $releaseService,
        HouseholdVerificationService $householdService
    ): void {
        if ($this->isScannerLocked || ! $this->selectedProjectId) {
            return;
        }

        $cleanPayload = $verificationService->normalizePayload($scannedPayload);
        if (empty($cleanPayload)) {
            return;
        }

        $program = AyudaProgram::find($this->selectedProjectId);
        if (! $program) {
            return;
        }

        // Run Authoritative Verification through DigitalIdVerificationService
        $verification = $verificationService->verify($cleanPayload, $program->id);

        if (! $verification['success']) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: $verification['message']);

            return;
        }

        $beneficiary = $verification['beneficiary'];
        if (! $beneficiary) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: 'No beneficiary profile linked to this verified card.');

            return;
        }

        // Check if already claimed in this program
        $alreadyClaimed = AyudaProjectClaim::where('ayuda_program_id', $program->id)
            ->where('beneficiary_id', $beneficiary->id)
            ->exists();

        if ($alreadyClaimed) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: "ALREADY CLAIMED: {$beneficiary->full_name} has already received this assistance!");

            return;
        }

        // Check household duplicate protection
        $hhCheck = $householdService->checkHouseholdStatus($beneficiary, $program);
        if ($hhCheck['has_warning']) {
            $this->duplicateWarningData = $hhCheck;
            $this->pendingBeneficiaryIdForRelease = $beneficiary->id;
            $this->showDuplicateWarningModal = true;
            $this->isScannerLocked = true;
            $this->dispatch('play-audio-error');

            return;
        }

        // Execute immediate atomic release
        $verificationMethod = ($verification['status'] === 'OFFLINE_CACHED') ? 'OFFLINE_CACHE_SCAN' : 'QR_SCAN';
        $this->executeRelease($program, $beneficiary, $verificationMethod, $cleanPayload, $releaseService);
    }

    /**
     * Direct Release Action (via button click or scanner confirmation).
     */
    public function releaseBeneficiary(int $beneficiaryId, DistributionReleaseService $releaseService, HouseholdVerificationService $householdService): void
    {
        $program = AyudaProgram::findOrFail($this->selectedProjectId);
        $beneficiary = Beneficiary::findOrFail($beneficiaryId);

        // Check household duplicate protection
        $hhCheck = $householdService->checkHouseholdStatus($beneficiary, $program);
        if ($hhCheck['has_warning']) {
            $this->duplicateWarningData = $hhCheck;
            $this->pendingBeneficiaryIdForRelease = $beneficiary->id;
            $this->showDuplicateWarningModal = true;
            $this->isScannerLocked = true;
            $this->dispatch('play-audio-error');

            return;
        }

        $this->executeRelease($program, $beneficiary, 'MANUAL_CLICK', null, $releaseService);
    }

    public function confirmOverrideRelease(DistributionReleaseService $releaseService): void
    {
        if (! $this->pendingBeneficiaryIdForRelease || ! $this->selectedProjectId) {
            return;
        }

        $program = AyudaProgram::findOrFail($this->selectedProjectId);
        $beneficiary = Beneficiary::findOrFail($this->pendingBeneficiaryIdForRelease);

        $this->showDuplicateWarningModal = false;
        $this->isScannerLocked = false;
        $this->pendingBeneficiaryIdForRelease = null;

        $this->executeRelease($program, $beneficiary, 'OVERRIDE_APPROVED', 'HOUSEHOLD_WARNING_OVERRIDDEN', $releaseService);
    }

    public function cancelOverrideRelease(): void
    {
        $this->showDuplicateWarningModal = false;
        $this->isScannerLocked = false;
        $this->pendingBeneficiaryIdForRelease = null;
    }

    protected function executeRelease(AyudaProgram $program, Beneficiary $beneficiary, string $method, ?string $payload, DistributionReleaseService $releaseService): void
    {
        try {
            $result = $releaseService->processRelease(
                program: $program,
                beneficiary: $beneficiary,
                verificationMethod: $method,
                qrPayload: $payload
            );

            $this->lastReleasedClaim = [
                'claim_code' => $result['claim']->claim_code,
                'beneficiary_name' => $beneficiary->full_name,
                'civil_registry_id' => $beneficiary->civil_registry_id,
                'household_no' => $beneficiary->household_no,
                'barangay' => $beneficiary->barangay,
                'amount' => $result['claim']->unit_amount,
                'item_details' => $result['claim']->item_details,
                'timestamp' => now()->format('h:i:s A'),
            ];

            app(PerformanceCacheService::class)->clearFundingCache();

            $this->showSuccessOverlay = false;
            $this->isScannerLocked = false;
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: 'Disbursed ₱'.number_format($result['claim']->unit_amount, 2)." to {$beneficiary->full_name} ({$result['claim']->claim_code})");
        } catch (Exception $e) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function dismissSuccessOverlay(): void
    {
        $this->showSuccessOverlay = false;
        $this->isScannerLocked = false;
    }

    public function moveToUnreleased(int $enrollmentId, DistributionReleaseService $releaseService): void
    {
        $enrollment = DistributionEnrollment::findOrFail($enrollmentId);
        $releaseService->updateStatus($enrollment, DistributionStatus::UNRELEASED, 'Excluded / Disqualified by releasing officer');
        $this->dispatch('toast', type: 'warning', message: 'Beneficiary moved to Unreleased / Excluded bucket.');
    }

    public function moveToPending(int $enrollmentId, DistributionReleaseService $releaseService): void
    {
        $enrollment = DistributionEnrollment::findOrFail($enrollmentId);
        $releaseService->updateStatus($enrollment, DistributionStatus::PENDING, null);
        $this->dispatch('toast', type: 'info', message: 'Beneficiary moved back to Pending queue.');
    }

    public function openBeneficiaryPicker(): void
    {
        $this->pickerSearch = '';
        $this->pickerBarangay = '';
        $this->showBeneficiaryPickerModal = true;
        $this->isScannerLocked = true;
    }

    public function closeBeneficiaryPicker(): void
    {
        $this->showBeneficiaryPickerModal = false;
        $this->isScannerLocked = false;
    }

    // ------------------------------------------
    // PROJECT PICKER MODAL
    // ------------------------------------------

    /**
     * Open the project picker, pre-highlighting the currently selected project.
     * Resets the search field so the full list is shown (requirement 4).
     * Idempotent: calling while already open simply resets search (requirement 9).
     */
    public function openProjectPicker(): void
    {
        $this->projectPickerSearch = '';
        $this->projectPickerHighlightedId = $this->selectedProjectId;
        $this->showProjectPickerModal = true;
    }

    /**
     * Cancel the picker without touching the currently selected project (requirement 8).
     */
    public function closeProjectPicker(): void
    {
        $this->showProjectPickerModal = false;
        $this->projectPickerHighlightedId = null;
    }

    /**
     * Highlight (single-click select) a row in the picker without confirming it.
     */
    public function highlightProject(int $id): void
    {
        $this->projectPickerHighlightedId = $id;
    }

    /**
     * Confirm the highlighted project and close the picker.
     * Delegates to selectProject() so pagination resets are preserved.
     * No-op when nothing is highlighted (requirement 6).
     */
    public function confirmProjectSelection(): void
    {
        if ($this->projectPickerHighlightedId === null) {
            return;
        }

        $this->selectProject($this->projectPickerHighlightedId);
        $this->showProjectPickerModal = false;
        $this->projectPickerHighlightedId = null;
        $this->projectPickerSearch = '';
    }

    public function enrollBeneficiary(int $beneficiaryId): void
    {
        $this->addBeneficiaryToProject($beneficiaryId);
    }

    public function addBeneficiaryToProject(int $beneficiaryId): void
    {
        if (! $this->selectedProjectId) {
            return;
        }

        $program = AyudaProgram::find($this->selectedProjectId);
        $beneficiary = Beneficiary::find($beneficiaryId);

        if (! $program || ! $beneficiary) {
            return;
        }

        // Check if already enrolled
        $exists = DistributionEnrollment::where('ayuda_program_id', $program->id)
            ->where('beneficiary_id', $beneficiary->id)
            ->exists();

        if ($exists) {
            $this->dispatch('toast', type: 'error', message: "Beneficiary {$beneficiary->full_name} is already enrolled in this project.");

            return;
        }

        DistributionEnrollment::create([
            'ayuda_program_id' => $program->id,
            'beneficiary_id' => $beneficiary->id,
            'civil_registry_id' => $beneficiary->civil_registry_id ?: $beneficiary->civilregistry_id ?: $beneficiary->beneficiary_id,
            'household_no' => $beneficiary->household_no,
            'status' => DistributionStatus::PENDING,
            'enrolled_at' => now(),
        ]);

        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'success', message: "Added {$beneficiary->full_name} to {$program->program_code} distribution queue.");
        $this->closeBeneficiaryPicker();
    }

    public function autoEnrollBeneficiariesForProject(): void
    {
        if (! $this->selectedProjectId) {
            return;
        }

        $program = AyudaProgram::find($this->selectedProjectId);
        if (! $program) {
            return;
        }

        $limit = max(1, (int) ($program->target_beneficiaries ?: 50));
        $query = Beneficiary::query();
        if ($program->target_barangay) {
            $query->where('address', 'like', "%{$program->target_barangay}%");
        }

        $beneficiaries = $query->take($limit)->get();
        $enrolled = 0;

        foreach ($beneficiaries as $b) {
            $crn = $b->civil_registry_id ?: $b->civilregistry_id ?: $b->beneficiary_id ?: "CRN-{$b->id}";
            $record = DistributionEnrollment::firstOrCreate(
                [
                    'ayuda_program_id' => $program->id,
                    'civil_registry_id' => $crn,
                ],
                [
                    'beneficiary_id' => $b->id,
                    'household_no' => $b->household_no ?? 'N/A',
                    'status' => DistributionStatus::PENDING,
                    'enrolled_at' => now(),
                ]
            );
            if ($record->wasRecentlyCreated) {
                $enrolled++;
            }
        }

        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'success', message: "Enlisted {$enrolled} beneficiaries into {$program->program_code} distribution queue.");
    }

    public function paginationView(): string
    {
        return 'components.pagination';
    }

    public function render(PerformanceCacheService $cacheService)
    {
        $activeProjects = AyudaProgram::where('status', ProgramStatus::Active)
            ->orderBy('title')
            ->get();

        // Filtered list for the project picker modal.
        // Computed from the already-loaded collection — no extra DB query (requirement 1).
        // Search matches both program_code and title, case-insensitively (requirement 5).
        $pickerPrograms = $this->projectPickerSearch
            ? $activeProjects->filter(function (AyudaProgram $p): bool {
                $term = mb_strtolower($this->projectPickerSearch);

                return str_contains(mb_strtolower($p->program_code), $term)
                    || str_contains(mb_strtolower($p->title), $term);
            })->values()
            : $activeProjects;

        $currentProject = $this->selectedProjectId
            ? AyudaProgram::with('fundingSource')->find($this->selectedProjectId)
            : null;

        // 3-Bucket Query Sets (Independently Paginated)
        $pendingQuery = DistributionEnrollment::with('beneficiary')
            ->where('ayuda_program_id', $this->selectedProjectId)
            ->where('status', DistributionStatus::PENDING);

        if ($this->pendingSearch) {
            $search = $this->pendingSearch;
            $matchingIds = Beneficiary::where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $pendingQuery->where(function ($q) use ($matchingIds, $search) {
                if (! empty($matchingIds)) {
                    $q->whereIn('beneficiary_id', $matchingIds)
                        ->orWhere('civil_registry_id', 'like', "%{$search}%")
                        ->orWhere('household_no', 'like', "%{$search}%");
                } else {
                    $q->where('civil_registry_id', 'like', "%{$search}%")
                        ->orWhere('household_no', 'like', "%{$search}%");
                }
            });
        }
        $pendingList = $pendingQuery->latest('enrolled_at')->paginate(10, ['*'], 'pendingPage');

        $releasedQuery = DistributionEnrollment::with(['beneficiary', 'processor'])
            ->where('ayuda_program_id', $this->selectedProjectId)
            ->where('status', DistributionStatus::RELEASED);

        if ($this->releasedSearch) {
            $search = $this->releasedSearch;
            $matchingIds = Beneficiary::where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $releasedQuery->where(function ($q) use ($matchingIds, $search) {
                if (! empty($matchingIds)) {
                    $q->whereIn('beneficiary_id', $matchingIds)
                        ->orWhere('civil_registry_id', 'like', "%{$search}%")
                        ->orWhere('household_no', 'like', "%{$search}%");
                } else {
                    $q->where('civil_registry_id', 'like', "%{$search}%")
                        ->orWhere('household_no', 'like', "%{$search}%");
                }
            });
        }
        $releasedList = $releasedQuery->latest('processed_at')->paginate(10, ['*'], 'releasedPage');

        $unreleasedQuery = DistributionEnrollment::with('beneficiary')
            ->where('ayuda_program_id', $this->selectedProjectId)
            ->where('status', DistributionStatus::UNRELEASED);

        if ($this->unreleasedSearch) {
            $search = $this->unreleasedSearch;
            $matchingIds = Beneficiary::where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('full_name', 'like', "%{$search}%")
                ->pluck('id')
                ->toArray();

            $unreleasedQuery->where(function ($q) use ($matchingIds, $search) {
                if (! empty($matchingIds)) {
                    $q->whereIn('beneficiary_id', $matchingIds)
                        ->orWhere('civil_registry_id', 'like', "%{$search}%")
                        ->orWhere('household_no', 'like', "%{$search}%");
                } else {
                    $q->where('civil_registry_id', 'like', "%{$search}%")
                        ->orWhere('household_no', 'like', "%{$search}%");
                }
            });
        }
        $unreleasedList = $unreleasedQuery->latest('processed_at')->paginate(10, ['*'], 'unreleasedPage');

        // Masterlist search for picker modal
        $pickerBeneficiaries = [];
        if ($this->showBeneficiaryPickerModal) {
            try {
                $pickerQ = Beneficiary::query();
                if ($this->pickerSearch) {
                    $pickerQ->where(function ($q) {
                        $q->where('first_name', 'like', "%{$this->pickerSearch}%")
                            ->orWhere('last_name', 'like', "%{$this->pickerSearch}%")
                            ->orWhere('full_name', 'like', "%{$this->pickerSearch}%")
                            ->orWhere('beneficiary_id', 'like', "%{$this->pickerSearch}%")
                            ->orWhere('civilregistry_id', 'like', "%{$this->pickerSearch}%");
                    });
                }
                if ($this->pickerBarangay) {
                    $pickerQ->where('address', 'like', "%{$this->pickerBarangay}%");
                }
                $pickerBeneficiaries = $pickerQ->take(15)->get();
            } catch (\Throwable) {
                $pickerBeneficiaries = collect();
            }
        }

        $barangays = $cacheService->getBarangays();

        return view('livewire.distribution.workspace', [
            'activeProjects' => $activeProjects,
            'pickerPrograms' => $pickerPrograms,
            'currentProject' => $currentProject,
            'pendingList' => $pendingList,
            'releasedList' => $releasedList,
            'unreleasedList' => $unreleasedList,
            'pickerBeneficiaries' => $pickerBeneficiaries,
            'barangays' => $barangays,
        ]);
    }
}
