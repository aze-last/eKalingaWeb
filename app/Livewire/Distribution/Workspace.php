<?php

namespace App\Livewire\Distribution;

use App\Enums\DistributionStatus;
use App\Enums\ProgramStatus;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\DistributionEnrollment;
use App\Services\DistributionReleaseService;
use App\Services\HouseholdVerificationService;
use Exception;
use Illuminate\Support\Facades\DB;
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

    public function mount(): void
    {
        $firstActive = AyudaProgram::where('status', ProgramStatus::Active)->first();
        $this->selectedProjectId = $firstActive?->id;
    }

    public function selectProject(int $id): void
    {
        $this->selectedProjectId = $id;
        $this->resetPage('pendingPage');
        $this->resetPage('releasedPage');
        $this->resetPage('unreleasedPage');
    }

    /**
     * Handle Barcode / QR Scanner Input submission.
     */
    public function handleScan(string $scannedPayload, DistributionReleaseService $releaseService, HouseholdVerificationService $householdService): void
    {
        if ($this->isScannerLocked || ! $this->selectedProjectId) {
            return;
        }

        $cleanPayload = trim($scannedPayload);
        if (empty($cleanPayload)) {
            return;
        }

        $program = AyudaProgram::find($this->selectedProjectId);
        if (! $program) {
            return;
        }

        // Try to match beneficiary by Civil Registry ID, Household ID, or exact ID
        $beneficiary = Beneficiary::where('civil_registry_id', $cleanPayload)
            ->orWhere('household_no', $cleanPayload)
            ->orWhere('id', (int) $cleanPayload)
            ->orWhereRaw("CONCAT('EKALIN-', civil_registry_id, '-', ?) = ?", [$program->program_code, $cleanPayload])
            ->first();

        if (! $beneficiary) {
            // Also try matching by name parts if payload formatted as "LASTNAME, FIRSTNAME"
            if (str_contains($cleanPayload, ',')) {
                [$last, $first] = explode(',', $cleanPayload, 2);
                $beneficiary = Beneficiary::where('last_name', 'like', trim($last).'%')
                    ->where('first_name', 'like', trim($first).'%')
                    ->first();
            }
        }

        if (! $beneficiary) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: "No beneficiary found matching scan code: {$cleanPayload}");

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
        $this->executeRelease($program, $beneficiary, 'QR_SCAN', $cleanPayload, $releaseService);
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

            $this->showSuccessOverlay = true;
            $this->isScannerLocked = true;
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "Claim {$result['claim']->claim_code} successfully disbursed!");
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

    public function enrollBeneficiary(int $beneficiaryId): void
    {
        if (! $this->selectedProjectId) {
            return;
        }

        $exists = DistributionEnrollment::where('ayuda_program_id', $this->selectedProjectId)
            ->where('beneficiary_id', $beneficiaryId)
            ->exists();

        if ($exists) {
            $this->dispatch('toast', type: 'warning', message: 'Beneficiary already enrolled in this project.');

            return;
        }

        DistributionEnrollment::create([
            'ayuda_program_id' => $this->selectedProjectId,
            'beneficiary_id' => $beneficiaryId,
            'status' => DistributionStatus::PENDING,
            'enrolled_at' => now(),
        ]);

        $this->dispatch('toast', type: 'success', message: 'Beneficiary added to Pending queue.');
    }

    public function render()
    {
        $activeProjects = AyudaProgram::where('status', ProgramStatus::Active)->latest()->get();
        $currentProject = $this->selectedProjectId ? AyudaProgram::with('fundingSource')->find($this->selectedProjectId) : null;

        // 3-Bucket Query Sets (Independently Paginated)
        $pendingQuery = DistributionEnrollment::with('beneficiary')
            ->where('ayuda_program_id', $this->selectedProjectId)
            ->where('status', DistributionStatus::PENDING);

        if ($this->pendingSearch) {
            $pendingQuery->whereHas('beneficiary', function ($q) {
                $q->where('first_name', 'like', "%{$this->pendingSearch}%")
                    ->orWhere('last_name', 'like', "%{$this->pendingSearch}%")
                    ->orWhere('civil_registry_id', 'like', "%{$this->pendingSearch}%")
                    ->orWhere('household_no', 'like', "%{$this->pendingSearch}%");
            });
        }
        $pendingList = $pendingQuery->latest('enrolled_at')->paginate(10, ['*'], 'pendingPage');

        $releasedQuery = DistributionEnrollment::with(['beneficiary', 'processor'])
            ->where('ayuda_program_id', $this->selectedProjectId)
            ->where('status', DistributionStatus::RELEASED);

        if ($this->releasedSearch) {
            $releasedQuery->whereHas('beneficiary', function ($q) {
                $q->where('first_name', 'like', "%{$this->releasedSearch}%")
                    ->orWhere('last_name', 'like', "%{$this->releasedSearch}%")
                    ->orWhere('civil_registry_id', 'like', "%{$this->releasedSearch}%");
            });
        }
        $releasedList = $releasedQuery->latest('processed_at')->paginate(10, ['*'], 'releasedPage');

        $unreleasedQuery = DistributionEnrollment::with('beneficiary')
            ->where('ayuda_program_id', $this->selectedProjectId)
            ->where('status', DistributionStatus::UNRELEASED);

        if ($this->unreleasedSearch) {
            $unreleasedQuery->whereHas('beneficiary', function ($q) {
                $q->where('first_name', 'like', "%{$this->unreleasedSearch}%")
                    ->orWhere('last_name', 'like', "%{$this->unreleasedSearch}%");
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

        try {
            $barangays = DB::connection('crs')->table('barangays')->orderBy('name')->pluck('name')->toArray();
        } catch (\Throwable) {
            $barangays = GgmsConsolidatedTransaction::distinct()->whereNotNull('barangay')->where('barangay', '!=', '')->orderBy('barangay')->pluck('barangay')->toArray();
        }

        if (empty($barangays)) {
            $barangays = [
                'Balasinon', 'Buguis', 'Carre', 'Clib', 'Harada Yano', 'Ibo', 'Inayagan',
                'Kiblagon', 'Labon', 'Lapediche', 'Luparan', 'Mckinley', 'New Cebu',
                'Osmeña', 'Palili', 'Parame', 'Poblacion', 'Roxas', 'Solongvale',
                'Tagolilong', 'Tala-o', 'Talas', 'Tanwalang', 'Waterfall',
            ];
        }

        return view('livewire.distribution.workspace', [
            'activeProjects' => $activeProjects,
            'currentProject' => $currentProject,
            'pendingList' => $pendingList,
            'releasedList' => $releasedList,
            'unreleasedList' => $unreleasedList,
            'pickerBeneficiaries' => $pickerBeneficiaries,
            'barangays' => $barangays,
        ]);
    }
}
