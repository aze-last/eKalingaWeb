<?php

namespace App\Enums;

enum ProgramStatus: string
{
    case Draft = 'Draft';
    case Active = 'Active';
    case Completed = 'Completed';
    case Cancelled = 'Cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active / In-Distribution',
            self::Completed => 'Completed / Closed',
            self::Cancelled => 'Cancelled',
        };
    }
}
