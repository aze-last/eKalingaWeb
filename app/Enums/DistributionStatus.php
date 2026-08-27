<?php

namespace App\Enums;

enum DistributionStatus: string
{
    case PENDING = 'PENDING';
    case RELEASED = 'RELEASED';
    case UNRELEASED = 'UNRELEASED';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending (Queued)',
            self::RELEASED => 'Released / Claimed',
            self::UNRELEASED => 'Unreleased / Excluded',
        };
    }

    public function badgeClasses(): string
    {
        return match ($this) {
            self::PENDING => 'bg-amber-500/10 text-amber-500 border-amber-500/20',
            self::RELEASED => 'bg-emerald-500/10 text-emerald-500 border-emerald-500/20',
            self::UNRELEASED => 'bg-rose-500/10 text-rose-500 border-rose-500/20',
        };
    }
}
