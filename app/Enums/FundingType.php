<?php

namespace App\Enums;

enum FundingType: string
{
    case Government = 'Government';
    case Private = 'Private';

    public function label(): string
    {
        return match ($this) {
            self::Government => 'Government (GGMS)',
            self::Private => 'Private Donation',
        };
    }
}
