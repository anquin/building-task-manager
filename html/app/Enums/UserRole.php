<?php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case EMPLOYEE = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::EMPLOYEE => 'Employee',
        };
    }
}
