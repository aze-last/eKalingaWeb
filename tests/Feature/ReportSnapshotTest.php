<?php

namespace Tests\Feature;

use App\DTO\ReportSnapshotData;
use App\Enums\ReportType;
use App\Models\FundingSource;
use App\Models\User;
use App\Services\ReportSnapshotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportSnapshotTest extends TestCase
{
    use RefreshDatabase;

    public function test_budget_utilization_report_snapshot_structure(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $this->actingAs($admin);

        FundingSource::create([
            'funding_type' => 'Government',
            'title' => 'Test Government Grant',
            'source_code' => 'GGMS-2026-001',
            'allocated_amount' => 1000000.00,
            'spent_amount' => 250000.00,
            'remaining_balance' => 750000.00,
        ]);

        $service = app(ReportSnapshotService::class);
        $snapshot = $service->generateSnapshot(ReportType::BudgetUtilization);

        $this->assertInstanceOf(ReportSnapshotData::class, $snapshot);
        $this->assertNotEmpty($snapshot->metadata['title']);
        $this->assertNotEmpty($snapshot->headers);
        $this->assertNotEmpty($snapshot->signatures);
        $this->assertArrayHasKey('prepared_by', $snapshot->signatures);
        $this->assertArrayHasKey('approved_by', $snapshot->signatures);
    }

    public function test_pdf_report_download_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'Admin']);

        $response = $this->actingAs($admin)
            ->get(route('reports.pdf', ['type' => 'BudgetUtilization']));

        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }
}
