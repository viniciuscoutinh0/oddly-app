<?php

declare(strict_types=1);

namespace App\Services\Pool;

use App\Models\Pool;
use App\Models\User;
use App\Services\Pool\Contracts\PointSource;
use App\Services\Pool\ValueObjects\UserStanding;
use Illuminate\Support\Collection;

final readonly class PoolStandings
{
    public function __construct(
        public Standings\BetPoint $bet,
        public Standings\GroupPoint $group,
        public Standings\ChampionPoint $champion,
    ) {}

    public function for(Pool $pool): Collection
    {
        /** @var Collection $participants */
        $participants = $pool->participants;

        $ids = $participants->pluck('id');

        $sources = collect($this->sources())->map(
            fn (PointSource $source): Collection => $source->pointsFor($pool, $ids),
        );

        return $participants
            ->map(fn (User $user): UserStanding => new UserStanding(
                id: $user->id,
                name: $user->name,
                initials: $user->initials(),
                points: $sources->sum(fn (Collection $point): int => (int) ($point[$user->id] ?? 0)),
            ))
            ->sortByDesc(fn (UserStanding $standing): int => $standing->points)
            ->values();
    }

    private function sources(): array
    {
        return [
            $this->bet,
            $this->group,
            $this->champion,
        ];
    }
}
