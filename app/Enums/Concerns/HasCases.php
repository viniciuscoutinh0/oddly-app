<?php

declare(strict_types=1);

namespace App\Enums\Concerns;

use BackedEnum;

trait HasCases
{
    public static function all(): array
    {
        return collect(static::cases())
            ->mapWithKeys(fn (BackedEnum $enum): array => [
                $enum->value => method_exists($enum, 'label') ? $enum->label() : $enum->name,
            ])
            ->all();
    }
}
