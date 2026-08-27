<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentBudgetSnapshot extends Model
{
    use HasFactory;

    protected $table = 'government_budget_snapshots';

    protected $fillable = [
        'office_code',
        'fiscal_year',
        'allocated_amount',
        'computed_spent_amount',
        'remaining_balance',
        'source_table',
        'raw_payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'computed_spent_amount' => 'decimal:2',
            'remaining_balance' => 'decimal:2',
            'raw_payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
