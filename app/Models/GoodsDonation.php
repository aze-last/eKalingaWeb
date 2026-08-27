<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class GoodsDonation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'funding_source_id',
        'reference_no',
        'donor_type',
        'donor_name',
        'contact_no',
        'item_name',
        'quantity',
        'unit',
        'estimated_value',
        'donation_date',
        'proof_attachment',
        'notes',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'quantity' => 'integer',
            'donation_date' => 'date',
        ];
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
