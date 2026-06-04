<?php

declare(strict_types=1);

namespace App\Enums\User;

use App\Enums\Concerns\HasCases;
use Filament\Support\Contracts\HasLabel;

enum Role: string implements HasLabel
{
    use HasCases;

    case Admin = 'admin';
    case Player = 'player';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Player => 'Jogador',
        };
    }
}
