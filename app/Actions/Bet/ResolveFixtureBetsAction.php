<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Fixture;

final class ResolveFixtureBetsAction
{
    public function handle(Fixture $fixture): void
    {
        if (! $fixture->isFinished() || $fixture->home_score === null || $fixture->away_score === null) {
            return;
        }

        $resultSign = $fixture->home_score <=> $fixture->away_score;

        $fixture->bets()->chunkById(200, function ($bets) use ($fixture, $resultSign): void {
            foreach ($bets as $bet) {
                $bet->is_exact = $bet->home_score === $fixture->home_score
                    && $bet->away_score === $fixture->away_score;
                $bet->is_correct_result = ($bet->home_score <=> $bet->away_score) === $resultSign;
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
