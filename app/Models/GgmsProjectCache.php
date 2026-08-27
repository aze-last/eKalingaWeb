<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GgmsProjectCache extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_details_id',
        'title',
        'description',
        'office',
        'fiscal_year',
        'total_allocation',
        'allocated_budget',
        'spent_budget',
        'available_balance',
        'voucher_code',
        'status',
        'sub_allocations',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'total_allocation' => 'decimal:2',
            'allocated_budget' => 'decimal:2',
            'spent_budget' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'fiscal_year' => 'integer',
            'sub_allocations' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }

    public function getProjectCodeAttribute(): string
    {
        return (string) $this->project_details_id;
    }

    public function getOfficeCodeAttribute(): string
    {
        return (string) ($this->office ?: 'OFF-2026-0006');
    }
}
