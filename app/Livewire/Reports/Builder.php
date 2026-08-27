<?php

namespace App\Livewire\Reports;

use App\Enums\ReportType;
use App\Models\AyudaProgram;
use App\Services\ReportSnapshotService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('components.layouts.app')]
#[Title('Reports & Audits - eKalinga+')]
class Builder extends Component
{
    public string $selectedReportType = 'BudgetUtilization';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $selectedProgramId = null;

    public function mount(): void
    {
        $this->dateFrom = now()->startOfMonth()->toDateString();
        $this->dateTo = now()->toDateString();
    }

    public function exportCsv(ReportSnapshotService $reportService): StreamedResponse
    {
        $reportTypeEnum = ReportType::from($this->selectedReportType);
        $snapshot = $reportService->generateSnapshot(
            type: $reportTypeEnum,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            programId: $this->selectedProgramId
        );

        $filename = 'eKalinga_Report_'.$this->selectedReportType.'_'.date('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($snapshot) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [$snapshot->metadata['title']]);
            fputcsv($handle, [$snapshot->metadata['subtitle']]);
            fputcsv($handle, ['Period: '.$snapshot->metadata['date_range_label']]);
            fputcsv($handle, []); // empty line

            // Headers
            fputcsv($handle, $snapshot->headers);

            // Rows
            foreach ($snapshot->rows as $row) {
                fputcsv($handle, array_values($row));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function render(ReportSnapshotService $reportService)
    {
        $reportTypeEnum = ReportType::from($this->selectedReportType);

        $snapshot = $reportService->generateSnapshot(
            type: $reportTypeEnum,
            dateFrom: $this->dateFrom,
            dateTo: $this->dateTo,
            programId: $this->selectedProgramId
        );

        $programs = AyudaProgram::latest()->get();

        return view('livewire.reports.builder', [
            'snapshot' => $snapshot,
            'programs' => $programs,
            'reportTypes' => ReportType::cases(),
        ]);
    }
}
