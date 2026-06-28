<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Season;
use App\Models\Team;
use Illuminate\Support\Collection;

final class ResolveGroupBetsAction
{
    public function handle(Season $season): void
    {
        $standings = $season->teams()
            ->wherePivotNotNull('group_position')
            ->get()
            ->groupBy(fn (Team $team): string => $team->pivot->group_letter)
            ->map(fn (Collection $teams): Collection => $teams->map(fn (Team $team): string => $team->pivot->group_letter. ':'. $team->id)->take(2))
            ->toArray();

        $season->groupBets()->chunkById(200, function ($bets) use ($standings): void {
            foreach ($bets as $bet) {
                $predicted = $bet->group_letter.':'.$bet->team_id;

                $bet->is_correct = in_array($predicted, $standings[$bet->group_letter] ?? [], true);
                $bet->resolved_at = now();
                $bet->save();
            }
        });
    }
}
