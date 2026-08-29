<?php

namespace App\Livewire\Ggms;

use App\Models\GgmsConsolidatedTransaction;
use App\Models\GgmsPendingTransaction;
use App\Services\GgmsTransactionService;
use App\Services\PerformanceCacheService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('GGMS Consolidated Transactions - eKalinga+')]
class TransactionLedger extends Component
{
    use WithPagination;

    // Filters
    public string $search = '';

    public string $categoryTab = 'ALL'; // ALL, Project Distribution, Cash For Work, Seminar, Aid Request

    public string $selectedBarangay = '';

    // Slide-out Inspector State
    public bool $showInspector = false;

    public ?int $inspectingTransactionId = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryTab(): void
    {
        $this->resetPage();
    }

    public function inspectTransaction(int $id): void
    {
        $this->inspectingTransactionId = $id;
        $this->showInspector = true;
    }

    public function closeInspector(): void
    {
        $this->showInspector = false;
        $this->inspectingTransactionId = null;
    }

    public function syncNow(GgmsTransactionService $ggmsService): void
    {
        $res = $ggmsService->syncWithGgms();
        $this->dispatch('play-audio-success');
        $this->dispatch('toast', type: 'success', message: "GGMS Synchronization complete. {$res['pushed']} pushed to municipal database, {$res['pulled']} new records consolidated.");
    }

    public function render()
    {
        // 1. KPI Counts
        $totalSynced = GgmsConsolidatedTransaction::count();
        $distributionCount = GgmsConsolidatedTransaction::where('project_name', 'Project Distribution')->count();
        $cfwCount = GgmsConsolidatedTransaction::whereIn('project_name', ['Cash For Work', 'Seminar'])->count();
        $aidRequestCount = GgmsConsolidatedTransaction::where('project_name', 'Aid Request')->count();
        $pendingRetryCount = GgmsPendingTransaction::where('status', 'Pending')->count();

        // 2. Main Query
        $query = GgmsConsolidatedTransaction::with(['beneficiary', 'recorder'])
            ->latest('disbursement_date');

        if ($this->categoryTab !== 'ALL') {
            $query->where('project_name', $this->categoryTab);
        }

        if ($this->selectedBarangay) {
            $query->where('barangay', $this->selectedBarangay);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                    ->orWhere('last_name', 'like', "%{$this->search}%")
                    ->orWhere('project_code', 'like', "%{$this->search}%")
                    ->orWhere('civil_registry_id', 'like', "%{$this->search}%")
                    ->orWhere('household_no', 'like', "%{$this->search}%")
                    ->orWhere('project_details_id', 'like', "%{$this->search}%");
            });
        }

        // Fixed 25 per page pagination
        $transactions = $query->paginate(25);

        // Inspected Record Details
        $inspectedRecord = $this->inspectingTransactionId
            ? GgmsConsolidatedTransaction::with(['beneficiary', 'recorder'])->find($this->inspectingTransactionId)
            : null;

        $barangays = app(PerformanceCacheService::class)->getBarangays();

        return view('livewire.ggms.transaction-ledger', [
            'totalSynced' => $totalSynced,
            'distributionCount' => $distributionCount,
            'cfwCount' => $cfwCount,
            'aidRequestCount' => $aidRequestCount,
            'pendingRetryCount' => $pendingRetryCount,
            'transactions' => $transactions,
            'inspectedRecord' => $inspectedRecord,
            'barangays' => $barangays,
        ]);
    }
}
