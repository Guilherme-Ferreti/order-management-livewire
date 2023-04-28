<?php

declare(strict_types=1);

namespace App\Enums;

enum PasswordStrengthLevel: int
{
    case WEAK = 1;

    case FAIR = 2;

    case GOOD = 3;

    case STRONG = 4;

    public function label(): string
    {
        return match ($this) {
            static::WEAK   => 'Weak',
            static::FAIR   => 'Fair',
            static::GOOD   => 'Good',
            static::STRONG => 'Strong',
        };
    }
}
