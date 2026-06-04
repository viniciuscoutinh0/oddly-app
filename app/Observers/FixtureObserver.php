<?php

declare(strict_types=1);

namespace App\Observers;

use App\Actions\Bet\ResolveFixtureBetsAction;
use App\Models\Fixture;

final class FixtureObserver
{
    public function __construct(private ResolveFixtureBetsAction $resolveBets) {}

    public function saved(Fixture $fixture): void
    {
        if (! $fixture->wasChanged(['status', 'home_score', 'away_score'])) {
            return;
        }

        $this->resolveBets->handle($fixture);
    }
}
