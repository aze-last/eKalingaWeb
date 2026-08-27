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
        'office',
        'fiscal_year',
        'total_allocation',
        'available_balance',
        'sub_allocations',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'total_allocation' => 'decimal:2',
            'available_balance' => 'decimal:2',
            'fiscal_year' => 'integer',
            'sub_allocations' => 'array',
            'last_synced_at' => 'datetime',
        ];
    }
}
