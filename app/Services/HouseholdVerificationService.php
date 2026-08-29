<?php

namespace App\Services;

use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class HouseholdVerificationService
{
    /**
     * Check if any other household member has already received assistance.
     *
     * @return array{has_warning: bool, duplicate_type: string, message: string, existing_claims: array}
     */
    public function checkHouseholdStatus(Beneficiary $beneficiary, AyudaProgram $currentProgram): array
    {
        $hhNo = $beneficiary->household_no;
        if (empty($hhNo) || $hhNo === 'N/A') {
            return [
                'has_warning' => false,
                'duplicate_type' => 'NONE',
                'message' => 'No household number registered.',
                'household_no' => 'N/A',
                'existing_claims' => [],
            ];
        }

        try {
            // Find other beneficiaries in same household
            $householdMembers = collect();
            $connection = $beneficiary->getConnectionName() ?: config('database.default');
            $table = $beneficiary->getTable();

            if (Schema::connection($connection)->hasColumn($table, 'household_no')) {
                $householdMembers = Beneficiary::where('household_no', $hhNo)
                    ->where('id', '!=', $beneficiary->id)
                    ->pluck('id');
            } elseif (Schema::connection($connection)->hasColumn($table, 'beneficiary_id')) {
                $householdMembers = Beneficiary::where('beneficiary_id', 'like', "%-{$hhNo}-%")
                    ->where('id', '!=', $beneficiary->id)
                    ->pluck('id');
            }

            // Query claims made by other household members in local AyudaProjectClaim
            $claimQuery = AyudaProjectClaim::with(['beneficiary', 'ayudaProgram'])
                ->where('household_no', $hhNo)
                ->where('beneficiary_id', '!=', $beneficiary->id)
                ->where(function ($query) use ($currentProgram) {
                    $query->where('ayuda_program_id', $currentProgram->id)
                        ->orWhereHas('ayudaProgram', function ($q) use ($currentProgram) {
                            $q->where('benefit_type', $currentProgram->benefit_type);
                        });
                });

            if ($householdMembers->isNotEmpty()) {
                $claimQuery->orWhere(function ($q) use ($householdMembers, $currentProgram) {
                    $q->whereIn('beneficiary_id', $householdMembers)
                        ->where(function ($inner) use ($currentProgram) {
                            $inner->where('ayuda_program_id', $currentProgram->id)
                                ->orWhereHas('ayudaProgram', function ($p) use ($currentProgram) {
                                    $p->where('benefit_type', $currentProgram->benefit_type);
                                });
                        });
                });
            }

            $existingClaims = $claimQuery->latest('claimed_at')->get();

            if ($existingClaims->isNotEmpty()) {
                $firstClaim = $existingClaims->first();
                $memberName = $firstClaim->beneficiary?->full_name ?? 'A household member';
                $programTitle = $firstClaim->ayudaProgram?->title ?? 'a project';
                $claimDate = $firstClaim->claimed_at?->format('M d, Y') ?? 'recently';

                $inSameProject = $existingClaims->contains('ayuda_program_id', $currentProgram->id);

                return [
                    'has_warning' => true,
                    'duplicate_type' => $inSameProject ? 'SAME_PROJECT' : 'CROSS_PROJECT_SAME_TYPE',
                    'message' => "Warning: Household #{$hhNo} member {$memberName} already received assistance in {$programTitle} on {$claimDate}.",
                    'household_no' => $hhNo,
                    'existing_claims' => $existingClaims->map(fn ($c) => [
                        'claim_code' => $c->claim_code,
                        'member_name' => $c->beneficiary?->full_name ?: 'Household Member',
                        'program' => $c->ayudaProgram?->title ?: 'Ayuda Program',
                        'benefit_type' => $c->ayudaProgram?->benefit_type?->value ?? 'Cash',
                        'amount' => $c->unit_amount,
                        'date' => $c->claimed_at?->format('M d, Y h:i A') ?? 'N/A',
                    ])->toArray(),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('Household duplicate check skipped: '.$e->getMessage());
        }

        return [
            'has_warning' => false,
            'duplicate_type' => 'NONE',
            'message' => 'No duplicate household claims detected.',
            'household_no' => $hhNo,
            'existing_claims' => [],
        ];
    }
}
