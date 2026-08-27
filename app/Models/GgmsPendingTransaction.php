<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GgmsPendingTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_uuid',
        'project_code',
        'project_name',
        'payload',
        'retry_count',
        'last_error',
        'status',
        'last_attempted_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'retry_count' => 'integer',
            'last_attempted_at' => 'datetime',
        ];
    }
}
