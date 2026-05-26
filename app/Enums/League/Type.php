<?php

declare(strict_types=1);

namespace App\Enums\League;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;

enum Type: string
{
    use HasCases;

    case Tournament = 'tournament';

    case League = 'league';

    public function label(): string
    {
        return match ($this) {
            self::Tournament => 'Torneio',
            self::League => 'Liga',
        };
    }

    public function color(): array
    {
        return match ($this) {
            self::Tournament => Color::Blue,
            self::League => Color::Green,
        };
    }
}
