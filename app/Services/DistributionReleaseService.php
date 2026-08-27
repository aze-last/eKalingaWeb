<?php

namespace App\Services;

use App\Enums\BenefitType;
use App\Enums\DistributionStatus;
use App\Models\AyudaProgram;
use App\Models\AyudaProjectClaim;
use App\Models\Beneficiary;
use App\Models\DistributionEnrollment;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DistributionReleaseService
{
    public function __construct(
        protected BudgetLedgerService $budgetLedgerService,
        protected GgmsTransactionService $ggmsTransactionService,
        protected HouseholdVerificationService $householdVerificationService
    ) {}

    /**
     * Atomically process a claim release for a beneficiary in an AyudaProgram.
     *
     * @return array{success: bool, claim: AyudaProjectClaim, message: string}
     */
    public function processRelease(
        AyudaProgram $program,
        Beneficiary $beneficiary,
        string $verificationMethod = 'QR_SCAN',
        ?string $qrPayload = null,
        ?int $userId = null
    ): array {
        return DB::transaction(function () use ($program, $beneficiary, $verificationMethod, $qrPayload, $userId) {
            $enrollment = DistributionEnrollment::where('ayuda_program_id', $program->id)
                ->where('beneficiary_id', $beneficiary->id)
                ->lockForUpdate()
                ->first();

            if (! $enrollment) {
                // Auto-enroll if not previously enrolled (allows walk-in eligible masterlist beneficiaries)
                $enrollment = DistributionEnrollment::create([
                    'ayuda_program_id' => $program->id,
                    'beneficiary_id' => $beneficiary->id,
                    'status' => DistributionStatus::PENDING,
                    'enrolled_at' => now(),
                ]);
            }

            if ($enrollment->status === DistributionStatus::RELEASED) {
                throw new Exception("Beneficiary {$beneficiary->full_name} has already claimed this assistance.");
            }

            $claimCode = 'CLM-'.date('Y').'-'.sprintf('%06d', AyudaProjectClaim::count() + 1);
            $amount = (float) $program->unit_amount;
            $benefitType = $program->benefit_type->value;
            $itemDetails = null;

            if ($program->benefit_type === BenefitType::Goods) {
                $itemDetails = "{$program->item_quantity_per_beneficiary} {$program->item_unit} of {$program->item_name}";
            }

            // 1. Create immutable AyudaProjectClaim
            $claim = AyudaProjectClaim::create([
                'ayuda_program_id' => $program->id,
                'beneficiary_id' => $beneficiary->id,
                'civil_registry_id' => $beneficiary->civil_registry_id,
                'household_no' => $beneficiary->household_no,
                'user_id' => $userId ?? Auth::id(),
                'claim_code' => $claimCode,
                'unit_amount' => $amount,
                'item_details' => $itemDetails,
                'scanned_qr_payload' => $qrPayload,
                'verification_method' => $verificationMethod,
                'claimed_at' => now(),
            ]);

            // 2. Write BudgetLedgerEntry against program envelope
            $this->budgetLedgerService->recordRelease(
                program: $program,
                amount: $amount,
                referenceCode: $claimCode,
                itemName: $program->item_name,
                itemQty: (int) ($program->item_quantity_per_beneficiary ?: 1),
                itemUnit: $program->item_unit,
                userId: $userId
            );

            // 3. Write GGMS Consolidated Transaction record
            $this->ggmsTransactionService->recordRelease(
                program: $program,
                beneficiary: $beneficiary,
                amount: $amount,
                benefitType: $benefitType,
                itemSummary: $itemDetails,
                userId: $userId
            );

            // 4. Update DistributionEnrollment state to RELEASED
            $enrollment->update([
                'status' => DistributionStatus::RELEASED,
                'processed_at' => now(),
                'processed_by' => $userId ?? Auth::id(),
            ]);

            AuditService::log(
                action: 'Release',
                module: 'Distribution',
                description: "Released assistance ({$claimCode}) to {$beneficiary->full_name} under {$program->program_code}",
                subjectType: AyudaProjectClaim::class,
                subjectId: $claim->id,
                details: ['claim_code' => $claimCode, 'amount' => $amount, 'beneficiary' => $beneficiary->full_name]
            );

            return [
                'success' => true,
                'claim' => $claim,
                'message' => "Successfully released assistance to {$beneficiary->full_name}",
            ];
        });
    }

    /**
     * Move enrollment status (e.g. PENDING -> UNRELEASED, UNRELEASED -> PENDING).
     */
    public function updateStatus(
        DistributionEnrollment $enrollment,
        DistributionStatus $newStatus,
        ?string $reason = null,
        ?int $userId = null
    ): DistributionEnrollment {
        $enrollment->update([
            'status' => $newStatus,
            'exclusion_reason' => $reason,
            'processed_at' => now(),
            'processed_by' => $userId ?? Auth::id(),
        ]);

        AuditService::log(
            action: 'StatusChange',
            module: 'Distribution',
            description: "Moved beneficiary status to {$newStatus->value} in project #{$enrollment->ayuda_program_id}",
            subjectType: DistributionEnrollment::class,
            subjectId: $enrollment->id
        );

        return $enrollment;
    }
}
