<?php

namespace Tests\Feature;

use App\Services\GgmsBudgetSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GgmsOfficeAllocationSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_office_budget_pulls_70k_allocation_from_officeallocations(): void
    {
        $syncService = app(GgmsBudgetSyncService::class);
        $snapshot = $syncService->syncOfficeBudget('OFF-2026-0006');

        $this->assertNotNull($snapshot);
        $this->assertEquals('OFF-2026-0006', $snapshot->office_code);
        $this->assertEquals(70000.00, (float) $snapshot->allocated_amount);
        $this->assertEquals('officeallocations', $snapshot->source_table);

        $this->assertDatabaseHas('funding_sources', [
            'source_code' => 'GGMS-OFF-2026-0006',
            'allocated_amount' => 70000.00,
            'remaining_balance' => 70000.00,
        ]);
    }
}
