<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Bet\ResolveChampionBetsAction;
use App\Models\Season;

final class SeasonObserver
{
    public function __construct(private ResolveChampionBetsAction $resolveChampion) {}

    public function saved(Season $season): void
    {
        if (! $season->wasChanged('winner_id')) {
            return;
        }

        $this->resolveChampion->handle($season);
    }
}
