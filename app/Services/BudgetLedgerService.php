<?php

namespace App\Services;

use App\Enums\BenefitType;
use App\Enums\FundingType;
use App\Enums\LedgerEntryType;
use App\Enums\ProgramStatus;
use App\Models\AyudaProgram;
use App\Models\BudgetLedgerEntry;
use App\Models\Donation;
use App\Models\FundingSource;
use App\Models\GoodsDonation;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BudgetLedgerService
{
    /**
     * Record a cash donation and append a ledger entry.
     */
    public function recordCashDonation(array $data, ?int $userId = null): Donation
    {
        return DB::transaction(function () use ($data, $userId) {
            $fundingSource = FundingSource::firstOrCreate(
                [
                    'funding_type' => FundingType::Private,
                    'source_code' => 'DON-CASH-'.date('Y'),
                ],
                [
                    'title' => 'Private Cash Donations Pool '.date('Y'),
                    'office' => 'MSWDO / Treasury',
                    'fiscal_year' => (int) date('Y'),
                    'allocated_amount' => 0.00,
                    'spent_amount' => 0.00,
                    'remaining_balance' => 0.00,
                    'status' => 'Active',
                ]
            );

            $refNo = 'DON-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
            $amount = (float) $data['amount'];
            $previousBalance = (float) $fundingSource->remaining_balance;
            $newBalance = $previousBalance + $amount;

            $fundingSource->allocated_amount += $amount;
            $fundingSource->remaining_balance = $newBalance;
            $fundingSource->save();

            $donation = Donation::create([
                'funding_source_id' => $fundingSource->id,
                'reference_no' => $refNo,
                'donor_type' => $data['donor_type'] ?? 'Person',
                'donor_name' => $data['donor_name'],
                'contact_no' => $data['contact_no'] ?? null,
                'email' => $data['email'] ?? null,
                'amount' => $amount,
                'donation_date' => $data['donation_date'] ?? now()->toDateString(),
                'proof_attachment' => $data['proof_attachment'] ?? null,
                'notes' => $data['notes'] ?? null,
                'received_by' => $userId ?? Auth::id(),
            ]);

            BudgetLedgerEntry::create([
                'funding_source_id' => $fundingSource->id,
                'ayuda_program_id' => null,
                'entry_type' => LedgerEntryType::Donation,
                'reference_code' => $refNo,
                'amount' => $amount,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'notes' => "Cash Donation received from {$donation->donor_name}",
                'created_by' => $userId ?? Auth::id(),
            ]);

            AuditService::log(
                action: 'Donation',
                module: 'Budget',
                description: 'Recorded private cash donation of ₱'.number_format($amount, 2)." from {$donation->donor_name}",
                subjectType: Donation::class,
                subjectId: $donation->id,
                details: ['amount' => $amount, 'reference_no' => $refNo]
            );

            return $donation;
        });
    }

    /**
     * Record a goods donation and append a ledger entry.
     */
    public function recordGoodsDonation(array $data, ?int $userId = null): GoodsDonation
    {
        return DB::transaction(function () use ($data, $userId) {
            $fundingSource = FundingSource::firstOrCreate(
                [
                    'funding_type' => FundingType::Private,
                    'source_code' => 'DON-GOODS-'.date('Y'),
                ],
                [
                    'title' => 'Private In-Kind & Goods Donations Pool '.date('Y'),
                    'office' => 'MSWDO / Warehouse',
                    'fiscal_year' => (int) date('Y'),
                    'allocated_amount' => 0.00,
                    'spent_amount' => 0.00,
                    'remaining_balance' => 0.00,
                    'status' => 'Active',
                ]
            );

            $refNo = 'GOODS-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));
            $estimatedValue = (float) ($data['estimated_value'] ?? 0.00);
            $previousBalance = (float) $fundingSource->remaining_balance;
            $newBalance = $previousBalance + $estimatedValue;

            $fundingSource->allocated_amount += $estimatedValue;
            $fundingSource->remaining_balance = $newBalance;
            $fundingSource->save();

            $goodsDonation = GoodsDonation::create([
                'funding_source_id' => $fundingSource->id,
                'reference_no' => $refNo,
                'donor_type' => $data['donor_type'] ?? 'Organization',
                'donor_name' => $data['donor_name'],
                'contact_no' => $data['contact_no'] ?? null,
                'item_name' => $data['item_name'],
                'quantity' => (int) $data['quantity'],
                'unit' => $data['unit'] ?? 'Pieces',
                'estimated_value' => $estimatedValue,
                'donation_date' => $data['donation_date'] ?? now()->toDateString(),
                'proof_attachment' => $data['proof_attachment'] ?? null,
                'notes' => $data['notes'] ?? null,
                'received_by' => $userId ?? Auth::id(),
            ]);

            BudgetLedgerEntry::create([
                'funding_source_id' => $fundingSource->id,
                'ayuda_program_id' => null,
                'entry_type' => LedgerEntryType::GoodsDonation,
                'reference_code' => $refNo,
                'amount' => $estimatedValue,
                'item_name' => $goodsDonation->item_name,
                'item_quantity' => $goodsDonation->quantity,
                'item_unit' => $goodsDonation->unit,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'notes' => "Goods donation received: {$goodsDonation->quantity} {$goodsDonation->unit} of {$goodsDonation->item_name} from {$goodsDonation->donor_name}",
                'created_by' => $userId ?? Auth::id(),
            ]);

            AuditService::log(
                action: 'GoodsDonation',
                module: 'Budget',
                description: "Recorded goods donation ({$goodsDonation->quantity} {$goodsDonation->unit} of {$goodsDonation->item_name}) from {$goodsDonation->donor_name}",
                subjectType: GoodsDonation::class,
                subjectId: $goodsDonation->id
            );

            return $goodsDonation;
        });
    }

    /**
     * Create an AyudaProgram project linked 1:1 to a funding source.
     */
    public function createAyudaProgram(array $data, ?int $userId = null): AyudaProgram
    {
        return DB::transaction(function () use ($data, $userId) {
            $fundingSource = FundingSource::lockForUpdate()->findOrFail($data['funding_source_id']);
            $budgetCap = (float) $data['budget_cap'];

            if ($budgetCap > (float) $fundingSource->remaining_balance) {
                throw new Exception('Requested budget cap (₱'.number_format($budgetCap, 2).') exceeds remaining funding balance (₱'.number_format((float) $fundingSource->remaining_balance, 2).').');
            }

            // Generate deterministic AMS-PD code
            $count = AyudaProgram::withTrashed()->count() + 1;
            $programCode = sprintf('AMS-PD-%06d', $count);

            // Deduct earmarked budget from funding source available balance
            $fundingSource->remaining_balance -= $budgetCap;
            $fundingSource->save();

            $program = AyudaProgram::create([
                'funding_source_id' => $fundingSource->id,
                'program_code' => $programCode,
                'title' => $data['title'],
                'benefit_type' => $data['benefit_type'] ?? BenefitType::Cash->value,
                'budget_cap' => $budgetCap,
                'unit_amount' => (float) ($data['unit_amount'] ?? 0.00),
                'item_name' => $data['item_name'] ?? null,
                'item_unit' => $data['item_unit'] ?? null,
                'item_quantity_per_beneficiary' => (int) ($data['item_quantity_per_beneficiary'] ?? 1),
                'target_beneficiaries' => (int) ($data['target_beneficiaries'] ?? 0),
                'target_barangay' => $data['target_barangay'] ?? null,
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? null,
                'description' => $data['description'] ?? null,
                'status' => ProgramStatus::Active,
                'created_by' => $userId ?? Auth::id(),
            ]);

            AuditService::log(
                action: 'Create',
                module: 'Budget',
                description: "Created Ayuda Project {$program->program_code} ({$program->title}) with budget cap ₱".number_format($budgetCap, 2),
                subjectType: AyudaProgram::class,
                subjectId: $program->id
            );

            return $program;
        });
    }

    /**
     * Record a financial release against a project's funding source.
     */
    public function recordRelease(AyudaProgram $program, float $amount, string $referenceCode, ?string $itemName = null, ?int $itemQty = 1, ?string $itemUnit = null, ?int $userId = null): BudgetLedgerEntry
    {
        $itemQty = $itemQty ?: 1;
        // Enforce cumulative spend <= budget_cap inside locked transaction
        $lockedProgram = AyudaProgram::lockForUpdate()->findOrFail($program->id);
        $cumulativeSpend = (float) $lockedProgram->total_disbursed_amount + $amount;

        if ($cumulativeSpend > (float) $lockedProgram->budget_cap) {
            throw new Exception('Release amount (₱'.number_format($amount, 2).') would exceed program budget cap (₱'.number_format((float) $lockedProgram->budget_cap, 2).').');
        }

        $fundingSource = FundingSource::lockForUpdate()->findOrFail($lockedProgram->funding_source_id);
        $previousBalance = (float) $fundingSource->remaining_balance;

        // Update funding source spent amount
        $fundingSource->spent_amount += $amount;
        $fundingSource->save();

        // Update program disbursed metrics
        $lockedProgram->total_disbursed_amount += $amount;
        $lockedProgram->total_claimed_count += 1;
        $lockedProgram->save();

        $entry = BudgetLedgerEntry::create([
            'funding_source_id' => $fundingSource->id,
            'ayuda_program_id' => $lockedProgram->id,
            'entry_type' => LedgerEntryType::Release,
            'reference_code' => $referenceCode,
            'amount' => $amount,
            'item_name' => $itemName,
            'item_quantity' => $itemQty,
            'item_unit' => $itemUnit,
            'previous_balance' => $previousBalance,
            'new_balance' => $previousBalance, // Earmark already accounted at creation
            'notes' => "Ayuda release for {$lockedProgram->program_code} - Ref: {$referenceCode}",
            'created_by' => $userId ?? Auth::id(),
        ]);

        return $entry;
    }

    /**
     * Reallocate remaining unspent funds from a completed/dormant project back to the funding source.
     */
    public function reallocateEarmark(AyudaProgram $program, ?int $userId = null): void
    {
        DB::transaction(function () use ($program, $userId) {
            $lockedProgram = AyudaProgram::lockForUpdate()->findOrFail($program->id);
            $unspent = (float) $lockedProgram->budget_cap - (float) $lockedProgram->total_disbursed_amount;

            if ($unspent <= 0) {
                return;
            }

            $fundingSource = FundingSource::lockForUpdate()->findOrFail($lockedProgram->funding_source_id);
            $previousBalance = (float) $fundingSource->remaining_balance;
            $newBalance = $previousBalance + $unspent;

            $fundingSource->remaining_balance = $newBalance;
            $fundingSource->save();

            // Adjust program cap to match actual spend and close program
            $lockedProgram->budget_cap = $lockedProgram->total_disbursed_amount;
            $lockedProgram->status = ProgramStatus::Completed;
            $lockedProgram->save();

            $refNo = 'REALLOC-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -5));

            BudgetLedgerEntry::create([
                'funding_source_id' => $fundingSource->id,
                'ayuda_program_id' => $lockedProgram->id,
                'entry_type' => LedgerEntryType::Reallocation,
                'reference_code' => $refNo,
                'amount' => $unspent,
                'previous_balance' => $previousBalance,
                'new_balance' => $newBalance,
                'notes' => 'Reallocated unspent funds of ₱'.number_format($unspent, 2)." from {$lockedProgram->program_code} back to {$fundingSource->source_code}",
                'created_by' => $userId ?? Auth::id(),
            ]);

            AuditService::log(
                action: 'Reallocation',
                module: 'Budget',
                description: 'Reallocated unspent ₱'.number_format($unspent, 2)." from {$lockedProgram->program_code} back to {$fundingSource->source_code}",
                subjectType: AyudaProgram::class,
                subjectId: $lockedProgram->id
            );
        });
    }
}
