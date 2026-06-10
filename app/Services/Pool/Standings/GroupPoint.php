<?php

declare(strict_types=1);

namespace App\Services\Pool\Standings;

use App\Models\GroupBet;
use App\Models\Pool;
use App\Services\Pool\Contracts\PointSource;
use Illuminate\Support\Collection;
use Override;

final class GroupPoint implements PointSource
{
    #[Override]
    public function pointsFor(Pool $pool, Collection $ids): Collection
    {
        return GroupBet::query()
            ->where('season_id', $pool->season_id)
            ->whereIn('user_id', $ids)
            ->where('is_correct', true)
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) * ? as points', [$pool->points_group_position])
            ->pluck('points', 'user_id');

    }
}
