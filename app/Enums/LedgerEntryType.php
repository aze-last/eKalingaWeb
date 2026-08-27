<?php

namespace App\Enums;

enum LedgerEntryType: string
{
    case Donation = 'Donation';
    case GoodsDonation = 'GoodsDonation';
    case Release = 'Release';
    case Reallocation = 'Reallocation';

    public function label(): string
    {
        return match ($this) {
            self::Donation => 'Cash Donation Received',
            self::GoodsDonation => 'Goods Donation Received',
            self::Release => 'Disbursement / Release',
            self::Reallocation => 'Earmark Reallocation',
        };
    }
}
