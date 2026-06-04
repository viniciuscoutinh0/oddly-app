<?php

declare(strict_types=1);

namespace App\Enums\Competition;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;

enum Type: string
{
    use HasCases;

    case Cup = 'cup';

    case League = 'league';

    public function label(): string
    {
        return match ($this) {
            self::Cup => 'Copa',
            self::League => 'Liga',
        };
    }

    public function color(): array
    {
        return match ($this) {
            self::Cup => Color::Blue,
            self::League => Color::Green,
        };
    }
}
