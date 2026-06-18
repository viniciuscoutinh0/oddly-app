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
        public ?float $award = null,
    ) {}

    public function withAward(float $value): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            initials: $this->initials,
            points: $this->points,
            award: $value,
        );
    }
}
