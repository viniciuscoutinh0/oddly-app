<?php

declare(strict_types=1);

namespace App\Enums\Fixture;

use App\Enums\Concerns\HasCases;
use Filament\Support\Contracts\HasLabel;

enum Duration: string implements HasLabel
{
    use HasCases;

    case Regular = 'regular';
    case ExtraTime = 'extra_time';
    case Penalties = 'penalties';

    public function getLabel(): string
    {
        return match ($this) {
            self::Regular => 'Tempo Normal',
            self::ExtraTime => 'Prorrogação',
            self::Penalties => 'Pênaltis',
        };
    }
}
