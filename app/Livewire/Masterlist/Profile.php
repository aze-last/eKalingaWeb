<?php

namespace App\Livewire\Masterlist;

use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\GgmsConsolidatedTransaction;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Beneficiary Profile - eKalinga+')]
class Profile extends Component
{
    public string $civilRegistryId;

    public ?string $connectionError = null;

    public function mount(string $civilRegistryId): void
    {
        $this->civilRegistryId = $civilRegistryId;
    }

    public function render()
    {
        $this->connectionError = null;
        $beneficiary = null;

        // 1. Fetch live identity record from CRS
        try {
            $beneficiary = Beneficiary::where('civil_registry_id', $this->civilRegistryId)
                ->orWhere('id', (int) $this->civilRegistryId)
                ->first();
        } catch (\Throwable $e) {
            $this->connectionError = 'Could not load live CRS identity: '.$e->getMessage();
        }

        // 2. Fetch all local assistance claims across modules
        $claims = AyudaProjectClaim::with(['ayudaProgram', 'releasingOfficer'])
            ->where(function ($q) use ($beneficiary) {
                $q->where('civil_registry_id', $this->civilRegistryId);
                if ($beneficiary) {
                    $q->orWhere('beneficiary_id', $beneficiary->id);
                }
            })
            ->latest('claimed_at')
            ->get();

        // 3. Fetch GGMS consolidated transactions
        $ggmsTransactions = GgmsConsolidatedTransaction::with('recorder')
            ->where(function ($q) use ($beneficiary) {
                $q->where('civil_registry_id', $this->civilRegistryId);
                if ($beneficiary?->household_no) {
                    $q->orWhere('household_no', $beneficiary->household_no);
                }
                if ($beneficiary) {
                    $q->orWhere('beneficiary_id', $beneficiary->id);
                }
            })
            ->latest('disbursement_date')
            ->get();

        // 4. Combine into a unified, deduplicated claims history stream
        $history = collect();

        foreach ($claims as $c) {
            $history->push([
                'id' => 'CLAIM-'.$c->id,
                'reference_code' => $c->claim_code,
                'program_title' => $c->ayudaProgram?->title ?? 'Municipal Ayuda Release',
                'program_code' => $c->ayudaProgram?->program_code ?? 'AYUDA',
                'benefit_type' => $c->ayudaProgram?->benefit_type->value ?? 'Cash',
                'amount' => $c->unit_amount,
                'item_details' => $c->item_details,
                'disbursed_at' => $c->claimed_at,
                'module' => 'Project Distribution',
                'officer' => $c->releasingOfficer?->name ?? 'Releasing Officer',
                'verification_method' => $c->verification_method ?? 'QR_SCAN',
            ]);
        }

        $existingProjectCodes = $claims->pluck('ayudaProgram.program_code')->filter()->toArray();

        foreach ($ggmsTransactions as $g) {
            // Avoid duplicate entry if this transaction corresponds to an already listed AyudaProjectClaim
            if (in_array($g->project_code, $existingProjectCodes)) {
                continue;
            }

            $history->push([
                'id' => 'GGMS-'.$g->id,
                'reference_code' => $g->project_details_id ?: $g->project_code,
                'program_title' => $g->project_name,
                'program_code' => $g->project_code,
                'benefit_type' => 'Cash',
                'amount' => $g->amount,
                'item_details' => null,
                'disbursed_at' => $g->disbursement_date,
                'module' => $g->project_name ?: 'GGMS External',
                'officer' => $g->recorder?->name ?? 'GGMS Sync',
                'verification_method' => 'GGMS_SYNC',
            ]);
        }

        $sortedHistory = $history->sortByDesc('disbursed_at')->values();
        $totalBenefitsReceived = $sortedHistory->sum('amount');
        $totalClaimsCount = $sortedHistory->count();

        return view('livewire.masterlist.profile', [
            'beneficiary' => $beneficiary,
            'history' => $sortedHistory,
            'totalBenefitsReceived' => $totalBenefitsReceived,
            'totalClaimsCount' => $totalClaimsCount,
            'connectionError' => $this->connectionError,
        ]);
    }
}
