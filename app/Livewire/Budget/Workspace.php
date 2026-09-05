<?php

namespace App\Livewire\Budget;

use App\Enums\BenefitType;
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
use Illuminate\Support\Facades\Schema;
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
    // AYUDA PROJECT DETAILS DRAWER STATE
    // ==========================================
    public bool $showProjectDetailsDrawer = false;

    public ?int $selectedProjectDetailsId = null;

    public string $projectDetailsTab = 'overview'; // 'overview', 'beneficiaries', 'claims'

    // ==========================================
    // FUNDING SOURCE DETAILS DRAWER STATE
    // ==========================================
    public bool $showFundingDetailsDrawer = false;

    public ?int $selectedFundingDetailsId = null;

    public string $fundingDetailsTab = 'overview'; // 'overview', 'projects', 'ledger'

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

    public array $newProjectTargetBarangays = [];

    public string $newProjectStartDate = '';

    public string $newProjectEndDate = '';

    public string $newProjectDescription = '';

    public bool $autoEnrollAllBarangay = false;

    // Step 4: Beneficiary Selection & Review Pool
    public string $candidateSearch = '';

    public string $candidateBarangay = '';

    public array $selectedBeneficiaries = []; // Keyed by identifier

    // Step 4 Demographic & Eligibility Filters
    public ?int $candidateMinAge = null;

    public ?int $candidateMaxAge = null;

    public bool $candidateSeniorOnly = false;

    public bool $candidatePwdOnly = false;

    public bool $showCandidateFilterDrawer = false;

    // Step 4b: Household Review Modal State
    public bool $showHouseholdModal = false;

    public ?array $reviewingCandidate = null;

    public ?string $reviewingCandidateClaimsAlert = null; // null, 'moderate', 'high'

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

    public function openProjectDetails(int $id): void
    {
        $this->selectedProjectDetailsId = $id;
        $this->projectDetailsTab = 'overview';
        $this->showProjectDetailsDrawer = true;
    }

    public function closeProjectDetails(): void
    {
        $this->showProjectDetailsDrawer = false;
        $this->selectedProjectDetailsId = null;
        $this->projectDetailsTab = 'overview';
    }

    public function setProjectDetailsTab(string $tab): void
    {
        $this->projectDetailsTab = in_array($tab, ['overview', 'beneficiaries', 'claims'], true) ? $tab : 'overview';
    }

    public function openFundingDetails(int $id): void
    {
        $this->selectedFundingDetailsId = $id;
        $this->fundingDetailsTab = 'overview';
        $this->showFundingDetailsDrawer = true;
    }

    public function closeFundingDetails(): void
    {
        $this->showFundingDetailsDrawer = false;
        $this->selectedFundingDetailsId = null;
        $this->fundingDetailsTab = 'overview';
    }

    public function setFundingDetailsTab(string $tab): void
    {
        $this->fundingDetailsTab = in_array($tab, ['overview', 'projects', 'ledger'], true) ? $tab : 'overview';
    }

    public function toggleTargetBarangay(string $brgy): void
    {
        if (in_array($brgy, $this->newProjectTargetBarangays, true)) {
            $this->newProjectTargetBarangays = array_values(array_diff($this->newProjectTargetBarangays, [$brgy]));
        } else {
            $this->newProjectTargetBarangays[] = $brgy;
        }
        $this->newProjectTargetBarangay = ! empty($this->newProjectTargetBarangays) ? implode(', ', $this->newProjectTargetBarangays) : '';
    }

    public function selectAllBarangays(): void
    {
        $cacheService = app(PerformanceCacheService::class);
        $this->newProjectTargetBarangays = $cacheService->getBarangays();
        $this->newProjectTargetBarangay = 'Municipality-Wide';
    }

    public function clearTargetBarangays(): void
    {
        $this->newProjectTargetBarangays = [];
        $this->newProjectTargetBarangay = '';
    }

    public function toggleAllBarangays(): void
    {
        $cacheService = app(PerformanceCacheService::class);
        $all = $cacheService->getBarangays();

        if (count($this->newProjectTargetBarangays) === count($all)) {
            $this->newProjectTargetBarangays = [];
            $this->newProjectTargetBarangay = '';
        } else {
            $this->newProjectTargetBarangays = $all;
            $this->newProjectTargetBarangay = 'Municipality-Wide';
        }
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

    #[Computed]
    public function calculatedTotalGoodsQty(): int
    {
        $qty = max(1, (int) ($this->newProjectItemQty ?: 1));
        $count = max(0, (int) ($this->newProjectTargetCount ?: 0));

        return $qty * $count;
    }

    #[Computed]
    public function sourceAvailableGoodsStock(): ?int
    {
        $source = $this->selectedFundingSource;
        if (! $source) {
            return null;
        }

        $totalDonated = (int) $source->goodsDonations()->sum('quantity');
        if ($totalDonated <= 0) {
            return null;
        }

        $alreadyAllocated = (int) $source->ayudaPrograms()
            ->where('benefit_type', BenefitType::Goods)
            ->get()
            ->sum(fn ($p) => (int) $p->target_beneficiaries * (int) ($p->item_quantity_per_beneficiary ?: 1));

        return max(0, $totalDonated - $alreadyAllocated);
    }

    public function getSelectedFundingSourceProperty(): ?FundingSource
    {
        return $this->selectedFundingSource;
    }

    public function getCalculatedTotalCostProperty(): float
    {
        return $this->calculatedTotalCost;
    }

    public function getCalculatedTotalGoodsQtyProperty(): int
    {
        return $this->calculatedTotalGoodsQty;
    }

    public function getSourceAvailableGoodsStockProperty(): ?int
    {
        return $this->sourceAvailableGoodsStock;
    }

    public function updatedNewProjectBenefitType(string $val): void
    {
        if ($val === 'Goods') {
            $this->newProjectUnitAmount = '0';
            $this->newProjectItemQty = '1';
            if (empty($this->newProjectItemUnit)) {
                $this->newProjectItemUnit = 'Sacks';
            }
            $source = $this->selectedFundingSource;
            $this->newProjectBudgetCap = (string) (float) ($source?->remaining_balance ?? 0);
            $this->validateBudgetCapRealtime();
        } else {
            $this->newProjectUnitAmount = '5000';
            $this->newProjectItemName = '';
            $this->validateBudgetCapRealtime();
        }
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
        unset($this->selectedFundingSource, $this->sourceAvailableGoodsStock);
        if ($this->newProjectBenefitType === 'Goods') {
            $source = $this->selectedFundingSource;
            $this->newProjectBudgetCap = (string) (float) ($source?->remaining_balance ?? 0);
        }
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectBudgetCap(): void
    {
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectUnitAmount(): void
    {
        unset($this->calculatedTotalCost);
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectTargetCount(): void
    {
        unset($this->calculatedTotalCost, $this->calculatedTotalGoodsQty);
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectItemQty(): void
    {
        unset($this->calculatedTotalGoodsQty);
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectItemName(): void
    {
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectItemUnit(): void
    {
        $this->validateBudgetCapRealtime();
    }

    public function updatedNewProjectTargetBarangay(?string $value): void
    {
        $this->candidateBarangay = $value ?: '';
    }

    public function updatedCandidateBarangay(?string $value): void
    {
        if (empty($this->newProjectTargetBarangay)) {
            $this->newProjectTargetBarangay = $value ?: '';
        }
    }

    protected function validateBudgetCapRealtime(): void
    {
        $source = $this->newProjectFundingSourceId ? FundingSource::find($this->newProjectFundingSourceId) : null;
        if (! $source) {
            return;
        }

        if ($this->newProjectBenefitType === 'Goods') {
            $this->resetErrorBag(['newProjectBudgetCap', 'newProjectItemQty', 'newProjectTargetCount']);

            $totalDonated = (int) $source->goodsDonations()->sum('quantity');
            if ($totalDonated > 0) {
                $alreadyAllocated = (int) $source->ayudaPrograms()
                    ->where('benefit_type', BenefitType::Goods)
                    ->get()
                    ->sum(fn ($p) => (int) $p->target_beneficiaries * (int) ($p->item_quantity_per_beneficiary ?: 1));
                $availableStock = max(0, $totalDonated - $alreadyAllocated);

                $qty = max(1, (int) ($this->newProjectItemQty ?: 1));
                $count = max(0, (int) ($this->newProjectTargetCount ?: 0));
                $totalRequired = $qty * $count;

                if ($totalRequired > $availableStock) {
                    $unit = $this->newProjectItemUnit ?: 'units';
                    $this->addError(
                        'newProjectTargetCount',
                        "Required items ({$totalRequired} {$unit}) exceeds available stock in {$source->source_code} ({$availableStock} {$unit} remaining)."
                    );
                    $this->addError(
                        'newProjectItemQty',
                        "Total goods allocation exceeds available inventory stock of {$availableStock} {$unit}."
                    );
                }
            }

            return;
        }

        $this->resetErrorBag(['newProjectBudgetCap']);

        $maxBalance = (float) $source->remaining_balance;
        $entered = (float) ($this->newProjectBudgetCap ?: 0);

        if ($this->newProjectBudgetCap !== '' && $entered > $maxBalance) {
            $this->addError(
                'newProjectBudgetCap',
                'Budget Cap (₱'.number_format($entered, 2).') exceeds the selected funding source available balance of ₱'.number_format($maxBalance, 2).'.'
            );
        }
    }

    public function resetCandidateFilters(): void
    {
        $this->candidateMinAge = null;
        $this->candidateMaxAge = null;
        $this->candidateSeniorOnly = false;
        $this->candidatePwdOnly = false;
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
        $this->newProjectTargetBarangays = [];
        $this->wizardStep = 1;
        $this->selectedBeneficiaries = [];
        $this->candidateSearch = '';
        $this->candidateBarangay = '';
        $this->resetCandidateFilters();
        $this->showCandidateFilterDrawer = false;
        $this->reviewingCandidateClaimsAlert = null;
        $this->resetErrorBag();
        $this->showProjectModal = true;
    }

    public function closeProjectModal(): void
    {
        $this->showProjectModal = false;
        $this->showHouseholdModal = false;
        $this->wizardStep = 1;
        $this->selectedBeneficiaries = [];
        $this->newProjectFundingSourceId = null;
        $this->newProjectTitle = '';
        $this->newProjectBenefitType = 'Cash';
        $this->newProjectBudgetCap = '';
        $this->newProjectUnitAmount = '5000';
        $this->newProjectItemName = '';
        $this->newProjectItemUnit = 'Sacks';
        $this->newProjectItemQty = '1';
        $this->newProjectTargetCount = '50';
        $this->newProjectTargetBarangay = '';
        $this->newProjectTargetBarangays = [];
        $this->newProjectStartDate = '';
        $this->newProjectEndDate = '';
        $this->newProjectDescription = '';
        $this->candidateSearch = '';
        $this->candidateBarangay = '';
        $this->resetCandidateFilters();
        $this->showCandidateFilterDrawer = false;
        $this->reviewingCandidate = null;
        $this->reviewingCandidateClaimsAlert = null;
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
            if ($this->newProjectBenefitType === 'Goods') {
                $this->validate([
                    'newProjectTitle' => 'required|string|min:3',
                    'newProjectItemName' => 'required|string|min:2',
                    'newProjectItemUnit' => 'required|string|min:1',
                    'newProjectItemQty' => 'required|integer|min:1',
                    'newProjectTargetCount' => 'required|integer|min:1',
                ], [
                    'newProjectItemName.required' => 'Please enter the name of the goods or supplies item.',
                    'newProjectItemUnit.required' => 'Please specify the unit (e.g. Sacks, Boxes, Packs, Kits).',
                    'newProjectItemQty.required' => 'Please enter the item quantity per recipient.',
                ]);
                if (empty($this->newProjectBudgetCap)) {
                    $source = FundingSource::find($this->newProjectFundingSourceId);
                    $this->newProjectBudgetCap = (string) (float) ($source?->remaining_balance ?? 0);
                }
            } else {
                $source = FundingSource::find($this->newProjectFundingSourceId);
                $maxBalance = $source ? (float) $source->remaining_balance : 0.00;
                $this->validate([
                    'newProjectTitle' => 'required|string|min:3',
                    'newProjectBudgetCap' => "required|numeric|min:1|max:{$maxBalance}",
                ], [
                    'newProjectBudgetCap.max' => 'Budget Cap cannot exceed the remaining balance of the selected funding source (₱'.number_format($maxBalance, 2).').',
                ]);
            }

            $this->validateBudgetCapRealtime();
            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }
        }
        if ($step === 4) {
            $this->candidateBarangay = '';
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
            if ($this->newProjectBenefitType === 'Goods') {
                $this->validate([
                    'newProjectTitle' => 'required|string|min:3',
                    'newProjectItemName' => 'required|string|min:2',
                    'newProjectItemUnit' => 'required|string|min:1',
                    'newProjectItemQty' => 'required|integer|min:1',
                    'newProjectTargetCount' => 'required|integer|min:1',
                ], [
                    'newProjectItemName.required' => 'Please enter the name of the goods or supplies item.',
                    'newProjectItemUnit.required' => 'Please specify the unit (e.g. Sacks, Boxes, Packs, Kits).',
                    'newProjectItemQty.required' => 'Please enter the item quantity per recipient.',
                ]);
                if (empty($this->newProjectBudgetCap)) {
                    $source = FundingSource::find($this->newProjectFundingSourceId);
                    $this->newProjectBudgetCap = (string) (float) ($source?->remaining_balance ?? 0);
                }
            } else {
                $source = FundingSource::find($this->newProjectFundingSourceId);
                $maxBalance = $source ? (float) $source->remaining_balance : 0.00;
                $this->validate([
                    'newProjectTitle' => 'required|string|min:3',
                    'newProjectBudgetCap' => "required|numeric|min:1|max:{$maxBalance}",
                ], [
                    'newProjectBudgetCap.max' => 'Budget Cap cannot exceed the remaining balance of the selected funding source (₱'.number_format($maxBalance, 2).').',
                ]);
            }

            $this->validateBudgetCapRealtime();
            if ($this->getErrorBag()->isNotEmpty()) {
                return;
            }

            $this->wizardStep = 3;
        } elseif ($this->wizardStep === 3) {
            $this->candidateBarangay = '';
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

        $candCrn = (string) $this->reviewingCandidate['civil_registry_id'];
        $candId = (int) $candidate->id;
        $candidateClaims = AyudaProjectClaim::where(function ($q) use ($candCrn, $candId) {
            $q->where('civil_registry_id', $candCrn)
                ->orWhere('beneficiary_id', $candId);
        })->count();

        $this->reviewingCandidate['claims_count'] = $candidateClaims;

        if ($candidateClaims >= 5) {
            $this->reviewingCandidateClaimsAlert = 'high';
        } elseif ($candidateClaims >= 3) {
            $this->reviewingCandidateClaimsAlert = 'moderate';
        } else {
            $this->reviewingCandidateClaimsAlert = null;
        }

        $this->showHouseholdModal = true;
    }

    public function closeHouseholdReview(): void
    {
        $this->showHouseholdModal = false;
        $this->reviewingCandidate = null;
        $this->reviewingCandidateClaimsAlert = null;
    }

    public function confirmAddCandidate(): void
    {
        if (! $this->reviewingCandidate) {
            return;
        }

        $key = (string) ($this->reviewingCandidate['civil_registry_id'] ?? $this->reviewingCandidate['id']);
        $this->selectedBeneficiaries[$key] = $this->reviewingCandidate;

        $this->showHouseholdModal = false;
        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'info', message: "Added {$this->reviewingCandidate['full_name']} to candidate roster.");
        $this->reviewingCandidate = null;
    }

    public function autoFillCandidatePool(): void
    {
        $targetCount = max(1, (int) ($this->newProjectTargetCount ?: 50));
        $alreadySelectedCount = count($this->selectedBeneficiaries);
        $needed = max(0, $targetCount - $alreadySelectedCount);

        if ($needed <= 0) {
            $this->dispatch('toast', type: 'info', message: "Target capacity of {$targetCount} beneficiaries has already been reached.");

            return;
        }

        $barangays = ! empty($this->newProjectTargetBarangays)
            ? $this->newProjectTargetBarangays
            : ($this->newProjectTargetBarangay && $this->newProjectTargetBarangay !== 'Municipality-Wide'
                ? array_map('trim', explode(',', $this->newProjectTargetBarangay))
                : []);

        $roster = $this->resolveIntelligentBeneficiaryRoster(
            selectedBarangays: $barangays,
            targetTotal: $targetCount,
            currentSelected: $this->selectedBeneficiaries,
            minAge: $this->candidateMinAge,
            maxAge: $this->candidateMaxAge,
            seniorOnly: $this->candidateSeniorOnly,
            pwdOnly: $this->candidatePwdOnly
        );

        $added = 0;
        foreach ($roster['added'] as $crn => $candidateData) {
            $this->selectedBeneficiaries[$crn] = $candidateData;
            $added++;
        }

        $highClaimsCount = $roster['moderate_high_claims_count'] ?? 0;
        $advisorySuffix = $highClaimsCount > 0
            ? " Note: {$highClaimsCount} citizen(s) have received 3–5+ past disbursements."
            : '';

        if ($added > 0) {
            $this->dispatch('play-audio-success');
            $brgyCount = count($roster['effective_barangays']);
            if ($added < $needed) {
                $this->dispatch(
                    'toast',
                    type: 'warning',
                    message: "Auto-filled {$added} citizen(s) across {$brgyCount} barangay(s). Only {$added} eligible unique household(s) were available (target: {$targetCount}).{$advisorySuffix}"
                );
            } else {
                $this->dispatch(
                    'toast',
                    type: 'info',
                    message: "Auto-filled {$added} citizen(s) across {$brgyCount} barangay(s) with 1 beneficiary per unique household.{$advisorySuffix}"
                );
            }
        } else {
            $this->dispatch('play-audio-error');
            $this->dispatch(
                'toast',
                type: 'warning',
                message: 'No additional eligible unique households found in the selected barangay(s).'
            );
        }
    }

    /**
     * Apply demographic filters to an Eloquent Beneficiary query.
     */
    protected function applyDemographicFiltersToQuery(
        $query,
        ?int $minAge = null,
        ?int $maxAge = null,
        bool $seniorOnly = false,
        bool $pwdOnly = false
    ): void {
        $conn = (new Beneficiary)->getConnectionName() ?: config('database.default');
        $tableName = (new Beneficiary)->getTable() ?: 'val_beneficiaries';

        $hasIsSenior = Schema::connection($conn)->hasColumn($tableName, 'is_senior');
        $hasIsPwd = Schema::connection($conn)->hasColumn($tableName, 'is_pwd');
        $hasAge = Schema::connection($conn)->hasColumn($tableName, 'age');
        $hasDob = Schema::connection($conn)->hasColumn($tableName, 'date_of_birth');
        $hasBirthDate = Schema::connection($conn)->hasColumn($tableName, 'birth_date');

        if ($seniorOnly) {
            $query->where(function ($q) use ($hasIsSenior, $hasAge, $hasDob, $hasBirthDate) {
                if ($hasIsSenior) {
                    $q->orWhere('is_senior', 1);
                }
                if ($hasAge) {
                    $q->orWhere('age', '>=', 60);
                }
                if ($hasDob) {
                    $q->orWhere('date_of_birth', '<=', now()->subYears(60)->toDateString());
                }
                if ($hasBirthDate) {
                    $q->orWhere('birth_date', '<=', now()->subYears(60)->toDateString());
                }
            });
        }

        if ($pwdOnly && $hasIsPwd) {
            $query->where('is_pwd', 1);
        }

        if ($minAge !== null && $minAge > 0 && ! $seniorOnly) {
            $cutoff = now()->subYears($minAge)->toDateString();
            $query->where(function ($q) use ($minAge, $cutoff, $hasAge, $hasDob, $hasBirthDate) {
                if ($hasAge) {
                    $q->orWhere('age', '>=', $minAge);
                }
                if ($hasDob) {
                    $q->orWhere('date_of_birth', '<=', $cutoff);
                }
                if ($hasBirthDate) {
                    $q->orWhere('birth_date', '<=', $cutoff);
                }
            });
        }

        if ($maxAge !== null && $maxAge > 0) {
            $cutoff = now()->subYears($maxAge + 1)->addDay()->toDateString();
            $query->where(function ($q) use ($maxAge, $cutoff, $hasAge, $hasDob, $hasBirthDate) {
                if ($hasAge) {
                    $q->orWhere('age', '<=', $maxAge);
                }
                if ($hasDob) {
                    $q->orWhere('date_of_birth', '>=', $cutoff);
                }
                if ($hasBirthDate) {
                    $q->orWhere('birth_date', '>=', $cutoff);
                }
            });
        }
    }

    /**
     * Intelligent Randomized Beneficiary Auto-Fill Engine
     *
     * @param  array<int, string>  $selectedBarangays
     * @param  array<string, array>  $currentSelected
     * @return array{
     *     added: array<string, array>,
     *     total_added: int,
     *     needed: int,
     *     target_total: int,
     *     effective_barangays: array<int, string>,
     *     quotas: array<string, int>,
     *     moderate_high_claims_count: int
     * }
     */
    protected function resolveIntelligentBeneficiaryRoster(
        array $selectedBarangays,
        int $targetTotal,
        array $currentSelected = [],
        ?int $minAge = null,
        ?int $maxAge = null,
        bool $seniorOnly = false,
        bool $pwdOnly = false
    ): array {
        $alreadySelectedCount = count($currentSelected);
        $neededCount = max(0, $targetTotal - $alreadySelectedCount);

        if ($neededCount <= 0) {
            return [
                'added' => [],
                'total_added' => 0,
                'needed' => 0,
                'target_total' => $targetTotal,
                'effective_barangays' => $selectedBarangays,
                'quotas' => [],
                'moderate_high_claims_count' => 0,
            ];
        }

        $allBarangays = app(PerformanceCacheService::class)->getBarangays();
        $effectiveBarangays = ! empty($selectedBarangays)
            ? array_values(array_unique($selectedBarangays))
            : $allBarangays;

        if (empty($effectiveBarangays)) {
            $effectiveBarangays = $allBarangays;
        }

        // Collect existing selected households and beneficiary IDs to strictly exclude
        $existingHouseholdKeys = [];
        $existingBenIds = [];

        foreach ($currentSelected as $b) {
            if (! empty($b['id'])) {
                $existingBenIds[(string) $b['id']] = true;
            }
            if (! empty($b['civil_registry_id'])) {
                $existingBenIds[(string) $b['civil_registry_id']] = true;
            }
            $hNo = $b['household_no'] ?? null;
            if ($hNo && $hNo !== 'N/A') {
                $existingHouseholdKeys["H-{$hNo}"] = true;
            }
        }

        // 1. Fetch eligible candidates per barangay
        $barangayPools = [];
        $isLargeSelection = count($effectiveBarangays) >= 10;

        if ($isLargeSelection) {
            $fetchLimit = max(600, $neededCount * 4);
            $query = Beneficiary::query();
            if (count($effectiveBarangays) < count($allBarangays)) {
                $query->where(function ($q) use ($effectiveBarangays) {
                    foreach ($effectiveBarangays as $i => $b) {
                        if ($i === 0) {
                            $q->where('address', 'like', "%{$b}%");
                        } else {
                            $q->orWhere('address', 'like', "%{$b}%");
                        }
                    }
                });
            }
            $this->applyDemographicFiltersToQuery($query, $minAge, $maxAge, $seniorOnly, $pwdOnly);
            $rawPool = $query->inRandomOrder()->take($fetchLimit)->get();

            foreach ($rawPool as $candidate) {
                $brgy = $candidate->barangay;
                if (! in_array($brgy, $effectiveBarangays, true)) {
                    foreach ($effectiveBarangays as $targetBrgy) {
                        if (str_contains($candidate->address, $targetBrgy)) {
                            $brgy = $targetBrgy;
                            break;
                        }
                    }
                }
                if (in_array($brgy, $effectiveBarangays, true)) {
                    $barangayPools[$brgy][] = $candidate;
                }
            }
        } else {
            $fetchPerBrgy = max(60, (int) ceil(($neededCount / max(1, count($effectiveBarangays))) * 3));
            foreach ($effectiveBarangays as $brgy) {
                $query = Beneficiary::where('address', 'like', "%{$brgy}%");
                $this->applyDemographicFiltersToQuery($query, $minAge, $maxAge, $seniorOnly, $pwdOnly);
                $rawPool = $query->inRandomOrder()->take($fetchPerBrgy)->get();
                $barangayPools[$brgy] = $rawPool->all();
            }
        }

        // 2. Group candidates by household within each barangay and pick 1 random member
        $availableByBarangay = [];
        $allChosenRepresentatives = [];

        foreach ($effectiveBarangays as $brgy) {
            $candidates = $barangayPools[$brgy] ?? [];
            $byHousehold = [];

            foreach ($candidates as $c) {
                // In-memory demographic filter check
                if ($seniorOnly && ! $c->is_senior) {
                    continue;
                }
                if ($pwdOnly && ! $c->is_pwd) {
                    continue;
                }
                if ($minAge !== null && ($c->age === null || $c->age < $minAge)) {
                    continue;
                }
                if ($maxAge !== null && ($c->age === null || $c->age > $maxAge)) {
                    continue;
                }

                $cId = (string) $c->id;
                $crn = (string) ($c->civil_registry_id ?: $c->civilregistry_id ?: $c->beneficiary_id ?: "CRN-{$c->id}");

                if (isset($existingBenIds[$cId]) || isset($existingBenIds[$crn])) {
                    continue;
                }

                $hNo = $c->household_no;
                if ($hNo && $hNo !== 'N/A' && isset($existingHouseholdKeys["H-{$hNo}"])) {
                    continue;
                }

                $hKey = ($hNo && $hNo !== 'N/A') ? "H-{$hNo}" : "IND-{$c->id}";
                $byHousehold[$hKey][] = $c;
            }

            $uniqueHouseholds = [];
            foreach ($byHousehold as $hKey => $members) {
                $chosen = $members[array_rand($members)];
                $chosenCrn = (string) ($chosen->civil_registry_id ?: $chosen->civilregistry_id ?: $chosen->beneficiary_id ?: "CRN-{$chosen->id}");
                $uniqueHouseholds[] = [
                    'candidate' => $chosen,
                    'household_key' => $hKey,
                    'crn' => $chosenCrn,
                ];
                $allChosenRepresentatives[] = $chosen;
            }

            $availableByBarangay[$brgy] = $uniqueHouseholds;
        }

        // Batch query claims for all chosen household members across all barangays to avoid N+1 queries
        $allCrns = array_map(fn ($c) => (string) ($c->civil_registry_id ?: $c->civilregistry_id ?: $c->beneficiary_id ?: "CRN-{$c->id}"), $allChosenRepresentatives);
        $claimCounts = [];
        if (! empty($allCrns)) {
            try {
                $claimCounts = AyudaProjectClaim::whereIn('civil_registry_id', $allCrns)
                    ->selectRaw('civil_registry_id, count(*) as cnt')
                    ->groupBy('civil_registry_id')
                    ->pluck('cnt', 'civil_registry_id')
                    ->all();
            } catch (\Throwable) {
                $claimCounts = [];
            }
        }

        // Prioritize candidates into 4 claim-history tiers within each barangay
        foreach ($effectiveBarangays as $brgy) {
            $households = $availableByBarangay[$brgy] ?? [];
            $tier1 = []; // 0 claims
            $tier2 = []; // 1–2 claims
            $tier3 = []; // 3–4 claims
            $tier4 = []; // 5+ claims

            foreach ($households as $item) {
                $cnt = (int) ($claimCounts[$item['crn']] ?? 0);
                $item['claims_count'] = $cnt;

                if ($cnt === 0) {
                    $tier1[] = $item;
                } elseif ($cnt <= 2) {
                    $tier2[] = $item;
                } elseif ($cnt <= 4) {
                    $tier3[] = $item;
                } else {
                    $tier4[] = $item;
                }
            }

            shuffle($tier1);
            shuffle($tier2);
            shuffle($tier3);
            shuffle($tier4);

            $availableByBarangay[$brgy] = array_merge($tier1, $tier2, $tier3, $tier4);
        }

        // 3. Fair water-filling quota allocation across selected barangays
        $allocatedQuotas = array_fill_keys($effectiveBarangays, 0);
        $availableCounts = array_map(fn ($list) => count($list), $availableByBarangay);

        $remainingNeeded = $neededCount;
        $activeBarangays = $effectiveBarangays;

        while ($remainingNeeded > 0 && ! empty($activeBarangays)) {
            $share = (int) ceil($remainingNeeded / count($activeBarangays));
            $progress = false;

            foreach ($activeBarangays as $idx => $brgy) {
                $canGive = $availableCounts[$brgy] - $allocatedQuotas[$brgy];
                if ($canGive <= 0) {
                    unset($activeBarangays[$idx]);

                    continue;
                }

                $take = min($canGive, min($share, $remainingNeeded));
                if ($take > 0) {
                    $allocatedQuotas[$brgy] += $take;
                    $remainingNeeded -= $take;
                    $progress = true;
                }

                if ($allocatedQuotas[$brgy] >= $availableCounts[$brgy]) {
                    unset($activeBarangays[$idx]);
                }

                if ($remainingNeeded <= 0) {
                    break;
                }
            }

            if (! $progress) {
                break;
            }
        }

        // 4. Assemble final roster with strict household uniqueness check
        $addedCandidates = [];
        $stagedHouseholdKeys = $existingHouseholdKeys;

        foreach ($effectiveBarangays as $brgy) {
            $quota = $allocatedQuotas[$brgy];
            $candidates = array_slice($availableByBarangay[$brgy], 0, $quota);

            foreach ($candidates as $item) {
                $c = $item['candidate'];
                $hKey = $item['household_key'];

                if (isset($stagedHouseholdKeys[$hKey])) {
                    continue;
                }
                $stagedHouseholdKeys[$hKey] = true;

                $crn = $c->civil_registry_id ?: $c->civilregistry_id ?: $c->beneficiary_id ?: "CRN-{$c->id}";
                $addedCandidates[$crn] = [
                    'id' => $c->id,
                    'civil_registry_id' => $crn,
                    'beneficiary_id' => $c->beneficiary_id ?: "BEN-{$c->id}",
                    'full_name' => $c->full_name ?: trim("{$c->first_name} {$c->last_name}"),
                    'household_no' => $c->household_no ?? 'N/A',
                    'barangay' => $brgy,
                    'address' => $c->address,
                    'sex' => $c->gender ?? $c->sex ?? 'N/A',
                    'birth_date' => $c->birthDate ?? $c->date_of_birth ?? 'N/A',
                    'age' => $c->age,
                    'is_senior' => $c->is_senior,
                    'is_pwd' => $c->is_pwd,
                    'claims_count' => $item['claims_count'] ?? 0,
                ];
            }
        }

        $moderateHighClaimsCount = 0;
        foreach ($addedCandidates as $item) {
            if (($item['claims_count'] ?? 0) >= 3) {
                $moderateHighClaimsCount++;
            }
        }

        return [
            'added' => $addedCandidates,
            'total_added' => count($addedCandidates),
            'needed' => $neededCount,
            'target_total' => $targetTotal,
            'effective_barangays' => $effectiveBarangays,
            'quotas' => $allocatedQuotas,
            'moderate_high_claims_count' => $moderateHighClaimsCount,
        ];
    }

    public function removeCandidate(int|string $key): void
    {
        unset($this->selectedBeneficiaries[(string) $key]);
        unset($this->selectedBeneficiaries[(int) $key]);

        foreach ($this->selectedBeneficiaries as $k => $item) {
            if ((isset($item['id']) && (string) $item['id'] === (string) $key) ||
                (isset($item['civil_registry_id']) && (string) $item['civil_registry_id'] === (string) $key)
            ) {
                unset($this->selectedBeneficiaries[$k]);
                break;
            }
        }
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

        $this->validateBudgetCapRealtime();
        if ($this->getErrorBag()->isNotEmpty()) {
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: 'Cannot create project: Allocation limits or available stock exceeded.');

            return;
        }

        if ($this->newProjectBenefitType === 'Goods') {
            $this->validate([
                'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
                'newProjectTitle' => 'required|string|min:3',
                'newProjectBenefitType' => 'required|in:Cash,Goods',
                'newProjectItemName' => 'required|string|min:2',
                'newProjectItemUnit' => 'required|string|min:1',
                'newProjectItemQty' => 'required|integer|min:1',
            ]);
            $this->newProjectBudgetCap = (string) min((float) ($this->newProjectBudgetCap ?: 0), $maxBalance);
        } else {
            $this->validate([
                'newProjectFundingSourceId' => 'required|exists:funding_sources,id',
                'newProjectTitle' => 'required|string|min:3',
                'newProjectBudgetCap' => "required|numeric|min:1|max:{$maxBalance}",
                'newProjectBenefitType' => 'required|in:Cash,Goods',
            ], [
                'newProjectBudgetCap.max' => 'Budget Cap cannot exceed the remaining balance of the selected funding source (₱'.number_format($maxBalance, 2).').',
            ]);
        }

        try {
            $program = null;
            $enrolledCount = 0;

            DB::transaction(function () use ($budgetService, &$program, &$enrolledCount) {
                $effectiveTargetBarangay = ! empty($this->newProjectTargetBarangays)
                    ? implode(', ', $this->newProjectTargetBarangays)
                    : ($this->newProjectTargetBarangay ?: ($this->candidateBarangay ?: 'Municipality-Wide'));

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
                    'target_barangay' => $effectiveTargetBarangay,
                    'start_date' => $this->newProjectStartDate ?: now()->toDateString(),
                    'end_date' => $this->newProjectEndDate ?: null,
                    'description' => $this->newProjectDescription ?: null,
                ]);

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
                    // 2. Intelligent Auto-enroll eligible citizens matching target count & target scope
                    $targetLimit = max(1, (int) ($this->newProjectTargetCount ?: 50));
                    $targetBarangays = ! empty($this->newProjectTargetBarangays)
                        ? $this->newProjectTargetBarangays
                        : ($this->newProjectTargetBarangay && $this->newProjectTargetBarangay !== 'Municipality-Wide'
                            ? array_map('trim', explode(',', $this->newProjectTargetBarangay))
                            : []);

                    $roster = $this->resolveIntelligentBeneficiaryRoster(
                        selectedBarangays: $targetBarangays,
                        targetTotal: $targetLimit,
                        currentSelected: [],
                        minAge: $this->candidateMinAge,
                        maxAge: $this->candidateMaxAge,
                        seniorOnly: $this->candidateSeniorOnly,
                        pwdOnly: $this->candidatePwdOnly
                    );

                    foreach ($roster['added'] as $b) {
                        DistributionEnrollment::firstOrCreate(
                            [
                                'ayuda_program_id' => $program->id,
                                'civil_registry_id' => $b['civil_registry_id'],
                            ],
                            [
                                'beneficiary_id' => $b['id'] ?? null,
                                'household_no' => $b['household_no'] ?? 'N/A',
                                'status' => DistributionStatus::PENDING,
                                'enrolled_at' => now(),
                            ]
                        );
                        $enrolledCount++;
                    }
                }
            });

            app(PerformanceCacheService::class)->clearFundingCache();

            $this->closeProjectModal();
            $this->activeTab = 'overview';
            $this->dispatch('play-audio-success');
            $this->dispatch('toast', type: 'success', message: "Created Ayuda Project {$program->program_code} with {$enrolledCount} enrolled beneficiaries.");
        } catch (\Throwable $e) {
            Log::error('Create project error: '.$e->getMessage());
            $this->dispatch('play-audio-error');
            $this->dispatch('toast', type: 'error', message: $e->getMessage());

            return;
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
                } elseif (! empty($this->newProjectTargetBarangays)) {
                    $candidateQ->where(function ($q) {
                        foreach ($this->newProjectTargetBarangays as $index => $brgy) {
                            if ($index === 0) {
                                $q->where('address', 'like', "%{$brgy}%");
                            } else {
                                $q->orWhere('address', 'like', "%{$brgy}%");
                            }
                        }
                    });
                }

                $this->applyDemographicFiltersToQuery(
                    $candidateQ,
                    $this->candidateMinAge,
                    $this->candidateMaxAge,
                    $this->candidateSeniorOnly,
                    $this->candidatePwdOnly
                );

                $totalCandidatesCount = $candidateQ->count();
                $candidates = $candidateQ->take(200)->get();

                // Batch-load claims counts for candidate cards to prevent N+1 queries
                $candCrns = $candidates->map(fn ($c) => (string) ($c->civil_registry_id ?: $c->civilregistry_id ?: $c->beneficiary_id ?: "CRN-{$c->id}"))->all();
                $claimsMap = [];
                if (! empty($candCrns)) {
                    try {
                        $claimsMap = AyudaProjectClaim::whereIn('civil_registry_id', $candCrns)
                            ->selectRaw('civil_registry_id, count(*) as cnt')
                            ->groupBy('civil_registry_id')
                            ->pluck('cnt', 'civil_registry_id')
                            ->all();
                    } catch (\Throwable) {
                        $claimsMap = [];
                    }
                }

                foreach ($candidates as $c) {
                    $crn = (string) ($c->civil_registry_id ?: $c->civilregistry_id ?: $c->beneficiary_id ?: "CRN-{$c->id}");
                    $c->claims_count = (int) ($claimsMap[$crn] ?? 0);
                }
            } catch (\Throwable) {
                $candidates = collect();
                $totalCandidatesCount = 0;
            }
        }

        // 8. Detailed Project for Inspector Drawer
        $detailedProject = null;
        if ($this->showProjectDetailsDrawer && $this->selectedProjectDetailsId) {
            $detailedProject = AyudaProgram::with([
                'fundingSource',
                'creator',
                'enrollments.beneficiary',
                'claims.beneficiary',
                'claims.releasingOfficer',
            ])->find($this->selectedProjectDetailsId);
        }

        // 9. Detailed Funding Source for Inspector Drawer
        $detailedFunding = null;
        if ($this->showFundingDetailsDrawer && $this->selectedFundingDetailsId) {
            $detailedFunding = FundingSource::with([
                'ayudaPrograms.claims',
                'donations',
                'goodsDonations',
                'budgetLedgerEntries.creator',
            ])->find($this->selectedFundingDetailsId);
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
            'detailedProject' => $detailedProject,
            'detailedFunding' => $detailedFunding,
        ]);
    }
}
