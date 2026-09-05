<?php

namespace Tests\Feature;

use App\Enums\DistributionStatus;
use App\Enums\FundingType;
use App\Enums\LedgerEntryType;
use App\Enums\ProgramStatus;
use App\Livewire\Budget\Workspace;
use App\Models\AyudaProgram;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\User;
use App\Services\BudgetLedgerService;
use App\Services\PerformanceCacheService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BudgetManagementTest extends TestCase
{
    use RefreshDatabase;

    protected BudgetLedgerService $budgetService;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->budgetService = app(BudgetLedgerService::class);
        $this->admin = User::factory()->create(['role' => 'Admin']);
    }

    public function test_private_cash_donation_writes_immutable_ledger_entry(): void
    {
        $donation = $this->budgetService->recordCashDonation([
            'donor_name' => 'Sulop Philanthropist Group',
            'amount' => 50000.00,
            'notes' => 'Donation for flood relief',
        ], $this->admin->id);

        $this->assertDatabaseHas('donations', [
            'donor_name' => 'Sulop Philanthropist Group',
            'amount' => 50000.00,
        ]);

        $this->assertDatabaseHas('budget_ledger_entries', [
            'entry_type' => LedgerEntryType::Donation->value,
            'amount' => 50000.00,
            'new_balance' => 50000.00,
        ]);
    }

    public function test_cannot_create_project_exceeding_funding_source_balance(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Test Grant',
            'source_code' => 'GRANT-001',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 100000.00,
        ]);

        $this->expectException(Exception::class);

        $this->budgetService->createAyudaProgram([
            'funding_source_id' => $funding->id,
            'title' => 'Over-budget Project',
            'budget_cap' => 150000.00, // Exceeds 100,000
        ], $this->admin->id);
    }

    public function test_reallocate_earmark_reclaims_unspent_funds(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Test Grant',
            'source_code' => 'GRANT-002',
            'allocated_amount' => 500000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 500000.00,
        ]);

        $program = $this->budgetService->createAyudaProgram([
            'funding_source_id' => $funding->id,
            'title' => 'Project to Reallocate',
            'budget_cap' => 200000.00,
            'unit_amount' => 5000.00,
        ], $this->admin->id);

        // Disburse 50,000
        $this->budgetService->recordRelease($program, 50000.00, 'TEST-CLM-01', null, 1, null, $this->admin->id);

        $this->assertEquals(50000.00, (float) $program->fresh()->total_disbursed_amount);
        $this->assertEquals(300000.00, (float) $funding->fresh()->remaining_balance);

        // Reallocate remaining 150,000 unspent earmark
        $this->budgetService->reallocateEarmark($program, $this->admin->id);

        $this->assertEquals(450000.00, (float) $funding->fresh()->remaining_balance); // 300k + 150k unspent
        $this->assertEquals(ProgramStatus::Completed, $program->fresh()->status);

        $this->assertDatabaseHas('budget_ledger_entries', [
            'ayuda_program_id' => $program->id,
            'entry_type' => LedgerEntryType::Reallocation->value,
            'amount' => 150000.00,
        ]);
    }

    public function test_unified_registry_tab_renders_and_filters(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'LGU Grant Pool',
            'source_code' => 'GOV-REG-01',
            'allocated_amount' => 500000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 500000.00,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->set('activeTab', 'registry')
            ->assertSee('Unified Project & Funding Registry', false)
            ->assertSee('GOV-REG-01')
            ->set('registryCategory', 'GOV_FUND')
            ->assertSee('Government Fund');
    }

    public function test_candidate_household_review_and_enrollment_wizard(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Emergency Aid Pool',
            'source_code' => 'GOV-EMERG-01',
            'allocated_amount' => 500000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 500000.00,
        ]);

        $candidate = Beneficiary::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'full_name' => 'Santos, Maria',
            'civil_registry_id' => 'CRN-TEST-100',
            'household_no' => 'HH-100',
            'barangay' => 'Poblacion',
            'address' => 'Purok 1, Poblacion, Sulop',
            'IsDeleted' => 0,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectTitle', 'Typhoon Relief 2026')
            ->set('newProjectBudgetCap', '50000.00')
            ->set('newProjectUnitAmount', '2500.00')
            ->call('goToStep', 4)
            ->call('openHouseholdReview', $candidate->id)
            ->assertSet('showHouseholdModal', true)
            ->assertSet('reviewingCandidate.full_name', 'Santos, Maria')
            ->call('confirmAddCandidate')
            ->assertCount('selectedBeneficiaries', 1)
            ->call('createProject')
            ->assertSet('showProjectModal', false);

        $this->assertDatabaseHas('ayuda_programs', [
            'title' => 'Typhoon Relief 2026',
            'budget_cap' => 50000.00,
        ]);

        $this->assertDatabaseHas('distribution_enrollments', [
            'civil_registry_id' => 'CRN-TEST-100',
            'household_no' => 'HH-100',
            'status' => DistributionStatus::PENDING->value,
        ]);
    }

    public function test_project_wizard_prevents_advancing_when_budget_cap_exceeds_funding_source(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'GGMS Municipal Government General Fund',
            'source_code' => 'GGMS-OFF-2026-0006',
            'allocated_amount' => 70000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 70000.00,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->call('nextStep')
            ->assertSet('wizardStep', 2)
            ->set('newProjectTitle', 'Barangay Aid')
            ->set('newProjectBudgetCap', '100000.00') // Exceeds 70k
            ->assertHasErrors(['newProjectBudgetCap'])
            ->call('nextStep')
            ->assertSet('wizardStep', 2) // Remains on step 2 due to validation
            ->call('setBudgetCapToMax')
            ->assertSet('newProjectBudgetCap', '70000')
            ->assertHasNoErrors(['newProjectBudgetCap'])
            ->call('nextStep')
            ->assertSet('wizardStep', 3);
    }

    public function test_create_project_using_70k_office_allocation_source(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'GGMS Municipal Government General Fund',
            'source_code' => 'GGMS-OFF-2026-0006',
            'office' => 'Municipal Office',
            'fiscal_year' => 2026,
            'allocated_amount' => 70000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 70000.00,
            'status' => 'Active',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Cash')
            ->call('nextStep')
            ->set('newProjectTitle', 'Sulop Elderly Aid')
            ->set('newProjectBudgetCap', '50000.00')
            ->set('newProjectUnitAmount', '2000.00')
            ->set('newProjectTargetCount', 25)
            ->call('nextStep')
            ->assertSet('wizardStep', 3)
            ->call('createProject')
            ->assertSet('showProjectModal', false);

        $this->assertDatabaseHas('ayuda_programs', [
            'title' => 'Sulop Elderly Aid',
            'budget_cap' => 50000.00,
            'funding_source_id' => $funding->id,
        ]);

        $this->assertEquals(20000.00, (float) $funding->fresh()->remaining_balance);
    }

    public function test_create_in_kind_goods_project_using_dedicated_inventory_fields(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Private,
            'title' => 'Private In-Kind & Goods Donations Pool 2026',
            'source_code' => 'DON-GOODS-2026',
            'office' => 'MSWDO / Warehouse',
            'fiscal_year' => 2026,
            'allocated_amount' => 150000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 150000.00,
            'status' => 'Active',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Goods')
            ->call('nextStep')
            ->assertSet('wizardStep', 2)
            ->assertHasNoErrors(['newProjectBudgetCap'])
            ->set('newProjectTitle', 'Typhoon Relief Rice Distribution')
            ->set('newProjectItemName', 'Premium Well-Milled Rice (25kg)')
            ->set('newProjectItemUnit', 'Sacks')
            ->set('newProjectItemQty', 1)
            ->set('newProjectTargetCount', 100)
            ->assertSet('calculatedTotalGoodsQty', 100)
            ->call('nextStep')
            ->assertSet('wizardStep', 3)
            ->call('createProject')
            ->assertSet('showProjectModal', false);

        $this->assertDatabaseHas('ayuda_programs', [
            'title' => 'Typhoon Relief Rice Distribution',
            'benefit_type' => 'Goods',
            'item_name' => 'Premium Well-Milled Rice (25kg)',
            'item_unit' => 'Sacks',
            'item_quantity_per_beneficiary' => 1,
            'target_beneficiaries' => 100,
            'funding_source_id' => $funding->id,
        ]);
    }

    public function test_open_and_close_ayuda_project_details_drawer(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Municipal Calamity Fund 2026',
            'source_code' => 'MCF-2026-0001',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 100000.00,
            'status' => 'Active',
        ]);

        $program = AyudaProgram::create([
            'funding_source_id' => $funding->id,
            'program_code' => 'AMS-PD-000099',
            'title' => 'Senior Citizen Cash Payout',
            'benefit_type' => 'Cash',
            'budget_cap' => 30000.00,
            'unit_amount' => 1500.00,
            'target_beneficiaries' => 20,
            'status' => 'Active',
            'created_by' => $this->admin->id,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->assertSet('showProjectDetailsDrawer', false)
            ->call('openProjectDetails', $program->id)
            ->assertSet('showProjectDetailsDrawer', true)
            ->assertSet('selectedProjectDetailsId', $program->id)
            ->assertSet('projectDetailsTab', 'overview')
            ->assertSee('Senior Citizen Cash Payout')
            ->assertSee('AMS-PD-000099')
            ->call('setProjectDetailsTab', 'beneficiaries')
            ->assertSet('projectDetailsTab', 'beneficiaries')
            ->call('closeProjectDetails')
            ->assertSet('showProjectDetailsDrawer', false)
            ->assertSet('selectedProjectDetailsId', null);
    }

    public function test_open_and_close_funding_source_details_drawer(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Municipal Social Welfare Fund 2026',
            'source_code' => 'MSWF-2026-0001',
            'office' => 'MSWDO',
            'fiscal_year' => 2026,
            'allocated_amount' => 500000.00,
            'spent_amount' => 100000.00,
            'remaining_balance' => 400000.00,
            'status' => 'Active',
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->assertSet('showFundingDetailsDrawer', false)
            ->call('openFundingDetails', $funding->id)
            ->assertSet('showFundingDetailsDrawer', true)
            ->assertSet('selectedFundingDetailsId', $funding->id)
            ->assertSet('fundingDetailsTab', 'overview')
            ->assertSee('MSWF-2026-0001')
            ->assertSee('Municipal Social Welfare Fund 2026')
            ->call('setFundingDetailsTab', 'projects')
            ->assertSet('fundingDetailsTab', 'projects')
            ->call('setFundingDetailsTab', 'ledger')
            ->assertSet('fundingDetailsTab', 'ledger')
            ->call('closeFundingDetails')
            ->assertSet('showFundingDetailsDrawer', false)
            ->assertSet('selectedFundingDetailsId', null);
    }

    public function test_toggle_and_select_all_target_barangays(): void
    {
        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->assertSet('newProjectTargetBarangays', [])
            ->call('toggleTargetBarangay', 'Poblacion')
            ->assertSet('newProjectTargetBarangays', ['Poblacion'])
            ->call('toggleTargetBarangay', 'Tagaca')
            ->assertSet('newProjectTargetBarangays', ['Poblacion', 'Tagaca'])
            ->call('toggleTargetBarangay', 'Poblacion') // Untoggle Poblacion
            ->assertSet('newProjectTargetBarangays', ['Tagaca'])
            ->call('selectAllBarangays');

        $allBarangays = app(PerformanceCacheService::class)->getBarangays();
        $this->assertEquals($allBarangays, $test->get('newProjectTargetBarangays'));

        $test->call('clearTargetBarangays')
            ->assertSet('newProjectTargetBarangays', []);
    }

    public function test_create_project_with_multiple_target_barangays_and_auto_enrollment(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Emergency Aid Envelope',
            'source_code' => 'EMERG-2026-001',
            'allocated_amount' => 200000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 200000.00,
            'status' => 'Active',
        ]);

        Beneficiary::create([
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'full_name' => 'Dela Cruz, Juan',
            'civil_registry_id' => 'CRN-TEST-BRGY-1',
            'household_no' => 'HH-BRGY-1',
            'address' => 'Purok 1, Tagaca, Sulop',
            'barangay' => 'Tagaca',
            'IsDeleted' => 0,
        ]);

        Beneficiary::create([
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'full_name' => 'Santos, Maria',
            'civil_registry_id' => 'CRN-TEST-BRGY-2',
            'household_no' => 'HH-BRGY-2',
            'address' => 'Purok 3, Poblacion, Sulop',
            'barangay' => 'Poblacion',
            'IsDeleted' => 0,
        ]);

        Beneficiary::create([
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'full_name' => 'Penduko, Pedro',
            'civil_registry_id' => 'CRN-TEST-BRGY-3',
            'household_no' => 'HH-BRGY-3',
            'address' => 'Purok 5, Labon, Sulop',
            'barangay' => 'Labon',
            'IsDeleted' => 0,
        ]);

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Cash')
            ->call('nextStep')
            ->assertSet('wizardStep', 2)
            ->set('newProjectTitle', 'Multi-Barangay Livelihood Aid')
            ->set('newProjectBudgetCap', 10000.00)
            ->set('newProjectUnitAmount', 5000.00)
            ->set('newProjectTargetCount', 2)
            ->call('toggleTargetBarangay', 'Tagaca')
            ->call('toggleTargetBarangay', 'Poblacion')
            ->call('nextStep')
            ->assertSet('wizardStep', 3)
            ->call('createProject')
            ->assertSet('showProjectModal', false);

        $program = AyudaProgram::where('title', 'Multi-Barangay Livelihood Aid')->first();
        $this->assertNotNull($program);
        $this->assertEquals('Tagaca, Poblacion', $program->target_barangay);

        // Check enrolled beneficiaries match the chosen barangays
        $enrollments = $program->enrollments()->with('beneficiary')->get();
        $this->assertCount(2, $enrollments);
        foreach ($enrollments as $enrollment) {
            $this->assertTrue(
                str_contains($enrollment->beneficiary->address, 'Tagaca') ||
                str_contains($enrollment->beneficiary->address, 'Poblacion')
            );
        }
    }

    public function test_goods_project_prevents_exceeding_inventory_stock_allocation(): void
    {
        $goodsDonation = $this->budgetService->recordGoodsDonation([
            'donor_name' => 'Red Cross Davao',
            'item_name' => 'Rice 25kg',
            'quantity' => 100,
            'unit' => 'Sacks',
            'estimated_value' => 50000.00,
        ]);
        $funding = $goodsDonation->fundingSource;

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Goods')
            ->call('nextStep')
            ->assertSet('wizardStep', 2)
            ->set('newProjectTitle', 'Barangay Rice Relief')
            ->set('newProjectItemName', 'Rice 25kg')
            ->set('newProjectItemUnit', 'Sacks')
            ->set('newProjectItemQty', 2)
            ->set('newProjectTargetCount', 60) // 2 * 60 = 120 > 100 available
            ->assertHasErrors(['newProjectTargetCount', 'newProjectItemQty'])
            ->call('nextStep')
            ->assertSet('wizardStep', 2) // Blocked on step 2
            ->set('newProjectItemQty', 1) // 1 * 60 = 60 <= 100 available
            ->assertHasNoErrors(['newProjectTargetCount', 'newProjectItemQty'])
            ->call('nextStep')
            ->assertSet('wizardStep', 3);
    }

    public function test_step_4_candidate_pool_listens_to_picked_barangay_without_crashing(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Emergency Relief 2026',
            'source_code' => 'EMERG-2026-002',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 100000.00,
        ]);

        Beneficiary::create([
            'first_name' => 'Pedro',
            'last_name' => 'LaplaCitizen',
            'full_name' => 'LaplaCitizen, Pedro',
            'civil_registry_id' => 'CRN-LAPLA-1',
            'household_no' => 'HH-LAPLA-1',
            'address' => 'Purok 2, Lapla, Sulop',
            'barangay' => 'Lapla',
            'IsDeleted' => 0,
        ]);

        Beneficiary::create([
            'first_name' => 'Ana',
            'last_name' => 'TagacaCitizen',
            'full_name' => 'TagacaCitizen, Ana',
            'civil_registry_id' => 'CRN-TAGACA-1',
            'household_no' => 'HH-TAGACA-1',
            'address' => 'Purok 4, Tagaca, Sulop',
            'barangay' => 'Tagaca',
            'IsDeleted' => 0,
        ]);

        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Cash')
            ->call('nextStep')
            ->set('newProjectTitle', 'Lapla Focused Ayuda')
            ->set('newProjectBudgetCap', '50000.00')
            ->set('newProjectUnitAmount', '1000.00')
            ->set('newProjectTargetCount', 10)
            ->call('toggleTargetBarangay', 'Lapla')
            ->call('nextStep') // to step 3
            ->assertSet('wizardStep', 3)
            ->call('nextStep') // to step 4
            ->assertSet('wizardStep', 4)
            ->assertSet('candidateBarangay', '')
            ->call('autoFillCandidatePool');

        $selected = $test->get('selectedBeneficiaries') ?? [];
        $this->assertCount(1, $selected);
        $this->assertEquals('CRN-LAPLA-1', array_key_first($selected));
    }

    public function test_toggle_all_barangays_choice_in_picker(): void
    {
        $all = app(PerformanceCacheService::class)->getBarangays();

        Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->assertSet('newProjectTargetBarangays', [])
            ->call('toggleAllBarangays')
            ->assertSet('newProjectTargetBarangays', $all)
            ->assertSet('newProjectTargetBarangay', 'Municipality-Wide')
            ->call('toggleAllBarangays')
            ->assertSet('newProjectTargetBarangays', [])
            ->assertSet('newProjectTargetBarangay', '');
    }

    public function test_autofill_distributes_across_multiple_selected_barangays(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Multi-Barangay Relief 2026',
            'source_code' => 'EMERG-MULTI-01',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 100000.00,
        ]);

        for ($i = 1; $i <= 4; $i++) {
            Beneficiary::create([
                'first_name' => "LaplaPerson{$i}",
                'last_name' => 'Citizen',
                'full_name' => "Citizen, LaplaPerson{$i}",
                'civil_registry_id' => "CRN-LAPLA-MB-{$i}",
                'household_no' => "HH-LAPLA-MB-{$i}",
                'address' => "Purok {$i}, Lapla, Sulop",
                'barangay' => 'Lapla',
                'IsDeleted' => 0,
            ]);

            Beneficiary::create([
                'first_name' => "TagacaPerson{$i}",
                'last_name' => 'Citizen',
                'full_name' => "Citizen, TagacaPerson{$i}",
                'civil_registry_id' => "CRN-TAGACA-MB-{$i}",
                'household_no' => "HH-TAGACA-MB-{$i}",
                'address' => "Purok {$i}, Tagaca, Sulop",
                'barangay' => 'Tagaca',
                'IsDeleted' => 0,
            ]);
        }

        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Cash')
            ->set('newProjectTitle', 'Multi-Barangay Distribution')
            ->set('newProjectBudgetCap', '50000.00')
            ->set('newProjectUnitAmount', '1000.00')
            ->set('newProjectTargetCount', 4)
            ->call('toggleTargetBarangay', 'Lapla')
            ->call('toggleTargetBarangay', 'Tagaca')
            ->call('autoFillCandidatePool');

        $selected = $test->get('selectedBeneficiaries') ?? [];
        $this->assertCount(4, $selected);

        $laplaCount = 0;
        $tagacaCount = 0;
        $seenHouseholds = [];

        foreach ($selected as $b) {
            $addr = $b['address'] ?? '';
            $brgy = $b['barangay'] ?? '';
            if (str_contains($addr, 'Lapla') || str_contains($brgy, 'Lapla')) {
                $laplaCount++;
            }
            if (str_contains($addr, 'Tagaca') || str_contains($brgy, 'Tagaca')) {
                $tagacaCount++;
            }
            $hh = $b['household_no'] ?? $b['household_id'] ?? null;
            $this->assertNotNull($hh);
            $this->assertNotContains($hh, $seenHouseholds);
            $seenHouseholds[] = $hh;
        }

        $this->assertEquals(2, $laplaCount, 'Expected 2 beneficiaries from Lapla');
        $this->assertEquals(2, $tagacaCount, 'Expected 2 beneficiaries from Tagaca');
    }

    public function test_autofill_enforces_household_uniqueness_and_excludes_manual_selections(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Household Test Relief',
            'source_code' => 'EMERG-HH-01',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 100000.00,
        ]);

        // Household 1 has two members: A and B
        $bA = Beneficiary::create([
            'first_name' => 'Father',
            'last_name' => 'Reyes',
            'full_name' => 'Reyes, Father',
            'civil_registry_id' => 'CRN-REYES-1',
            'household_no' => 'HH-REYES-100',
            'address' => 'Purok 1, Balasinon, Sulop',
            'barangay' => 'Balasinon',
            'IsDeleted' => 0,
        ]);

        $bB = Beneficiary::create([
            'first_name' => 'Mother',
            'last_name' => 'Reyes',
            'full_name' => 'Reyes, Mother',
            'civil_registry_id' => 'CRN-REYES-2',
            'household_no' => 'HH-REYES-100',
            'address' => 'Purok 1, Balasinon, Sulop',
            'barangay' => 'Balasinon',
            'IsDeleted' => 0,
        ]);

        // Household 2 has one member: C
        $bC = Beneficiary::create([
            'first_name' => 'Solo',
            'last_name' => 'Dela Cruz',
            'full_name' => 'Dela Cruz, Solo',
            'civil_registry_id' => 'CRN-DELACRUZ-1',
            'household_no' => 'HH-DELACRUZ-200',
            'address' => 'Purok 2, Balasinon, Sulop',
            'barangay' => 'Balasinon',
            'IsDeleted' => 0,
        ]);

        // Manually select Father Reyes from HH-REYES-100
        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Cash')
            ->set('newProjectBudgetCap', '50000.00')
            ->set('newProjectUnitAmount', '1000.00')
            ->set('newProjectTargetCount', 3)
            ->call('toggleTargetBarangay', 'Balasinon')
            ->call('openHouseholdReview', $bA->id)
            ->call('confirmAddCandidate')
            ->assertCount('selectedBeneficiaries', 1);

        // Now run Auto-Fill with target 3. Only 1 additional eligible unique household exists (HH-DELACRUZ-200)
        $test->call('autoFillCandidatePool');

        $selected = $test->get('selectedBeneficiaries') ?? [];
        $this->assertCount(2, $selected, 'Only 2 unique households should be selected');

        // Verify Mother Reyes was never added
        $selectedKeys = array_keys($selected);
        $this->assertNotContains('CRN-REYES-2', $selectedKeys);
        $this->assertNotContains($bB->id, $selectedKeys);

        // Verify Dela Cruz was added
        $this->assertTrue(
            in_array('CRN-DELACRUZ-1', $selectedKeys) || in_array((string) $bC->id, $selectedKeys) || in_array($bC->id, $selectedKeys)
        );
    }

    public function test_autofill_handles_insufficient_eligible_households_gracefully(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'Insufficient Aid',
            'source_code' => 'EMERG-INSUF-01',
            'allocated_amount' => 100000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 100000.00,
        ]);

        // Only create 2 beneficiaries in Talao
        Beneficiary::create([
            'first_name' => 'TalaoOne',
            'last_name' => 'Resident',
            'full_name' => 'Resident, TalaoOne',
            'civil_registry_id' => 'CRN-TALAO-1',
            'household_no' => 'HH-TALAO-1',
            'address' => 'Purok 1, Tala-o, Sulop',
            'barangay' => 'Tala-o',
            'IsDeleted' => 0,
        ]);

        Beneficiary::create([
            'first_name' => 'TalaoTwo',
            'last_name' => 'Resident',
            'full_name' => 'Resident, TalaoTwo',
            'civil_registry_id' => 'CRN-TALAO-2',
            'household_no' => 'HH-TALAO-2',
            'address' => 'Purok 2, Tala-o, Sulop',
            'barangay' => 'Tala-o',
            'IsDeleted' => 0,
        ]);

        $test = Livewire::actingAs($this->admin)
            ->test(Workspace::class)
            ->call('openProjectModal')
            ->set('newProjectFundingSourceId', $funding->id)
            ->set('newProjectBenefitType', 'Cash')
            ->set('newProjectBudgetCap', '50000.00')
            ->set('newProjectUnitAmount', '1000.00')
            ->set('newProjectTargetCount', 10) // Request 10
            ->call('toggleTargetBarangay', 'Tala-o')
            ->call('autoFillCandidatePool')
            ->assertDispatched('toast', function ($event, $params) {
                return ($params['type'] ?? '') === 'warning'
                    && str_contains($params['message'] ?? '', 'eligible unique household');
            });

        $selected = $test->get('selectedBeneficiaries') ?? [];
        $this->assertCount(2, $selected);
    }
}
