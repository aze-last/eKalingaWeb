<?php

namespace App\Services;

use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;

class HouseholdVerificationService
{
    /**
     * Check if any other household member has already received assistance.
     *
     * @return array{has_warning: bool, duplicate_type: string, message: string, existing_claims: array}
     */
    public function checkHouseholdStatus(Beneficiary $beneficiary, AyudaProgram $currentProgram): array
    {
        if (empty($beneficiary->household_no)) {
            return [
                'has_warning' => false,
                'duplicate_type' => 'NONE',
                'message' => 'No household number registered.',
                'existing_claims' => [],
            ];
        }

        // Find all other beneficiaries in the same household
        $householdMembers = Beneficiary::where('household_no', $beneficiary->household_no)
            ->where('id', '!=', $beneficiary->id)
            ->pluck('id');

        if ($householdMembers->isEmpty()) {
            return [
                'has_warning' => false,
                'duplicate_type' => 'NONE',
                'message' => 'No other members found in household '.$beneficiary->household_no,
                'existing_claims' => [],
            ];
        }

        // Query claims made by other household members in this program or matching benefit type
        $existingClaims = AyudaProjectClaim::with(['beneficiary', 'ayudaProgram'])
            ->whereIn('beneficiary_id', $householdMembers)
            ->where(function ($query) use ($currentProgram) {
                $query->where('ayuda_program_id', $currentProgram->id)
                    ->orWhereHas('ayudaProgram', function ($q) use ($currentProgram) {
                        $q->where('benefit_type', $currentProgram->benefit_type);
                    });
            })
            ->latest('claimed_at')
            ->get();

        if ($existingClaims->isNotEmpty()) {
            $firstClaim = $existingClaims->first();
            $memberName = $firstClaim->beneficiary?->full_name ?? 'A household member';
            $programTitle = $firstClaim->ayudaProgram?->title ?? 'a project';
            $claimDate = $firstClaim->claimed_at->format('M d, Y');

            $inSameProject = $existingClaims->contains('ayuda_program_id', $currentProgram->id);

            return [
                'has_warning' => true,
                'duplicate_type' => $inSameProject ? 'SAME_PROJECT' : 'CROSS_PROJECT_SAME_TYPE',
                'message' => "Warning: Household #{$beneficiary->household_no} member {$memberName} already claimed in {$programTitle} on {$claimDate}.",
                'existing_claims' => $existingClaims->map(fn ($c) => [
                    'claim_code' => $c->claim_code,
                    'member_name' => $c->beneficiary?->full_name,
                    'program' => $c->ayudaProgram?->title,
                    'amount' => $c->unit_amount,
                    'date' => $c->claimed_at->format('M d, Y h:i A'),
                ])->toArray(),
            ];
        }

        return [
            'has_warning' => false,
            'duplicate_type' => 'NONE',
            'message' => 'No duplicate household claims detected.',
            'existing_claims' => [],
        ];
    }
}
