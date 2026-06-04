<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Season;

final class ResolveChampionBetsAction
{
    public function handle(Season $season): void
    {
        if ($season->winner_id === null) {
            return;
        }

        $season->championBets()->chunkById(200, function ($bets) use ($season): void {
            foreach ($bets as $bet) {
                $bet->is_correct = $bet->team_id === $season->winner_id;
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
