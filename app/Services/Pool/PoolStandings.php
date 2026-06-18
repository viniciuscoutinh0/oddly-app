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
        return $this->awarding($pool, $this->rank($pool));
    }

    private function rank(Pool $pool): Collection
    {
        $ids = $pool->participants->pluck('id');

        $sources = collect($this->sources())->map(
            fn (PointSource $source): Collection => $source->pointsFor($pool, $ids),
        );

        return $pool
            ->participants
            ->map(fn (User $user): UserStanding => new UserStanding(
                id: $user->id,
                name: $user->name,
                initials: $user->initials(),
                points: $sources->sum(fn (Collection $point): int => (int) ($point[$user->id] ?? 0)),
            ))
            ->sortByDesc(fn (UserStanding $standing): int => $standing->points)
            ->values();
    }

    private function awarding(Pool $pool, Collection $ranked): Collection
    {
        $total = $pool->entry_fee * $pool->participants->count();

        $position = 0;

        return $ranked
            ->groupBy(fn (UserStanding $standing): int => $standing->points)
            ->flatMap(function (Collection $group) use ($pool, $total, &$position): Collection {
                $count = $group->count();

                $percentage = collect(range($position, $position + $count - 1))
                    ->sum(fn (int $pos): float => $this->percentageFor($pool, $pos));

                $position += $count;
                $award = (($percentage / 100) * $total) / $count;

                return $group->map(
                    fn (UserStanding $standing): UserStanding => $standing->withAward($award),
                );
            })
            ->values();
    }

    private function percentageFor(Pool $pool, int $position): float
    {
        return (float) ($pool->distributions->firstWhere('position', $position + 1)?->percentage ?? 0);
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
