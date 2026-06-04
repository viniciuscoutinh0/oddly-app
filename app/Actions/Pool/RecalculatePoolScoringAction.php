<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Actions\Bet\ResolveChampionBetsAction;
use App\Actions\Bet\ResolveFixtureBetsAction;
use App\Actions\Bet\ResolveGroupBetsAction;
use App\Enums\Fixture\Status;
use App\Models\Fixture;
use App\Models\Pool;
use Illuminate\Support\Facades\DB;

final class RecalculatePoolScoringAction
{
    public function __construct(
        private ResolveFixtureBetsAction $resolveFixtureBets,
        private ResolveChampionBetsAction $resolveChampionBets,
        private ResolveGroupBetsAction $resolveGroupBets,
    ) {}

    public function handle(Pool $pool): void
    {
        $pool->loadMissing('season');
        $season = $pool->season;

        DB::transaction(function () use ($season): void {
            $season->fixtures()
                ->where('status', Status::Finished)
                ->each(function (Fixture $fixture): void {
                    $this->resolveFixtureBets->handle($fixture);
                });

            $this->resolveChampionBets->handle($season);
            $this->resolveGroupBets->handle($season);
        });
    }
}
