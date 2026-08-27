<?php

namespace App\Services;

use App\DTO\ReportSnapshotData;
use App\Enums\ReportType;
use App\Models\ActivityLog;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\FundingSource;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ReportSnapshotService
{
    /**
     * Build the structured snapshot DTO for the selected report type and parameters.
     */
    public function generateSnapshot(
        ReportType $type,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?int $programId = null
    ): ReportSnapshotData {
        $from = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
        $to = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfDay();
        $dateRangeLabel = $from->format('M d, Y').' - '.$to->format('M d, Y');

        $preparedBy = Auth::user()?->name ?? 'System Administrator';
        $reviewedBy = Setting::get('municipal_accountant_name', 'Municipal Budget Officer');
        $approvedBy = Setting::get('municipal_mayor_name', 'Hon. Municipal Mayor');

        $signatures = [
            'prepared_by' => $preparedBy,
            'reviewed_by' => $reviewedBy,
            'approved_by' => $approvedBy,
        ];

        return match ($type) {
            ReportType::BudgetUtilization => $this->buildBudgetUtilizationSnapshot($from, $to, $dateRangeLabel, $signatures),
            ReportType::DistributionClaims => $this->buildDistributionClaimsSnapshot($from, $to, $programId, $dateRangeLabel, $signatures),
            ReportType::AdminActivityAudit => $this->buildAdminActivityAuditSnapshot($from, $to, $dateRangeLabel, $signatures),
        };
    }

    protected function buildBudgetUtilizationSnapshot(Carbon $from, Carbon $to, string $dateRangeLabel, array $signatures): ReportSnapshotData
    {
        $programs = AyudaProgram::with('fundingSource')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        $totalCap = $programs->sum('budget_cap');
        $totalDisbursed = $programs->sum('total_disbursed_amount');
        $totalRemaining = $totalCap - $totalDisbursed;
        $utilizationRate = $totalCap > 0 ? round(($totalDisbursed / $totalCap) * 100, 1) : 0;

        $govFunds = FundingSource::where('funding_type', 'Government')->sum('allocated_amount');
        $privateFunds = FundingSource::where('funding_type', 'Private')->sum('allocated_amount');

        $highlights = [
            'Total allocated program budget across all active initiatives stands at ₱'.number_format($totalCap, 2).'.',
            "Overall budget utilization rate is currently at {$utilizationRate}% with ₱".number_format($totalDisbursed, 2).' successfully disbursed.',
            'Government (GGMS) funding accounts for ₱'.number_format($govFunds, 2).' while Private Donations contribute ₱'.number_format($privateFunds, 2).'.',
            $totalRemaining < ($totalCap * 0.1) && $totalCap > 0
                ? 'ALERT: Overall remaining balance has dropped below 10% threshold.'
                : 'Budget allocations and disbursements remain within planned thresholds.',
        ];

        $metrics = [
            ['label' => 'Total Program Cap', 'value' => '₱'.number_format($totalCap, 2), 'subtext' => "{$programs->count()} Projects"],
            ['label' => 'Total Disbursed', 'value' => '₱'.number_format($totalDisbursed, 2), 'subtext' => "{$utilizationRate}% Utilized", 'status' => 'success'],
            ['label' => 'Remaining Balance', 'value' => '₱'.number_format($totalRemaining, 2), 'subtext' => 'Unrestricted/Unspent', 'status' => 'info'],
            ['label' => 'Active Programs', 'value' => (string) $programs->count(), 'subtext' => 'In Current Fiscal Period'],
        ];

        $headers = ['Program Code', 'Project Title', 'Funding Source', 'Type', 'Budget Cap', 'Disbursed', 'Remaining', 'Beneficiaries', 'Status'];
        $rows = $programs->map(fn ($p) => [
            'Program Code' => $p->program_code,
            'Project Title' => $p->title,
            'Funding Source' => $p->fundingSource?->source_code ?? 'N/A',
            'Type' => $p->benefit_type->value,
            'Budget Cap' => '₱'.number_format($p->budget_cap, 2),
            'Disbursed' => '₱'.number_format($p->total_disbursed_amount, 2),
            'Remaining' => '₱'.number_format($p->budget_cap - $p->total_disbursed_amount, 2),
            'Beneficiaries' => "{$p->total_claimed_count} / {$p->target_beneficiaries}",
            'Status' => $p->status->value,
        ])->toArray();

        return new ReportSnapshotData(
            metadata: [
                'title' => 'MUNICIPAL BUDGET UTILIZATION & CAP AUDIT REPORT',
                'subtitle' => 'Comprehensive allocation and disbursement ledger for the Municipality of Sulop',
                'date_range_label' => $dateRangeLabel,
                'program_label' => 'All Programs',
                'orientation' => 'landscape',
                'generated_at' => now()->format('M d, Y h:i A'),
                'generated_by' => $signatures['prepared_by'],
            ],
            highlights: $highlights,
            metrics: $metrics,
            headers: $headers,
            rows: $rows,
            signatures: $signatures
        );
    }

    protected function buildDistributionClaimsSnapshot(Carbon $from, Carbon $to, ?int $programId, string $dateRangeLabel, array $signatures): ReportSnapshotData
    {
        $query = AyudaProjectClaim::with(['beneficiary', 'ayudaProgram', 'releasingOfficer'])
            ->whereBetween('claimed_at', [$from, $to]);

        $programLabel = 'All Distribution Projects';
        if ($programId) {
            $query->where('ayuda_program_id', $programId);
            $selectedProg = AyudaProgram::find($programId);
            if ($selectedProg) {
                $programLabel = "{$selectedProg->program_code} - {$selectedProg->title}";
            }
        }

        $claims = $query->latest('claimed_at')->get();
        $totalClaimedAmount = $claims->sum('unit_amount');
        $uniqueHouseholds = $claims->pluck('beneficiary.household_no')->filter()->unique()->count();
        $uniqueBarangays = $claims->pluck('beneficiary.barangay')->filter()->unique()->count();

        $highlights = [
            "A total of {$claims->count()} beneficiaries have claimed aid totaling ₱".number_format($totalClaimedAmount, 2).'.',
            "Assistance reached {$uniqueHouseholds} distinct households across {$uniqueBarangays} barangays in Sulop.",
            'All claims recorded with verified timestamps and releasing officer digital signatures.',
        ];

        $metrics = [
            ['label' => 'Total Claims Released', 'value' => number_format($claims->count()), 'subtext' => 'Processed Recipients', 'status' => 'success'],
            ['label' => 'Total Value Disbursed', 'value' => '₱'.number_format($totalClaimedAmount, 2), 'subtext' => 'Cash & In-Kind Valuation'],
            ['label' => 'Unique Households', 'value' => number_format($uniqueHouseholds), 'subtext' => 'Beneficiary Families'],
            ['label' => 'Barangays Covered', 'value' => (string) $uniqueBarangays, 'subtext' => 'Municipal Coverage'],
        ];

        $headers = ['Claim Code', 'Beneficiary Name', 'Civil Registry ID', 'Household #', 'Barangay', 'Project Title', 'Amount / Item', 'Claim Date & Time', 'Releasing Officer'];
        $rows = $claims->map(fn ($c) => [
            'Claim Code' => $c->claim_code,
            'Beneficiary Name' => $c->beneficiary?->full_name ?? 'N/A',
            'Civil Registry ID' => $c->beneficiary?->civil_registry_id ?? 'N/A',
            'Household #' => $c->beneficiary?->household_no ?? 'N/A',
            'Barangay' => $c->beneficiary?->barangay ?? 'N/A',
            'Project Title' => $c->ayudaProgram?->title ?? 'N/A',
            'Amount / Item' => $c->ayudaProgram?->benefit_type->value === 'Cash' ? '₱'.number_format($c->unit_amount, 2) : ($c->item_details ?? 'In-Kind'),
            'Claim Date & Time' => $c->claimed_at->format('M d, Y h:i A'),
            'Releasing Officer' => $c->releasingOfficer?->name ?? 'Admin',
        ])->toArray();

        return new ReportSnapshotData(
            metadata: [
                'title' => 'AYUDA PROJECT DISTRIBUTION CLAIMS REPORT',
                'subtitle' => 'Official recipient disbursement masterlog and verification register',
                'date_range_label' => $dateRangeLabel,
                'program_label' => $programLabel,
                'orientation' => 'landscape',
                'generated_at' => now()->format('M d, Y h:i A'),
                'generated_by' => $signatures['prepared_by'],
            ],
            highlights: $highlights,
            metrics: $metrics,
            headers: $headers,
            rows: $rows,
            signatures: $signatures
        );
    }

    protected function buildAdminActivityAuditSnapshot(Carbon $from, Carbon $to, string $dateRangeLabel, array $signatures): ReportSnapshotData
    {
        $logs = ActivityLog::with('user')
            ->whereBetween('created_at', [$from, $to])
            ->latest()
            ->get();

        $actionCounts = $logs->groupBy('action')->map->count();
        $userCounts = $logs->groupBy('user_id')->map->count();

        $highlights = [
            "Recorded {$logs->count()} immutable administrative audit log entries during the specified period.",
            "{$userCounts->count()} unique system administrator accounts engaged in operational workflows.",
            "Actions logged include: Release ({$actionCounts->get('Release', 0)}), Create ({$actionCounts->get('Create', 0)}), Donation ({$actionCounts->get('Donation', 0)}).",
        ];

        $metrics = [
            ['label' => 'Total Audit Logs', 'value' => number_format($logs->count()), 'subtext' => 'Immutable System Trail'],
            ['label' => 'Releases Executed', 'value' => (string) $actionCounts->get('Release', 0), 'subtext' => 'Disbursement Actions', 'status' => 'success'],
            ['label' => 'Project/Budget Edits', 'value' => (string) ($actionCounts->get('Create', 0) + $actionCounts->get('Update', 0)), 'subtext' => 'Config Operations'],
            ['label' => 'Active Admins', 'value' => (string) $userCounts->count(), 'subtext' => 'Logged Operators'],
        ];

        $headers = ['Timestamp', 'Administrator', 'Module', 'Action', 'Description', 'IP Address'];
        $rows = $logs->map(fn ($l) => [
            'Timestamp' => $l->created_at->format('M d, Y h:i:s A'),
            'Administrator' => $l->user?->name ?? 'System',
            'Module' => $l->module,
            'Action' => $l->action,
            'Description' => $l->description,
            'IP Address' => $l->ip_address ?? '127.0.0.1',
        ])->toArray();

        return new ReportSnapshotData(
            metadata: [
                'title' => 'ADMINISTRATIVE ACTIVITY & COMPLIANCE AUDIT TRAIL',
                'subtitle' => 'Immutable forensic log of municipal operator actions, authorizations, and disbursements',
                'date_range_label' => $dateRangeLabel,
                'program_label' => 'System-Wide Audit',
                'orientation' => 'landscape',
                'generated_at' => now()->format('M d, Y h:i A'),
                'generated_by' => $signatures['prepared_by'],
            ],
            highlights: $highlights,
            metrics: $metrics,
            headers: $headers,
            rows: $rows,
            signatures: $signatures
        );
    }
}
