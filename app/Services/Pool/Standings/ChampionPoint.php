<?php

declare(strict_types=1);

namespace App\Services\Pool\Standings;

use App\Models\ChampionBet;
use App\Models\Pool;
use App\Services\Pool\Contracts\PointSource;
use Illuminate\Support\Collection;
use Override;

final class ChampionPoint implements PointSource
{
    #[Override]
    public function pointsFor(Pool $pool, Collection $ids): Collection
    {
        return ChampionBet::query()
            ->where('season_id', $pool->season_id)
            ->whereIn('user_id', $ids)
            ->where('is_correct', true)
            ->pluck('user_id')
            ->mapWithKeys(fn (int $userId): array => [$userId => $pool->points_champion]);
    }
}
