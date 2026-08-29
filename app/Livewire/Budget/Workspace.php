<?php

namespace App\Livewire\Budget;

use App\Enums\DistributionStatus;
use App\Enums\FundingType;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\BudgetLedgerEntry;
use App\Models\DistributionEnrollment;
use App\Models\Donation;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use App\Models\GgmsProjectCache;
use App\Services\BudgetLedgerService;
use App\Services\GgmsBudgetSyncService;
use App\Services\GgmsProjectSyncService;
use App\Services\PerformanceCacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('Budget Management - eKalinga+')]
class Workspace extends Component
{
    use WithPagination;

    // Active sub-tabs: overview, registry, ledger, ggms_sync
    public string $activeTab = 'overview';

    // ==========================================
    // UNIFIED REGISTRY STATE
    // ==========================================
    public string $registryCategory = 'ALL'; // ALL, GOV_FUND, GGMS_PROJECT, PRIVATE_DONATION, DISTRIBUTION_PROJECT

    public string $registrySearch = '';

    public bool $showRegistryInspector = false;

    public ?array $inspectingRecord = null;

    // ==========================================
    // IMMUTABLE LEDGER FILTER STATE
    // ==========================================
    public string $ledgerSearch = '';

    public string $ledgerFilterType = 'ALL';

    public ?int $ledgerFundingSourceId = null;

    // ==========================================
    // PRIVATE DONATION MODAL STATE
    // ==========================================
    public bool $showDonationModal = false;

    public string $donationType = 'Cash'; // Cash, Goods

    public string $donorType = 'Organization'; // Person, Organization

    public string $donorName = '';

    public string $donorContact = '';

    public string $donorEmail = '';

    public string $donationDate = '';

    public string $donationNotes = '';

    public string $cashAmount = '';

    public string $goodsItemName = '';

    public string $goodsQuantity = '';

    public string $goodsUnit = 'Sacks';

    public string $goodsEstimatedValue = '';

    // ==========================================
    // PROJECT CREATION WIZARD STATE (4 STEPS)
    // ==========================================
    public bool $showProjectModal = false;

    public int $wizardStep = 1;

    public ?int $newProjectFundingSourceId = null;

    public string $newProjectTitle = '';

    public string $newProjectBenefitType = 'Cash'; // Cash, Goods

    public string $newProjectBudgetCap = '';

    public string $newProjectUnitAmount = '5000';

    public string $newProjectItemName = '';

    public string $newProjectItemUnit = 'Sacks';

    public string $newProjectItemQty = '1';

    public string $newProjectTargetCount = '50';

    public ?string $newProjectTargetBarangay = '';

    public string $newProjectStartDate = '';

    public string $newProjectEndDate = '';

    public string $newProjectDescription = '';

    public bool $autoEnrollAllBarangay = false;

    // Step 4: Beneficiary Selection & Review Pool
    public string $candidateSearch = '';

    public string $candidateBarangay = '';

    public array $selectedBeneficiaries = []; // Keyed by identifier

    // Step 4b: Household Review Modal State
    public bool $showHouseholdModal = false;

    public ?array $reviewingCandidate = null;

    public array $reviewingHouseholdMembers = [];

    public int $reviewingHouseholdTotalBenefits = 0;

    public string $reviewingDemographicsSummary = '';

    public string $reviewingHouseholdHead = 'No linked household on record';

    public string $reviewingHouseholdCode = '';

    // ==========================================
    // REALLOCATION MODAL STATE
    // ==========================================
    public bool $showReallocationModal = false;

    public ?int $reallocatingProgramId = null;

    public function mount(): void
    {
        $this->donationDate = now()->toDateString();
        $this->newProjectStartDate = now()->toDateString();
        $this->newProjectEndDate = now()->addDays(30)->toDateString();
    }

    // ------------------------------------------
    // UNIFIED REGISTRY INSPECTOR
    // ------------------------------------------
    public function openRegistryInspector(string $category, string|int $id): void
    {
        switch ($category) {
            case 'Government Fund':
                $fund = FundingSource::with(['ayudaPrograms', 'budgetLedgerEntries'])->find($id);
                if ($fund) {
                    $this->inspectingRecord = [
                        'category' => 'Government Fund',
                        'code' => $fund->source_code,
                        'title' => $fund->title,
                        'office' => $fund->office,
                        'fiscal_year' => $fund->fiscal_year,
                        'allocated' => $fund->allocated_amount,
                        'spent' => $fund->spent_amount,
                        'balance' => $fund->remaining_balance,
                        'status' => $fund->status,
                        'programs_count' => $fund->ayudaPrograms->count(),
                        'created_at' => $fund->created_at?->format('M d, Y h:i A') ?? 'N/A',
                        'notes' => "Official LGU appropriation envelope managed under {$fund->office}.",
                        'details' => $fund->ayudaPrograms->map(fn ($p) => [
                            'code' => $p->program_code,
                            'title' => $p->title,
                            'cap' => $p->budget_cap,
                            'disbursed' => $p->disbursed_amount,
                            'status' => $p->status->value ?? 'Active',
                        ])->toArray(),
                    ];
                    $this->showRegistryInspector = true;
                }
                break;

            case 'GGMS Project':
                $cache = GgmsProjectCache::find($id);
                if ($cache) {
                    $allocated = (float) ($cache->allocated_budget ?: $cache->total_allocation);
                    $spent = (float) $cache->spent_budget;
                    $this->inspectingRecord = [
                        'category' => 'GGMS Project',
                        'code' => (string) ($cache->project_details_id ?: "OPP-{$cache->id}"),
                        'title' => $cache->title,
                        'office' => (string) ($cache->office ?: 'OFF-2026-0006'),
                        'fiscal_year' => $cache->fiscal_year ?: 2026,
                        'allocated' => $allocated,
                        'spent' => $spent,
                        'balance' => max(0.00, $allocated - $spent),
                        'status' => ucfirst($cache->status ?? 'active'),
                        'programs_count' => 0,
                        'created_at' => $cache->last_synced_at?->format('M d, Y h:i A') ?? 'Synced',
                        'notes' => $cache->description ?: "Earmarked GGMS Grant Envelope (Voucher: {$cache->voucher_code})",
                        'details' => [
                            'voucher_code' => $cache->voucher_code,
                            'sync_status' => $cache->status,
                        ],
                    ];
                    $this->showRegistryInspector = true;
                }
                break;

            case 'Private Donation':
                $fund = FundingSource::with(['donations', 'goodsDonations'])->find($id);
                if ($fund) {
                    $donations = $fund->donations->concat($fund->goodsDonations);
                    $this->inspectingRecord = [
                        'category' => 'Private Donation',
                        'code' => $fund->source_code,
                        'title' => $fund->title,
                        'office' => $fund->office,
                        'fiscal_year' => $fund->fiscal_year,
                        'allocated' => $fund->allocated_amount,
                        'spent' => $fund->spent_amount,
                        'balance' => $fund->remaining_balance,
                        'status' => $fund->status,
                        'programs_count' => $fund->donations->count() + $fund->goodsDonations->count(),
                        'created_at' => $fund->created_at?->format('M d, Y h:i A') ?? 'N/A',
                        'notes' => 'Philanthropic contribution earmarked for indigent social welfare.',
                        'details' => $donations->map(fn ($d) => [
                            'donor_name' => $d->donor_name,
                            'donor_type' => $d->donor_type ?? 'Organization',
                            'amount' => $d->amount ?? $d->estimated_value ?? 0,
                            'date' => $d->donation_date ?? 'N/A',
                            'notes' => $d->notes ?? '',
                        ])->toArray(),
                    ];
                    $this->showRegistryInspector = true;
                }
                break;

            case 'Distribution Project':
                $prog = AyudaProgram::with(['fundingSource', 'claims', 'enrollments'])->find($id);
                if ($prog) {
                    $this->inspectingRecord = [
                        'category' => 'Distribution Project',
                        'code' => $prog->program_code,
                        'title' => $prog->title,
                        'office' => $prog->fundingSource?->office ?? 'MSWDO',
                        'fiscal_year' => date('Y'),
                        'allocated' => $prog->budget_cap,
                        'spent' => $prog->disbursed_amount,
                        'balance' => $prog->remaining_balance,
                        'status' => $prog->status->value ?? 'Active',
                        'programs_count' => $prog->claims->count(),
                        'created_at' => $prog->created_at?->format('M d, Y h:i A') ?? 'N/A',
                        'notes' => $prog->description ?: 'Operational distribution project created under eKalinga+.',
                        'details' => [
                            'benefit_type' => $prog->benefit_type->value ?? 'Cash',
                            'unit_amount' => $prog->unit_amount,
                            'target_beneficiaries' => $prog->target_beneficiaries,
                            'target_barangay' => $prog->target_barangay ?: 'Municipality-Wide',
                            'enrolled_count' => $prog->enrollments->count(),
                            'claimed_count' => $prog->claims->count(),
                            'source_pool' => $prog->fundingSource?->title ?? 'N/A',
                        ],
                    ];
                    $this->showRegistryInspector = true;
                }
                break;
        }
    }

    public function closeRegistryInspector(): void
    {
        $this->showRegistryInspector = false;
        $this->inspectingRecord = null;
    }

    // ------------------------------------------
    // PRIVATE DONATION ACTIONS
    // ------------------------------------------
    public function openDonationModal(): void
    {
        $this->reset([
            'donationType', 'donorType', 'donorName', 'donorContact',
            'donorEmail', 'donationNotes', 'cashAmount', 'goodsItemName',
            'goodsQuantity', 'goodsEstimatedValue',
        ]);
        $this->donationType = 'Cash';
        $this->donorType = 'Organization';
        $this->donationDate = now()->toDateString();
        $this->showDonationModal = true;
    }

    public function closeDonationModal(): void
    {
        $this->showDonationModal = false;
    }

    public function saveDonation(BudgetLedgerService $budgetService): void
    {
        if ($this->donationType === 'Cash') {
            $this->validate([
                'donorName' => 'required|string|min:2',
                'cashAmount' => 'required|numeric|min:1',
            ]);

            $budgetService->recordCashDonation([
                'donor_type' => $this->donorType,
                'donor_name' => $this->donorName,
                'contact_no' => $this->donorContact,
                'email' => $this->donorEmail,
                'amount' => (float) $this->cashAmount,
                'donation_date' => $this->donationDate,
                'notes' => $this->donationNotes,
            ]);
        } else {
            $this->validate([
                'donorName' => 'required|string|min:2',
                'goodsItemName' => 'required|string|min:2',
                'goodsQuantity' => 'required|integer|min:1',
                'goodsEstimatedValue' => 'nullable|numeric|min:0',
            ]);

            $budgetService->recordGoodsDonation([
                'donor_type' => $this->donorType,
                'donor_name' => $this->donorName,
                'contact_no' => $this->donorContact,
                'item_name' => $this->goodsItemName,
                'quantity' => (int) $this->goodsQuantity,
                'unit' => $this->goodsUnit,
                'estimated_value' => (float) ($this->goodsEstimatedValue ?: 0),
                'donation_date' => $this->donationDate,
                'notes' => $this->donationNotes,
            ]);
        }

        app(PerformanceCacheService::class)->clearFundingCache();

        $this->showDonationModal = false;
        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'success', message: 'Private donation registered and posted to immutable ledger.');
    }

    // ------------------------------------------
    // PROJECT CREATION WIZARD (4 STEPS)
    // ------------------------------------------
    #[Computed]
    public function selectedFundingSource(): ?FundingSource
    {
        return $this->newProjectFundingSourceId
            ? FundingSource::find($this->newProjectFundingSourceId)
            : null;
    }

    #[Computed]
    public function calculatedTotalCost(): float
    {
        $unit = (float) ($this->newProjectUnitAmount ?: 0);
        $count = (int) ($this->newProjectTargetCount ?: 0);

        return $unit * $count;
    }

    public function getSelectedFundingSourceProperty(): ?FundingSource
    {
        return $this->selectedFundingSource;
    }

    public function getCalculatedTotalCostProperty(): float
    {
        return $this->calculatedTotalCost;
    }

    public function setBudgetCapToMax(): void
    {
        $source = $this->selectedFundingSource;
        if ($source) {
            $this->newProjectBudgetCap = (string) (float) $source->remaining_balance;
            $this->validateBudgetCapRealtime();
        }
    }

    public function syncBudgetCapWithCalculated(): void
    {
        $calculated = $this->calculatedTotalCost;
        if ($calculated > 0) {
            $this->newProjectBudgetCap = (string) $calculated;
            $this->validateBudgetCapRealtime();
        }
    }

    public function updatedNewProjectFundingSourceId(): void
    {
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectBudgetCap(): void
    {
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectUnitAmount(): void
    {
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectTargetCount(): void
    {
        $this->validateBudgetCapRealtime();
    }

    protected function validateBudgetCapRealtime(): void
    {
        if (! $this->newProjectFundingSourceId || $this->newProjectBudgetCap === '') {
            return;
        }

        $source = $this->selectedFundingSource;
        if (! $source) {
            return;
        }

        $max = (float) $source->remaining_balance;
        $entered = (float) $this->newProjectBudgetCap;

        if ($entered > $max) {
            $this->addError(
                'newProjectBudgetCap',
                'Budget Cap (₱'.number_format($entered, 2).') exceeds the selected funding source available balance of ₱'.number_format($max, 2).'.'
            );
        } else {
            $this->resetErrorBag('newProjectBudgetCap');
        }
    }

    public function openProjectModal(): void
    {
        $firstSource = FundingSource::where('remaining_balance', '>', 0)->first();
        $this->newProjectFundingSourceId = $firstSource?->id;
        $this->newProjectTitle = '';
        $this->newProjectBenefitType = 'Cash';
        $this->newProjectBudgetCap = '';
        $this->newProjectUnitAmount = '5000';
        $this->newProjectItemName = '';
        $this->newProjectItemUnit = 'Sacks';
        $this->newProjectItemQty = '1';
        $this->newProjectTargetCount = '50';
        $this->newProjectTargetBarangay = '';
        $this->wizardStep = 1;
        $this->selectedBeneficiaries = [];
        $this->candidateSearch = '';
        $this->candidateBarangay = '';
        $this->resetErrorBag();
        $this->showProjectModal = true;
    }

    public function closeProjectModal(): void
    {
        $this->showProjectModal = false;
        $this->showHouseholdModal = false;
        $this->resetErrorBag();
    }

    public function goToStep(int $step): void
    {
        if ($step > 1) {
            $this->validate([
                'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
            ]);
        }
        if ($step > 2) {
            $source = FundingSource::find($this->newProjectFundingSourceId);
            $maxBalance = $source ? (float) $source->remaining_balance : 0.00;
            $this->validate([
                'newProjectTitle' => 'required|string|min:3',
                'newProjectBudgetCap' => "required|numeric|min:1|max:{$maxBalance}",
            ], [
                'newProjectBudgetCap.max' => 'Budget Cap cannot exceed the remaining balance of the selected funding source (₱'.number_format($maxBalance, 2).').',
            ]);
        }
        $this->wizardStep = $step;
    }

    public function nextStep(): void
    {
        if ($this->wizardStep === 1) {
            $this->validate([
                'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
                'newProjectBenefitType' => 'required|in:Cash,Goods',
            ]);
            $this->wizardStep = 2;
        } elseif ($this->wizardStep === 2) {
            $source = FundingSource::find($this->newProjectFundingSourceId);
            $maxBalance = $source ? (float) $source->remaining_balance : 0.00;
            $this->validate([
                'newProjectTitle' => 'required|string|min:3',
                'newProjectBudgetCap' => "required|numeric|min:1|max:{$maxBalance}",
            ], [
                'newProjectBudgetCap.max' => 'Budget Cap cannot exceed the remaining balance of the selected funding source (₱'.number_format($maxBalance, 2).').',
            ]);
            $this->wizardStep = 3;
        } elseif ($this->wizardStep === 3) {
            $this->wizardStep = 4;
        }
    }

    public function prevStep(): void
    {
        if ($this->wizardStep > 1) {
            $this->wizardStep--;
        }
    }

    // ------------------------------------------
    // STEP 4: BENEFICIARY PICKER & HOUSEHOLD REVIEW
    // ------------------------------------------
    public function openHouseholdReview(int $beneficiaryId): void
    {
        $candidate = Beneficiary::find($beneficiaryId);
        if (! $candidate) {
            return;
        }

        $this->reviewingCandidate = [
            'id' => $candidate->id,
            'civil_registry_id' => $candidate->civil_registry_id ?: $candidate->civilregistry_id ?: "CRN-{$candidate->id}",
            'beneficiary_id' => $candidate->beneficiary_id ?: "BEN-{$candidate->id}",
            'full_name' => $candidate->full_name ?: trim("{$candidate->first_name} {$candidate->last_name}"),
            'household_no' => $candidate->household_no ?? 'N/A',
            'barangay' => $candidate->barangay,
            'address' => $candidate->address,
            'sex' => $candidate->gender ?? $candidate->sex ?? 'N/A',
            'birth_date' => $candidate->birthDate ?? $candidate->date_of_birth ?? 'N/A',
        ];

        // Retrieve demographic and household details from CRS
        $householdNumber = null;
        $demoSummary = '';
        $headName = 'No linked household on record';
        $members = [];

        try {
            if ($candidate->demographic_characteristic_id) {
                $demo = DB::connection('crs')->table('demographic_characteristics')
                    ->where('id', $candidate->demographic_characteristic_id)
                    ->first();

                if ($demo) {
                    $householdNumber = $demo->household_number;
                    $demoParts = array_filter([$demo->marital_status, $demo->ethnicity, $demo->tribe]);
                    $demoSummary = implode(' · ', $demoParts);

                    if ($householdNumber) {
                        $rawMembers = DB::connection('crs')->table('demographic_characteristics')
                            ->where('household_number', $householdNumber)
                            ->get();

                        foreach ($rawMembers as $m) {
                            if ($m->position === 'family_head' || strtolower($m->relationship_to_head ?? '') === 'head') {
                                $headName = $m->full_name;
                            }

                            // Query local claims and GGMS cross-records for this member
                            $claimsCount = AyudaProjectClaim::where('civil_registry_id', $m->family_id ?? $m->household_number)
                                ->orWhere('beneficiary_id', $m->id)
                                ->count();

                            $ggmsCount = GgmsConsolidatedTransaction::where('civil_registry_id', $m->family_id ?? $m->household_number)
                                ->orWhere('beneficiary_name', 'like', "%{$m->last_name}%")
                                ->count();

                            $totalBenefits = $claimsCount + $ggmsCount;

                            $members[] = [
                                'full_name' => $m->full_name,
                                'relationship' => $m->relationship_to_head ?: ($m->position === 'family_head' ? 'Household Head' : 'Family Member'),
                                'sex' => $m->sex,
                                'age' => $m->age,
                                'benefits_count' => $totalBenefits,
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable) {
        }

        // If no members were found from relations, represent the single candidate
        if (empty($members)) {
            $claimsCount = AyudaProjectClaim::where('civil_registry_id', $this->reviewingCandidate['civil_registry_id'])->count();
            $members[] = [
                'full_name' => $this->reviewingCandidate['full_name'],
                'relationship' => 'Primary Applicant',
                'sex' => $this->reviewingCandidate['sex'],
                'age' => 'N/A',
                'benefits_count' => $claimsCount,
            ];
        }

        $this->reviewingHouseholdMembers = $members;
        $this->reviewingHouseholdTotalBenefits = array_sum(array_column($members, 'benefits_count'));
        $this->reviewingDemographicsSummary = $demoSummary;
        $this->reviewingHouseholdHead = $headName;
        $this->reviewingHouseholdCode = $householdNumber ?: ($candidate->household_no ?? 'N/A');

        $this->showHouseholdModal = true;
    }

    public function closeHouseholdReview(): void
    {
        $this->showHouseholdModal = false;
        $this->reviewingCandidate = null;
    }

    public function confirmAddCandidate(): void
    {
        if (! $this->reviewingCandidate) {
            return;
        }

        $key = $this->reviewingCandidate['civil_registry_id'];
        $this->selectedBeneficiaries[$key] = $this->reviewingCandidate;

        $this->showHouseholdModal = false;
        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'info', message: "Added {$this->reviewingCandidate['full_name']} to candidate roster.");
        $this->reviewingCandidate = null;
    }

    public function autoFillCandidatePool(): void
    {
        $targetCount = max(1, (int) ($this->newProjectTargetCount ?: 50));
        $query = Beneficiary::query();
        if ($this->candidateBarangay) {
            $query->where('address', 'like', "%{$this->candidateBarangay}%");
        } elseif ($this->newProjectTargetBarangay) {
            $query->where('address', 'like', "%{$this->newProjectTargetBarangay}%");
        }

        $candidates = $query->take($targetCount)->get();
        $added = 0;
        foreach ($candidates as $c) {
            $crn = $c->civil_registry_id ?: $c->civilregistry_id ?: $c->beneficiary_id ?: "CRN-{$c->id}";
            $this->selectedBeneficiaries[$crn] = [
                'id' => $c->id,
                'civil_registry_id' => $crn,
                'beneficiary_id' => $c->beneficiary_id ?: "BEN-{$c->id}",
                'full_name' => $c->full_name ?: trim("{$c->first_name} {$c->last_name}"),
                'household_no' => $c->household_no ?? 'N/A',
                'barangay' => $c->barangay ?: ($c->address ?? 'Sulop'),
                'address' => $c->address,
                'sex' => $c->gender ?? $c->sex ?? 'N/A',
                'birth_date' => $c->birthDate ?? $c->date_of_birth ?? 'N/A',
            ];
            $added++;
        }

        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'info', message: "Enlisted {$added} candidate citizen(s) into project roster.");
    }

    public function removeCandidate(string $key): void
    {
        unset($this->selectedBeneficiaries[$key]);
    }

    public function clearAllCandidates(): void
    {
        $this->selectedBeneficiaries = [];
    }

    // ------------------------------------------
    // FINALIZE PROJECT CREATION
    // ------------------------------------------
    public function createProject(?BudgetLedgerService $budgetService = null): void
    {
        $budgetService ??= app(BudgetLedgerService::class);
        $source = FundingSource::findOrFail($this->newProjectFundingSourceId);
        $maxBalance = (float) $source->remaining_balance;

        $this->validate([
            'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
            'newProjectTitle' => 'required|string|min:3',
            'newProjectBudgetCap' => "required|numeric|min:1|max:{$maxBalance}",
            'newProjectBenefitType' => 'required|in:Cash,Goods',
        ], [
            'newProjectBudgetCap.max' => 'Budget Cap cannot exceed the remaining balance of the selected funding source (₱'.number_format($maxBalance, 2).').',
        ]);

        try {
            $program = $budgetService->createAyudaProgram([
                'funding_source_id' => $this->newProjectFundingSourceId,
                'title' => $this->newProjectTitle,
                'benefit_type' => $this->newProjectBenefitType,
                'budget_cap' => (float) $this->newProjectBudgetCap,
                'unit_amount' => (float) ($this->newProjectUnitAmount ?: 0),
                'item_name' => $this->newProjectItemName,
                'item_unit' => $this->newProjectItemUnit,
                'item_quantity_per_beneficiary' => (int) ($this->newProjectItemQty ?: 1),
                'target_beneficiaries' => count($this->selectedBeneficiaries) > 0 ? count($this->selectedBeneficiaries) : (int) ($this->newProjectTargetCount ?: 0),
                'target_barangay' => $this->newProjectTargetBarangay ?: null,
                'start_date' => $this->newProjectStartDate ?: now()->toDateString(),
                'end_date' => $this->newProjectEndDate ?: null,
                'description' => $this->newProjectDescription ?: null,
            ]);

            $enrolledCount = 0;

            // 1. Enrol manually selected candidates if any
            if (! empty($this->selectedBeneficiaries)) {
                foreach ($this->selectedBeneficiaries as $b) {
                    DistributionEnrollment::firstOrCreate(
                        [
                            'ayuda_program_id' => $program->id,
                            'civil_registry_id' => $b['civil_registry_id'],
                        ],
                        [
                            'beneficiary_id' => $b['id'] ?? null,
                            'household_no' => $b['household_no'] ?? null,
                            'status' => DistributionStatus::PENDING,
                            'enrolled_at' => now(),
                        ]
                    );
                    $enrolledCount++;
                }
            } else {
                // 2. Auto-enroll eligible citizens matching target count & target scope
                try {
                    $query = Beneficiary::query();
                    if ($this->newProjectTargetBarangay) {
                        $query->where('address', 'like', "%{$this->newProjectTargetBarangay}%");
                    }
                    $limit = max(1, (int) ($this->newProjectTargetCount ?: 50));
                    $autoBeneficiaries = $query->take($limit)->get();

                    foreach ($autoBeneficiaries as $b) {
                        $crn = $b->civil_registry_id ?: $b->civilregistry_id ?: $b->beneficiary_id ?: "CRN-{$b->id}";
                        DistributionEnrollment::firstOrCreate(
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
                        $enrolledCount++;
                    }
                } catch (\Throwable $e) {
                    Log::error('Auto-enroll beneficiaries error: '.$e->getMessage());
                }
            }

            app(PerformanceCacheService::class)->clearFundingCache();

            $this->showProjectModal = false;
            $this->wizardStep = 1;
            $this->selectedBeneficiaries = [];
            $this->newProjectTitle = '';
            $this->newProjectBudgetCap = '';
            $this->activeTab = 'overview';
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "Created Ayuda Project {$program->program_code} with {$enrolledCount} enrolled beneficiaries.");
        } catch (\Throwable $e) {
            Log::error('Create project error: '.$e->getMessage());
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmReallocation(int $programId): void
    {
        $this->reallocatingProgramId = $programId;
        $this->showReallocationModal = true;
    }

    public function executeReallocation(?BudgetLedgerService $budgetService = null): void
    {
        $budgetService ??= app(BudgetLedgerService::class);

        if (! $this->reallocatingProgramId) {
            return;
        }

        $program = AyudaProgram::find($this->reallocatingProgramId);
        if ($program) {
            $budgetService->reallocateEarmark($program);
            app(PerformanceCacheService::class)->clearFundingCache();
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "Unspent funds from {$program->program_code} reallocated back to funding source.");
        }

        $this->showReallocationModal = false;
        $this->reallocatingProgramId = null;
    }

    public function syncGgms(
        ?GgmsBudgetSyncService $budgetSync = null,
        ?GgmsProjectSyncService $projectSync = null
    ): void {
        $budgetSync ??= app(GgmsBudgetSyncService::class);
        $projectSync ??= app(GgmsProjectSyncService::class);

        try {
            $budgetSync->syncOfficeBudget('OFF-2026-0006');
            $count = $projectSync->syncProjects('OFF-2026-0006');
            app(PerformanceCacheService::class)->clearFundingCache();

            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "GGMS synchronized: General Office Fund updated and {$count} Sub-Project Envelopes refreshed.");
        } catch (\Throwable) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: 'GGMS Sync Failed');
        }
    }

    // ------------------------------------------
    // RENDER
    // ------------------------------------------
    public function render(PerformanceCacheService $cacheService)
    {
        // 1. Financial Overview Aggregates
        $fundingSources = FundingSource::withCount('ayudaPrograms')->latest()->get();
        $govSources = $fundingSources->where('funding_type', FundingType::Government);
        $privateSources = $fundingSources->where('funding_type', FundingType::Private);

        $govAllocated = (float) $govSources->sum('allocated_amount');
        $govSpent = (float) $govSources->sum('spent_amount');
        $govBalance = (float) $govSources->sum('remaining_balance');

        $privateAllocated = (float) $privateSources->sum('allocated_amount');
        $privateSpent = (float) $privateSources->sum('spent_amount');
        $privateBalance = (float) $privateSources->sum('remaining_balance');

        $totalAllocated = $govAllocated + $privateAllocated;
        $totalDisbursed = $govSpent + $privateSpent;
        $totalAvailable = $govBalance + $privateBalance;

        // 2. Active Projects List
        $programs = AyudaProgram::with('fundingSource')
            ->latest()
            ->get();

        // 3. UNIFIED REGISTRY DATA MERGE (4 Categories)
        $registryItems = collect();

        // Category 1: Government Funds
        if (in_array($this->registryCategory, ['ALL', 'GOV_FUND'])) {
            foreach ($govSources as $gov) {
                $registryItems->push([
                    'id' => $gov->id,
                    'category' => 'Government Fund',
                    'code' => $gov->source_code,
                    'title' => $gov->title,
                    'allocated' => (float) $gov->allocated_amount,
                    'spent' => (float) $gov->spent_amount,
                    'balance' => (float) $gov->remaining_balance,
                    'status' => $gov->status ?? 'Active',
                    'date' => $gov->created_at?->format('Y-m-d') ?? date('Y-m-d'),
                    'detail_summary' => "{$gov->office} • FY {$gov->fiscal_year}",
                ]);
            }
        }

        // Category 2: GGMS Projects
        if (in_array($this->registryCategory, ['ALL', 'GGMS_PROJECT'])) {
            $ggmsList = GgmsProjectCache::latest()->get();
            foreach ($ggmsList as $prj) {
                $alloc = (float) ($prj->allocated_budget ?: $prj->total_allocation);
                $spent = (float) $prj->spent_budget;
                $registryItems->push([
                    'id' => $prj->id,
                    'category' => 'GGMS Project',
                    'code' => (string) ($prj->project_details_id ?: $prj->project_code),
                    'title' => $prj->title,
                    'allocated' => $alloc,
                    'spent' => $spent,
                    'balance' => max(0.00, $alloc - $spent),
                    'status' => ucfirst($prj->status ?? 'active'),
                    'date' => $prj->last_synced_at?->format('Y-m-d') ?? date('Y-m-d'),
                    'detail_summary' => "GGMS Grant • Voucher: {$prj->voucher_code}",
                ]);
            }
        }

        // Category 3: Private Donations
        if (in_array($this->registryCategory, ['ALL', 'PRIVATE_DONATION'])) {
            foreach ($privateSources as $priv) {
                $registryItems->push([
                    'id' => $priv->id,
                    'category' => 'Private Donation',
                    'code' => $priv->source_code,
                    'title' => $priv->title,
                    'allocated' => (float) $priv->allocated_amount,
                    'spent' => (float) $priv->spent_amount,
                    'balance' => (float) $priv->remaining_balance,
                    'status' => $priv->status ?? 'Active',
                    'date' => $priv->created_at?->format('Y-m-d') ?? date('Y-m-d'),
                    'detail_summary' => "Philanthropic Pool • {$priv->office}",
                ]);
            }
        }

        // Category 4: Distribution Projects
        if (in_array($this->registryCategory, ['ALL', 'DISTRIBUTION_PROJECT'])) {
            foreach ($programs as $prog) {
                $registryItems->push([
                    'id' => $prog->id,
                    'category' => 'Distribution Project',
                    'code' => $prog->program_code,
                    'title' => $prog->title,
                    'allocated' => (float) $prog->budget_cap,
                    'spent' => (float) $prog->disbursed_amount,
                    'balance' => (float) $prog->remaining_balance,
                    'status' => $prog->status->value ?? 'Active',
                    'date' => $prog->start_date ?? $prog->created_at?->format('Y-m-d'),
                    'detail_summary' => "Linked: {$prog->fundingSource?->source_code} • {$prog->benefit_type->value}",
                ]);
            }
        }

        // Apply Unified Search
        if ($this->registrySearch) {
            $search = strtolower($this->registrySearch);
            $registryItems = $registryItems->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['code']), $search) ||
                    str_contains(strtolower($item['title']), $search) ||
                    str_contains(strtolower($item['category']), $search) ||
                    str_contains(strtolower($item['detail_summary']), $search);
            });
        }

        // 4. Immutable Ledger Entries (Paginated)
        $ledgerQuery = BudgetLedgerEntry::with(['fundingSource', 'ayudaProgram', 'creator'])
            ->latest();

        if ($this->ledgerSearch) {
            $ledgerQuery->where(function ($q) {
                $q->where('reference_code', 'like', "%{$this->ledgerSearch}%")
                    ->orWhere('notes', 'like', "%{$this->ledgerSearch}%");
            });
        }

        if ($this->ledgerFilterType !== 'ALL') {
            $ledgerQuery->where('entry_type', $this->ledgerFilterType);
        }

        if ($this->ledgerFundingSourceId) {
            $ledgerQuery->where('funding_source_id', $this->ledgerFundingSourceId);
        }

        $ledgerEntries = $ledgerQuery->paginate(15);

        // 5. GGMS Project Caches
        $ggmsCaches = GgmsProjectCache::latest()->get();

        // 6. Cached Barangay List
        $barangays = $cacheService->getBarangays();

        // 7. Step 4 Candidate Pool (Capped at 200 server-side)
        $candidates = collect();
        $totalCandidatesCount = 0;
        if ($this->showProjectModal && $this->wizardStep === 4) {
            try {
                $candidateQ = Beneficiary::query();
                if ($this->candidateSearch) {
                    $candidateQ->where(function ($q) {
                        $q->where('full_name', 'like', "%{$this->candidateSearch}%")
                            ->orWhere('first_name', 'like', "%{$this->candidateSearch}%")
                            ->orWhere('last_name', 'like', "%{$this->candidateSearch}%")
                            ->orWhere('beneficiary_id', 'like', "%{$this->candidateSearch}%")
                            ->orWhere('civilregistry_id', 'like', "%{$this->candidateSearch}%");
                    });
                }
                if ($this->candidateBarangay) {
                    $candidateQ->where('address', 'like', "%{$this->candidateBarangay}%");
                }

                $totalCandidatesCount = $candidateQ->count();
                $candidates = $candidateQ->take(200)->get();
            } catch (\Throwable) {
                $candidates = collect();
                $totalCandidatesCount = 0;
            }
        }

        return view('livewire.budget.workspace', [
            'fundingSources' => $fundingSources,
            'govAllocated' => $govAllocated,
            'govSpent' => $govSpent,
            'govBalance' => $govBalance,
            'privateAllocated' => $privateAllocated,
            'privateSpent' => $privateSpent,
            'privateBalance' => $privateBalance,
            'totalAllocated' => $totalAllocated,
            'totalDisbursed' => $totalDisbursed,
            'totalAvailable' => $totalAvailable,
            'programs' => $programs,
            'registryItems' => $registryItems,
            'ledgerEntries' => $ledgerEntries,
            'ggmsCaches' => $ggmsCaches,
            'barangays' => $barangays,
            'candidates' => $candidates,
            'totalCandidatesCount' => $totalCandidatesCount,
        ]);
    }
}
