<?php

namespace Tests\Models\Enums;

enum ETestEnum: string
{
    case FIRST = 'first';
    case SECOND = 'second';
    case THIRD = 'third';

    /**
     * @return array
     */
    public static function values(): array
    {
        return array_map(fn ($enum) => $enum->value, self::cases());
    }
}
