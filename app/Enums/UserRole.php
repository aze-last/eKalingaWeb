<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'SuperAdmin';
    case Admin = 'Admin';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Administrator',
            self::Admin => 'Administrator',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this === self::SuperAdmin;
    }
}
