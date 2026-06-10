<?php

declare(strict_types=1);

namespace App\Enums\Pool;

use App\Enums\Concerns\HasCases;
use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum Visibility: string implements HasColor, HasLabel, HasIcon
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

    public function getIcon(): string
    {
        return match ($this) {
            self::Public => 'globe-alt',
            self::Private => 'lock-closed',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Public => 'Qualquer usuário pode ver e participar',
            self::Private => 'Disponível apenas via link ou convite',
        };
    }
}
