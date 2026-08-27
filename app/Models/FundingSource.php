<?php

namespace App\Models;

use App\Enums\FundingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FundingSource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'funding_type',
        'title',
        'source_code',
        'project_details_id',
        'office',
        'fiscal_year',
        'allocated_amount',
        'spent_amount',
        'remaining_balance',
        'status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'funding_type' => FundingType::class,
            'allocated_amount' => 'decimal:2',
            'spent_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'fiscal_year' => 'integer',
        ];
    }

    public function ayudaPrograms(): HasMany
    {
        return $this->hasMany(AyudaProgram::class);
    }

    public function ledgerEntries(): HasMany
    {
        return $this->hasMany(BudgetLedgerEntry::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }

    public function goodsDonations(): HasMany
    {
        return $this->hasMany(GoodsDonation::class);
    }
}
