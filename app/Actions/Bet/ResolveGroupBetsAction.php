<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Season;

final class ResolveGroupBetsAction
{
    public function handle(Season $season): void
    {
        // Map of "<group_letter>:<position>" => team_id from the actual standings.
        $actual = $season->teams()
            ->wherePivotNotNull('group_position')
            ->get()
            ->mapWithKeys(fn ($team): array => [
                $team->pivot->group_letter.':'.$team->pivot->group_position => $team->id,
            ]);

        $season->groupBets()->chunkById(200, function ($bets) use ($actual): void {
            foreach ($bets as $bet) {
                $key = $bet->group_letter.':'.$bet->predicted_position;
                $bet->is_correct = ($actual[$key] ?? null) === $bet->team_id;
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
