<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GgmsConsolidatedTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_code',
        'project_details_id',
        'project_name',
        'beneficiary_id',
        'civil_registry_id',
        'first_name',
        'middle_name',
        'last_name',
        'barangay',
        'household_no',
        'amount',
        'benefit_type',
        'item_summary',
        'disbursement_date',
        'sync_status',
        'payload',
        'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'disbursement_date' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
