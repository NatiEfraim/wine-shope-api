<?php

namespace App\Enum;

enum StatusEnum:int
{
    //
        case PENDING = 1;
    case APPROVED = 2;
    case DELIVERED = 3;
    case COMPLETED = 4;

    public function name(): string
    {
        return match ($this) {
            self::PENDING => 'pending',
            self::APPROVED => 'approved',
            self::DELIVERED => 'delivered',
            self::COMPLETED => 'completed',
        };
    }

    public static function hebrew(int $id): string
    {
        return match ($id) {
            self::PENDING->value => 'ממתין לאישור',
            self::APPROVED->value => 'מאושר',
            self::DELIVERED->value => 'נשלח',
            self::COMPLETED->value => 'הושלם',
            default => 'לא ידוע',
        };
    }
}
