<?php

namespace Tests\Feature;

use App\Enums\BenefitType;
use App\Enums\DistributionStatus;
use App\Enums\FundingType;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\DistributionEnrollment;
use App\Models\FundingSource;
use App\Models\User;
use App\Services\DistributionReleaseService;
use App\Services\HouseholdVerificationService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributionReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected DistributionReleaseService $releaseService;

    protected HouseholdVerificationService $householdService;

    protected User $admin;

    protected FundingSource $funding;

    protected AyudaProgram $program;

    protected Beneficiary $beneficiary;

    protected function setUp(): void
    {
        parent::setUp();
        $this->releaseService = app(DistributionReleaseService::class);
        $this->householdService = app(HouseholdVerificationService::class);
        $this->admin = User::factory()->create(['role' => 'Admin']);

        $this->funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'GGMS Municipal Relief',
            'source_code' => 'GGMS-2026-TEST',
            'allocated_amount' => 500000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 500000.00,
        ]);

        $this->program = AyudaProgram::create([
            'funding_source_id' => $this->funding->id,
            'program_code' => 'AMS-PD-000001',
            'title' => 'Indigent Cash Assistance',
            'benefit_type' => BenefitType::Cash,
            'budget_cap' => 100000.00,
            'unit_amount' => 5000.00,
            'target_beneficiaries' => 20,
        ]);

        $this->beneficiary = Beneficiary::create([
            'civil_registry_id' => 'CRN-2026-9999',
            'household_no' => 'HH-SULOP-900',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'barangay' => 'Poblacion',
        ]);
    }

    public function test_atomic_4_way_release_transaction(): void
    {
        $result = $this->releaseService->processRelease(
            program: $this->program,
            beneficiary: $this->beneficiary,
            verificationMethod: 'QR_SCAN',
            qrPayload: 'QR-TEST-001',
            userId: $this->admin->id
        );

        $this->assertTrue($result['success']);

        // 1. AyudaProjectClaim created
        $this->assertDatabaseHas('ayuda_project_claims', [
            'ayuda_program_id' => $this->program->id,
            'beneficiary_id' => $this->beneficiary->id,
            'unit_amount' => 5000.00,
        ]);

        // 2. BudgetLedgerEntry recorded
        $this->assertDatabaseHas('budget_ledger_entries', [
            'ayuda_program_id' => $this->program->id,
            'amount' => 5000.00,
        ]);

        // 3. GGMS Consolidated Transaction recorded
        $this->assertDatabaseHas('ggms_consolidated_transactions', [
            'project_code' => $this->program->program_code,
            'civil_registry_id' => $this->beneficiary->civil_registry_id,
            'amount' => 5000.00,
        ]);

        // 4. DistributionEnrollment status is RELEASED
        $this->assertDatabaseHas('distribution_enrollments', [
            'ayuda_program_id' => $this->program->id,
            'beneficiary_id' => $this->beneficiary->id,
            'status' => DistributionStatus::RELEASED->value,
        ]);
    }

    public function test_prevent_double_claim_for_same_beneficiary(): void
    {
        // First claim succeeds
        $this->releaseService->processRelease($this->program, $this->beneficiary, userId: $this->admin->id);

        // Second claim attempt must fail
        $this->expectException(Exception::class);
        $this->releaseService->processRelease($this->program, $this->beneficiary, userId: $this->admin->id);
    }

    public function test_household_duplicate_check_detects_co_member_claim(): void
    {
        // Juan claims
        $this->releaseService->processRelease($this->program, $this->beneficiary, userId: $this->admin->id);

        // Maria from the SAME household (HH-SULOP-900)
        $maria = Beneficiary::create([
            'civil_registry_id' => 'CRN-2026-9998',
            'household_no' => 'HH-SULOP-900', // Same household
            'first_name' => 'Maria',
            'last_name' => 'Dela Cruz',
            'barangay' => 'Poblacion',
        ]);

        $check = $this->householdService->checkHouseholdStatus($maria, $this->program);

        $this->assertTrue($check['has_warning']);
        $this->assertStringContainsString('Dela Cruz', $check['message']);
    }
}
