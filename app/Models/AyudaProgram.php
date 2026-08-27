<?php

namespace App\Models;

use App\Enums\BenefitType;
use App\Enums\ProgramStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AyudaProgram extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'funding_source_id',
        'source_project_details_id',
        'source_donation_id',
        'source_ggms_budget_id',
        'program_code',
        'title',
        'benefit_type',
        'budget_cap',
        'unit_amount',
        'item_name',
        'item_unit',
        'item_quantity_per_beneficiary',
        'target_beneficiaries',
        'total_disbursed_amount',
        'total_claimed_count',
        'status',
        'target_barangay',
        'start_date',
        'end_date',
        'description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'benefit_type' => BenefitType::class,
            'status' => ProgramStatus::class,
            'budget_cap' => 'decimal:2',
            'unit_amount' => 'decimal:2',
            'total_disbursed_amount' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'item_quantity_per_beneficiary' => 'integer',
            'target_beneficiaries' => 'integer',
            'total_claimed_count' => 'integer',
        ];
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class, 'source_donation_id');
    }

    public function ggmsSnapshot(): BelongsTo
    {
        return $this->belongsTo(GovernmentBudgetSnapshot::class, 'source_ggms_budget_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(DistributionEnrollment::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(AyudaProjectClaim::class);
    }

    /**
     * Resolves fund source code display priority:
     * 1. Linked GGMS Project Details ID
     * 2. Private Donation
     * 3. General GGMS Budget / Linked Funding Source
     * 4. Fallback to "General Fund"
     */
    protected function fundSourceCode(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->source_project_details_id) {
                    return $this->source_project_details_id;
                }
                if ($this->source_donation_id && $this->donation) {
                    return $this->donation->donation_code ?? "DON-{$this->source_donation_id}";
                }
                if ($this->fundingSource) {
                    return $this->fundingSource->source_code;
                }

                return 'General Fund';
            }
        );
    }

    public function getRemainingBalanceAttribute(): float
    {
        return max(0.00, (float) $this->budget_cap - (float) $this->total_disbursed_amount);
    }
}
