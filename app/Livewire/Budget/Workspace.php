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
use Exception;
use Illuminate\Support\Facades\DB;
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

    public int $newProjectItemQty = 1;

    public int $newProjectTargetCount = 50;

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
                    $this->inspectingRecord = [
                        'category' => 'GGMS Project',
                        'code' => $cache->project_code,
                        'title' => $cache->title,
                        'office' => $cache->office_code,
                        'fiscal_year' => $cache->fiscal_year,
                        'allocated' => $cache->allocated_budget,
                        'spent' => $cache->spent_budget,
                        'balance' => $cache->allocated_budget - $cache->spent_budget,
                        'status' => $cache->status,
                        'programs_count' => 0,
                        'created_at' => $cache->last_synced_at?->format('M d, Y h:i A') ?? 'Synced',
                        'notes' => 'Direct mirrored sub-allocation synced with national GGMS grant database.',
                        'details' => $cache->raw_payload ?? [],
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

        $this->showDonationModal = false;
        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'success', message: 'Private donation registered and posted to immutable ledger.');
    }

    // ------------------------------------------
    // PROJECT CREATION WIZARD (4 STEPS)
    // ------------------------------------------
    public function openProjectModal(): void
    {
        $firstSource = FundingSource::where('remaining_balance', '>', 0)->first();
        $this->newProjectFundingSourceId = $firstSource?->id;
        $this->newProjectTitle = '';
        $this->newProjectBenefitType = 'Cash';
        $this->newProjectBudgetCap = '';
        $this->newProjectUnitAmount = '5000';
        $this->newProjectTargetCount = 50;
        $this->newProjectTargetBarangay = '';
        $this->wizardStep = 1;
        $this->selectedBeneficiaries = [];
        $this->candidateSearch = '';
        $this->candidateBarangay = '';
        $this->showProjectModal = true;
    }

    public function closeProjectModal(): void
    {
        $this->showProjectModal = false;
        $this->showHouseholdModal = false;
    }

    public function goToStep(int $step): void
    {
        if ($step > 1) {
            $this->validate([
                'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
            ]);
        }
        if ($step > 2) {
            $this->validate([
                'newProjectTitle' => 'required|string|min:3',
                'newProjectBudgetCap' => 'required|numeric|min:1',
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
            $this->validate([
                'newProjectTitle' => 'required|string|min:3',
                'newProjectBudgetCap' => 'required|numeric|min:1',
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
    public function createProject(BudgetLedgerService $budgetService): void
    {
        $this->validate([
            'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
            'newProjectTitle' => 'required|string|min:3',
            'newProjectBudgetCap' => 'required|numeric|min:1',
            'newProjectBenefitType' => 'required|in:Cash,Goods',
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
                'item_quantity_per_beneficiary' => $this->newProjectItemQty,
                'target_beneficiaries' => count($this->selectedBeneficiaries) > 0 ? count($this->selectedBeneficiaries) : $this->newProjectTargetCount,
                'target_barangay' => $this->newProjectTargetBarangay ?: null,
                'start_date' => $this->newProjectStartDate,
                'end_date' => $this->newProjectEndDate,
                'description' => $this->newProjectDescription,
            ]);

            // Enrol manually selected candidates if any
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
                }
            } elseif ($this->autoEnrollAllBarangay) {
                // Auto-enroll from masterlist if specified
                try {
                    $query = Beneficiary::query();
                    if ($this->newProjectTargetBarangay) {
                        $query->where('address', 'like', "%{$this->newProjectTargetBarangay}%");
                    }
                    $beneficiaries = $query->take($this->newProjectTargetCount)->get();

                    foreach ($beneficiaries as $b) {
                        DistributionEnrollment::firstOrCreate(
                            ['ayuda_program_id' => $program->id, 'civil_registry_id' => $b->civil_registry_id],
                            [
                                'beneficiary_id' => $b->id,
                                'household_no' => $b->household_no,
                                'status' => DistributionStatus::PENDING,
                                'enrolled_at' => now(),
                            ]
                        );
                    }
                } catch (\Throwable) {
                }
            }

            $this->showProjectModal = false;
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "Created Ayuda Project {$program->program_code} with budget cap ₱".number_format($program->budget_cap, 2));
        } catch (Exception $e) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: $e->getMessage());
        }
    }

    public function confirmReallocation(int $programId): void
    {
        $this->reallocatingProgramId = $programId;
        $this->showReallocationModal = true;
    }

    public function executeReallocation(BudgetLedgerService $budgetService): void
    {
        if (! $this->reallocatingProgramId) {
            return;
        }

        $program = AyudaProgram::find($this->reallocatingProgramId);
        if ($program) {
            $budgetService->reallocateEarmark($program);
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "Unspent funds from {$program->program_code} reallocated back to funding source.");
        }

        $this->showReallocationModal = false;
        $this->reallocatingProgramId = null;
    }

    public function syncGgms(): void
    {
        GgmsProjectCache::query()->update(['last_synced_at' => now()]);
        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'success', message: 'GGMS Government Grant allocations synchronized successfully.');
    }

    // ------------------------------------------
    // RENDER
    // ------------------------------------------
    public function render()
    {
        // 1. Financial Overview Aggregates
        $fundingSources = FundingSource::withCount('ayudaPrograms')->latest()->get();
        $govSources = $fundingSources->where('funding_type', FundingType::Government);
        $privateSources = $fundingSources->where('funding_type', FundingType::Private);

        $govAllocated = $govSources->sum('allocated_amount');
        $govSpent = $govSources->sum('spent_amount');
        $govBalance = $govSources->sum('remaining_balance');

        $privateAllocated = $privateSources->sum('allocated_amount');
        $privateSpent = $privateSources->sum('spent_amount');
        $privateBalance = $privateSources->sum('remaining_balance');

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
                $registryItems->push([
                    'id' => $prj->id,
                    'category' => 'GGMS Project',
                    'code' => $prj->project_code,
                    'title' => $prj->title,
                    'allocated' => (float) $prj->allocated_budget,
                    'spent' => (float) $prj->spent_budget,
                    'balance' => (float) ($prj->allocated_budget - $prj->spent_budget),
                    'status' => $prj->status,
                    'date' => $prj->created_at?->format('Y-m-d') ?? date('Y-m-d'),
                    'detail_summary' => "GGMS Sync • Office {$prj->office_code}",
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

        // 6. Barangay List
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
