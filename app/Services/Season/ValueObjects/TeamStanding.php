<?php

declare(strict_types=1);

namespace App\Services\Season\ValueObjects;

final readonly class TeamStanding
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
        public ?string $logoUrl,
        public string $groupLetter,
        public int $groupPosition,
        public int $matchesPlayed,
        public int $wins,
        public int $draws,
        public int $losses,
        public int $goalsFor,
        public int $goalsAgainst,
        public int $goalDifference,
        public int $points,
    ) {}
}
