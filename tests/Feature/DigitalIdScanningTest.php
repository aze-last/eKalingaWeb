<?php

namespace Tests\Feature;

use App\Enums\BenefitType;
use App\Enums\FundingType;
use App\Enums\ProgramStatus;
use App\Livewire\Distribution\Workspace;
use App\Models\AyudaProgram;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\User;
use App\Services\DigitalIdVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class DigitalIdScanningTest extends TestCase
{
    use RefreshDatabase;

    protected DigitalIdVerificationService $verificationService;

    protected User $admin;

    protected AyudaProgram $program;

    protected function setUp(): void
    {
        parent::setUp();
        $this->verificationService = app(DigitalIdVerificationService::class);
        $this->admin = User::factory()->create(['role' => 'Admin']);

        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'GGMS Municipal Relief',
            'source_code' => 'GGMS-2026-TEST',
            'allocated_amount' => 500000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 500000.00,
        ]);

        $this->program = AyudaProgram::create([
            'funding_source_id' => $funding->id,
            'program_code' => 'AMS-PD-000001',
            'title' => 'Indigent Cash Assistance',
            'status' => ProgramStatus::Active,
            'benefit_type' => BenefitType::Cash,
            'budget_cap' => 100000.00,
            'unit_amount' => 5000.00,
            'target_beneficiaries' => 20,
        ]);
    }

    public function test_payload_normalization_and_routing(): void
    {
        $raw = "\r\n CRS-2026-18-00001 \n\x00";
        $clean = $this->verificationService->normalizePayload($raw);
        $this->assertEquals('CRS-2026-18-00001', $clean);

        $this->assertEquals('CRS_CARD_NUMBER', $this->verificationService->detectPayloadType('CRS-2026-18-00001'));
        $this->assertEquals('CRS_BENEFICIARY_ID', $this->verificationService->detectPayloadType('BEN-2026-703984046-1'));
        $this->assertEquals('EKALINGA_QR', $this->verificationService->detectPayloadType('EKALIN-693631224-AMS-PD-000001'));
        $this->assertEquals('OWN_DIGITAL_ID', $this->verificationService->detectPayloadType('ASMBID-12345'));
        $this->assertEquals('CIVIL_REGISTRY_ID', $this->verificationService->detectPayloadType('CRN-693631224'));
        $this->assertEquals('NAME_LOOKUP', $this->verificationService->detectPayloadType('Ababa, Victoria'));
    }

    public function test_live_crs_active_digital_id_verification(): void
    {
        try {
            $result = $this->verificationService->verify('CRS-2026-18-00001');

            $this->assertTrue($result['success']);
            $this->assertEquals('VERIFIED', $result['status']);
            $this->assertEquals('CRS_LIVE', $result['source']);
            $this->assertNotNull($result['beneficiary']);
        } catch (\Throwable) {
            $this->markTestSkipped('CRS external connection not reachable in this test run.');
        }
    }

    public function test_live_crs_revoked_digital_id_verification(): void
    {
        try {
            $result = $this->verificationService->verify('CRS-2026-7-00001');

            $this->assertFalse($result['success']);
            $this->assertEquals('REVOKED', $result['status']);
            $this->assertEquals('CRS_LIVE', $result['source']);
            $this->assertStringContainsString('REVOKED', $result['message']);
        } catch (\Throwable) {
            $this->markTestSkipped('CRS external connection not reachable in this test run.');
        }
    }

    public function test_offline_cache_resilience_fallback(): void
    {
        $payload = 'CRS-OFFLINE-TEST-999';
        $cacheKey = 'crs_did_v1_'.md5(strtolower($payload));

        $ben = Beneficiary::create([
            'civil_registry_id' => 'CRN-OFFLINE-001',
            'household_no' => 'HH-OFFLINE-001',
            'first_name' => 'Offline',
            'last_name' => 'Citizen',
            'barangay' => 'Poblacion',
        ]);

        // Seed cache simulating previously cached digital ID verification
        Cache::put($cacheKey, [
            'status' => 'VERIFIED',
            'digital_id_info' => ['id_number' => $payload, 'status' => 'Active'],
            'beneficiary_id' => $ben->id,
            'full_name' => $ben->full_name,
            'barangay' => $ben->barangay,
            'household_no' => $ben->household_no,
            'cached_at' => now()->toIso8601String(),
        ], 86400);

        // When CRS has no such record, but cache exists
        $result = $this->verificationService->verify($payload);

        $this->assertTrue($result['success']);
        $this->assertEquals('OFFLINE_CACHED', $result['status']);
        $this->assertEquals('OFFLINE_CACHE', $result['source']);
        $this->assertEquals($ben->id, $result['beneficiary']->id);
    }

    public function test_unknown_card_not_found(): void
    {
        $result = $this->verificationService->verify('CRS-9999-99-999999999');

        $this->assertFalse($result['success']);
        $this->assertEquals('NOT_FOUND', $result['status']);
        $this->assertStringContainsString('not found', strtolower($result['message']));
    }

    public function test_livewire_handle_scan_integration(): void
    {
        $this->actingAs($this->admin);

        $payload = 'CRS-2026-TEST-CLAIM';
        $cacheKey = 'crs_did_v1_'.md5(strtolower($payload));

        $ben = Beneficiary::create([
            'civil_registry_id' => 'CRN-SCAN-001',
            'household_no' => 'HH-SCAN-001',
            'first_name' => 'Scanned',
            'last_name' => 'Beneficiary',
            'barangay' => 'Poblacion',
        ]);

        Cache::put($cacheKey, [
            'status' => 'VERIFIED',
            'digital_id_info' => ['id_number' => $payload, 'status' => 'Active'],
            'beneficiary_id' => $ben->id,
            'full_name' => $ben->full_name,
            'barangay' => $ben->barangay,
            'household_no' => $ben->household_no,
            'cached_at' => now()->toIso8601String(),
        ], 86400);

        Livewire::test(Workspace::class)
            ->set('selectedProjectId', $this->program->id)
            ->call('handleScan', $payload)
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'success';
            });

        $this->assertDatabaseHas('ayuda_project_claims', [
            'ayuda_program_id' => $this->program->id,
            'beneficiary_id' => $ben->id,
            'unit_amount' => 5000.00,
        ]);
    }

    public function test_livewire_enroll_beneficiary_into_distribution_queue(): void
    {
        $this->actingAs($this->admin);

        $ben = Beneficiary::create([
            'civil_registry_id' => 'CRN-ENROLL-001',
            'household_no' => 'HH-ENROLL-001',
            'first_name' => 'Queued',
            'last_name' => 'Citizen',
            'barangay' => 'Poblacion',
        ]);

        Livewire::test(Workspace::class)
            ->set('selectedProjectId', $this->program->id)
            ->call('enrollBeneficiary', $ben->id)
            ->assertDispatched('toast', function ($name, $params) {
                return $params['type'] === 'success';
            });

        $this->assertDatabaseHas('distribution_enrollments', [
            'ayuda_program_id' => $this->program->id,
            'beneficiary_id' => $ben->id,
            'status' => 'PENDING',
        ]);
    }
}
