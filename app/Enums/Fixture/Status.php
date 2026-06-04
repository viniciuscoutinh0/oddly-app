<?php

declare(strict_types=1);

namespace App\Enums\Fixture;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Status: string implements HasColor, HasLabel
{
    use HasCases;

    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Postponed = 'postponed';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return match ($this) {
            self::Scheduled => 'Agendado',
            self::InProgress => 'Em Andamento',
            self::Finished => 'Encerrado',
            self::Postponed => 'Adiado',
            self::Cancelled => 'Cancelado',
        };
    }

    public function getColor(): array
    {
        return match ($this) {
            self::Scheduled => Color::Gray,
            self::InProgress => Color::Amber,
            self::Finished => Color::Green,
            self::Postponed => Color::Orange,
            self::Cancelled => Color::Red,
        };
    }
}
