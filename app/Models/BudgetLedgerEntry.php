<?php

namespace App\Models;

use App\Enums\LedgerEntryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLedgerEntry extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'funding_source_id',
        'ayuda_program_id',
        'entry_type',
        'reference_code',
        'amount',
        'item_name',
        'item_quantity',
        'item_unit',
        'previous_balance',
        'new_balance',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'entry_type' => LedgerEntryType::class,
            'amount' => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'new_balance' => 'decimal:2',
            'item_quantity' => 'integer',
        ];
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function ayudaProgram(): BelongsTo
    {
        return $this->belongsTo(AyudaProgram::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
