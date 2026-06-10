<?php

declare(strict_types=1);

namespace App\Services\Pool\Standings;

use App\Models\Bet;
use App\Models\Pool;
use App\Services\Pool\Contracts\PointSource;
use Illuminate\Support\Collection;
use Override;

final class BetPoint implements PointSource
{
    #[Override]
    public function pointsFor(Pool $pool, Collection $ids): Collection
    {
        return Bet::query()
            ->selectRaw(
                <<<'SQL'
                    user_id,
                    SUM(CASE
                        WHEN is_exact = 1 THEN ?
                        WHEN is_correct_result = 1 THEN ?
                        ELSE 0 END
                    ) as points
                    SQL,
                [
                    $pool->points_exact,
                    $pool->points_result,
                ],
            )
            ->whereIn('fixture_id', $pool->season->fixtures()->pluck('fixtures.id'))
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->pluck('points', 'user_id');
    }
}
