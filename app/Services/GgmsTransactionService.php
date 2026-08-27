<?php

namespace App\Services;

use App\Models\AyudaProgram;
use App\Models\Beneficiary;
use App\Models\GgmsConsolidatedTransaction;
use App\Models\GgmsPendingTransaction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GgmsTransactionService
{
    /**
     * Record a disbursement transaction in GGMS consolidated transactions table.
     */
    public function recordRelease(
        AyudaProgram $program,
        Beneficiary $beneficiary,
        float $amount,
        string $benefitType = 'Cash',
        ?string $itemSummary = null,
        ?int $userId = null
    ): GgmsConsolidatedTransaction {
        $projectDetailsId = $program->fundingSource?->project_details_id;

        $payload = [
            'project_code' => $program->program_code,
            'project_details_id' => $projectDetailsId,
            'project_name' => 'Project Distribution',
            'beneficiary_id' => $beneficiary->id,
            'civil_registry_id' => $beneficiary->civil_registry_id,
            'first_name' => $beneficiary->first_name,
            'middle_name' => $beneficiary->middle_name,
            'last_name' => $beneficiary->last_name,
            'barangay' => $beneficiary->barangay,
            'household_no' => $beneficiary->household_no,
            'amount' => $amount,
            'benefit_type' => $benefitType,
            'item_summary' => $itemSummary,
            'disbursement_date' => now()->toDateTimeString(),
            'recorded_by' => $userId ?? Auth::id(),
        ];

        try {
            return GgmsConsolidatedTransaction::create([
                'project_code' => $program->program_code,
                'project_details_id' => $projectDetailsId,
                'project_name' => 'Project Distribution',
                'beneficiary_id' => $beneficiary->id,
                'civil_registry_id' => $beneficiary->civil_registry_id,
                'first_name' => $beneficiary->first_name,
                'middle_name' => $beneficiary->middle_name,
                'last_name' => $beneficiary->last_name,
                'barangay' => $beneficiary->barangay,
                'household_no' => $beneficiary->household_no,
                'amount' => $amount,
                'benefit_type' => $benefitType,
                'item_summary' => $itemSummary,
                'disbursement_date' => now(),
                'sync_status' => 'Synced',
                'payload' => $payload,
                'recorded_by' => $userId ?? Auth::id(),
            ]);
        } catch (Exception $e) {
            // Queue into pending retry store for offline resilience
            GgmsPendingTransaction::create([
                'transaction_uuid' => (string) Str::uuid(),
                'project_code' => $program->program_code,
                'project_name' => 'Project Distribution',
                'payload' => $payload,
                'last_error' => $e->getMessage(),
                'status' => 'Pending',
            ]);

            throw $e;
        }
    }

    /**
     * Flush and replay pending transactions from the retry dead-letter queue.
     */
    public function flushPendingTransactions(): int
    {
        $pending = GgmsPendingTransaction::where('status', 'Pending')->get();
        $flushedCount = 0;

        foreach ($pending as $item) {
            try {
                DB::transaction(function () use ($item, &$flushedCount) {
                    $payload = $item->payload;

                    GgmsConsolidatedTransaction::create([
                        'project_code' => $payload['project_code'],
                        'project_details_id' => $payload['project_details_id'] ?? null,
                        'project_name' => $payload['project_name'] ?? 'Project Distribution',
                        'beneficiary_id' => $payload['beneficiary_id'] ?? null,
                        'civil_registry_id' => $payload['civil_registry_id'] ?? null,
                        'first_name' => $payload['first_name'] ?? 'Unknown',
                        'middle_name' => $payload['middle_name'] ?? null,
                        'last_name' => $payload['last_name'] ?? 'Unknown',
                        'barangay' => $payload['barangay'] ?? 'Poblacion',
                        'household_no' => $payload['household_no'] ?? 'N/A',
                        'amount' => (float) ($payload['amount'] ?? 0.00),
                        'benefit_type' => $payload['benefit_type'] ?? 'Cash',
                        'item_summary' => $payload['item_summary'] ?? null,
                        'disbursement_date' => $payload['disbursement_date'] ?? now(),
                        'sync_status' => 'Synced',
                        'payload' => $payload,
                        'recorded_by' => $payload['recorded_by'] ?? null,
                    ]);

                    $item->update(['status' => 'Completed']);
                    $flushedCount++;
                });
            } catch (Exception $e) {
                $item->increment('retry_count');
                $item->update([
                    'last_error' => $e->getMessage(),
                    'last_attempted_at' => now(),
                ]);
            }
        }

        return $flushedCount;
    }
}
