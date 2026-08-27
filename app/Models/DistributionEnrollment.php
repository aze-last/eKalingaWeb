<?php

namespace App\Models;

use App\Enums\DistributionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DistributionEnrollment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'ayuda_program_id',
        'beneficiary_id',
        'civil_registry_id',
        'household_no',
        'status',
        'exclusion_reason',
        'enrolled_at',
        'processed_at',
        'processed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => DistributionStatus::class,
            'enrolled_at' => 'datetime',
            'processed_at' => 'datetime',
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

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
