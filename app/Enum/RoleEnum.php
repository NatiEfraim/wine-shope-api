<?php

namespace App\Enum;

enum RoleEnum:int
{
    //
    case ADMIN = 1;
    case MODERATOR = 2;
    case USER = 3;

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'מנהל מערכת',
            self::MODERATOR => 'עורך מערכת',
            self::USER => 'משתמש',
        };
    }

    public static function labelFromId(int $id): string
    {
        return self::tryFrom($id)?->label() ?? $id;
    }
}
