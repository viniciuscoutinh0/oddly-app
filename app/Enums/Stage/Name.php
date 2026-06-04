<?php

declare(strict_types=1);

namespace App\Enums\Stage;

use App\Enums\Concerns\HasCases;
use Filament\Support\Contracts\HasLabel;

enum Name: string implements HasLabel
{
    use HasCases;

    case GroupStage = 'group_stage';
    case LastThirtyTwo = 'last_32';
    case LastSixteen = 'last_16';
    case QuarterFinals = 'quarter_finals';
    case SemiFinals = 'semi_finals';
    case ThirdPlace = 'third_place';
    case Final = 'final';

    public function getLabel(): string
    {
        return match ($this) {
            self::GroupStage => 'Fase de Grupos',
            self::LastThirtyTwo => '16-avos de Final',
            self::LastSixteen => 'Oitavas de Final',
            self::QuarterFinals => 'Quartas de Final',
            self::SemiFinals => 'Semifinais',
            self::ThirdPlace => 'Disputa de 3º Lugar',
            self::Final => 'Final',
        };
    }

    public function isKnockout(): bool
    {
        return $this !== self::GroupStage;
    }
}
