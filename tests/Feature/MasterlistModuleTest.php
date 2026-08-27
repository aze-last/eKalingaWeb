<?php

namespace Tests\Feature;

use App\Enums\BenefitType;
use App\Enums\FundingType;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\GgmsConsolidatedTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MasterlistModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'Admin']);
    }

    public function test_unauthenticated_user_redirected_from_masterlist(): void
    {
        $response = $this->get('/masterlist');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_masterlist_index(): void
    {
        // Seed a beneficiary in the CRS table
        Beneficiary::create([
            'civil_registry_id' => 'CRN-2026-0001',
            'household_no' => 'HH-001',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'barangay' => 'Poblacion',
        ]);

        $response = $this->actingAs($this->user)->get('/masterlist');

        $response->assertStatus(200);
        $response->assertSee('Civil Registry Masterlist');
        $response->assertSee('Maria');
        $response->assertSee('CRN-2026-0001');
    }

    public function test_authenticated_user_can_view_masterlist_profile_with_claims_history(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'LGU Relief',
            'source_code' => 'LGU-2026',
            'allocated_amount' => 500000,
            'spent_amount' => 0,
            'remaining_balance' => 500000,
        ]);

        $program = AyudaProgram::create([
            'funding_source_id' => $funding->id,
            'program_code' => 'AMS-PD-000100',
            'title' => 'Emergency Cash Grant',
            'benefit_type' => BenefitType::Cash,
            'budget_cap' => 50000,
            'unit_amount' => 3000,
            'target_beneficiaries' => 10,
        ]);

        $beneficiary = Beneficiary::create([
            'civil_registry_id' => 'CRN-2026-7777',
            'household_no' => 'HH-777',
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'barangay' => 'Balasinon',
        ]);

        // Create local disbursement claim
        AyudaProjectClaim::create([
            'ayuda_program_id' => $program->id,
            'beneficiary_id' => $beneficiary->id,
            'civil_registry_id' => 'CRN-2026-7777',
            'household_no' => 'HH-777',
            'claim_code' => 'CLM-TEST-7777',
            'unit_amount' => 3000.00,
            'verification_method' => 'QR_SCAN',
            'claimed_at' => now(),
        ]);

        // Create GGMS transaction
        GgmsConsolidatedTransaction::create([
            'project_code' => 'GGMS-CFW-001',
            'project_details_id' => 'OPP-CFW-001',
            'project_name' => 'Cash For Work',
            'beneficiary_id' => $beneficiary->id,
            'civil_registry_id' => 'CRN-2026-7777',
            'first_name' => 'Jose',
            'last_name' => 'Rizal',
            'barangay' => 'Balasinon',
            'household_no' => 'HH-777',
            'amount' => 4500.00,
            'benefit_type' => 'Cash',
            'disbursement_date' => now(),
            'sync_status' => 'Synced',
        ]);

        $response = $this->actingAs($this->user)->get('/masterlist/CRN-2026-7777');

        $response->assertStatus(200);
        $response->assertSee('Jose');
        $response->assertSee('CRN-2026-7777');
        $response->assertSee('Emergency Cash Grant');
        $response->assertSee('Cash For Work');
        $response->assertSee('7,500.00'); // 3000 + 4500 total
    }
}
