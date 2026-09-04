<?php

namespace Tests\Feature;

use App\Enums\DistributionStatus;
use App\Enums\FundingType;
use App\Enums\LedgerEntryType;
use App\Enums\ProgramStatus;
use App\Livewire\Budget\Workspace;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\User;
use App\Services\BudgetLedgerService;
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
            ->call('createProject');

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
            ->call('createProject');

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
            ->call('createProject');

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
}
