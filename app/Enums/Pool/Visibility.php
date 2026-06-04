<?php

declare(strict_types=1);

namespace App\Enums\Pool;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Visibility: string implements HasColor, HasLabel
{
    use HasCases;

    case Public = 'public';
    case Private = 'private';

    public function getLabel(): string
    {
        return match ($this) {
            self::Public => 'Público',
            self::Private => 'Privado',
        };
    }

    public function getColor(): array
    {
        return match ($this) {
            self::Public => Color::Green,
            self::Private => Color::Gray,
        };
    }
}
