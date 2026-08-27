<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AyudaProjectClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ayuda_program_id',
        'beneficiary_id',
        'civil_registry_id',
        'household_no',
        'user_id',
        'claim_code',
        'unit_amount',
        'item_details',
        'scanned_qr_payload',
        'verification_method',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_amount' => 'decimal:2',
            'claimed_at' => 'datetime',
        ];
    }

    public function ayudaProgram(): BelongsTo
    {
        return $this->belongsTo(AyudaProgram::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class);
    }

    public function releasingOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
