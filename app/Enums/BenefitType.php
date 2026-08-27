<?php

namespace App\Enums;

enum BenefitType: string
{
    case Cash = 'Cash';
    case Goods = 'Goods';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash Assistance',
            self::Goods => 'Goods / In-Kind Items',
        };
    }
}
