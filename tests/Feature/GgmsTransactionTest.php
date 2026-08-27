<?php

namespace Tests\Feature;

use App\Enums\FundingType;
use App\Models\AyudaProgram;
use App\Models\Beneficiary;
use App\Models\FundingSource;
use App\Models\GgmsPendingTransaction;
use App\Services\GgmsTransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GgmsTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_ggms_transaction_records_deterministic_project_code(): void
    {
        $funding = FundingSource::create([
            'funding_type' => FundingType::Government,
            'title' => 'GGMS Relief',
            'source_code' => 'GGMS-TEST-001',
            'project_details_id' => 'OPP-2026-0006',
            'allocated_amount' => 500000.00,
            'spent_amount' => 0.00,
            'remaining_balance' => 500000.00,
        ]);

        $program = AyudaProgram::create([
            'funding_source_id' => $funding->id,
            'program_code' => 'AMS-PD-000001',
            'title' => 'Rice Distribution',
            'benefit_type' => 'Goods',
            'budget_cap' => 100000.00,
        ]);

        $beneficiary = Beneficiary::create([
            'civil_registry_id' => 'CRN-2026-0001',
            'household_no' => 'HH-001',
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'barangay' => 'Balasinon',
        ]);

        $service = app(GgmsTransactionService::class);
        $trx = $service->recordRelease($program, $beneficiary, 1500.00, 'Goods', '1 Sack Rice');

        $this->assertEquals('AMS-PD-000001', $trx->project_code);
        $this->assertEquals('OPP-2026-0006', $trx->project_details_id);
        $this->assertEquals('Project Distribution', $trx->project_name);
        $this->assertEquals('Pedro', $trx->first_name);
    }

    public function test_flush_pending_offline_transactions(): void
    {
        GgmsPendingTransaction::create([
            'transaction_uuid' => 'test-uuid-001',
            'project_code' => 'AMS-PD-000001',
            'project_name' => 'Project Distribution',
            'payload' => [
                'project_code' => 'AMS-PD-000001',
                'project_name' => 'Project Distribution',
                'first_name' => 'Ana',
                'last_name' => 'Gomez',
                'barangay' => 'Poblacion',
                'household_no' => 'HH-002',
                'amount' => 5000.00,
            ],
            'status' => 'Pending',
        ]);

        $service = app(GgmsTransactionService::class);
        $flushed = $service->flushPendingTransactions();

        $this->assertEquals(1, $flushed);
        $this->assertDatabaseHas('ggms_consolidated_transactions', [
            'first_name' => 'Ana',
            'last_name' => 'Gomez',
            'amount' => 5000.00,
        ]);
        $this->assertDatabaseHas('ggms_pending_transactions', [
            'transaction_uuid' => 'test-uuid-001',
            'status' => 'Completed',
        ]);
    }
}
