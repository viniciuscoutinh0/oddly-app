<?php

declare(strict_types=1);

namespace App\Enums\Competition;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Type: string implements HasColor, HasLabel
{
    use HasCases;

    case Cup = 'cup';

    case League = 'league';

    public function getLabel(): string
    {
        return match ($this) {
            self::Cup => 'Copa',
            self::League => 'Liga',
        };
    }

    public function getColor(): array
    {
        return match ($this) {
            self::Cup => Color::Blue,
            self::League => Color::Green,
        };
    }
}
