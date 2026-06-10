<?php

declare(strict_types=1);

namespace App\Services\Pool\ValueObjects;

final readonly class UserStanding
{
    public function __construct(
        public int $id,
        public string $name,
        public string $initials,
        public int $points,
    ) {}
}
