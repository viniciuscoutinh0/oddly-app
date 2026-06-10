<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\GroupBet;
use App\Models\Season;
use App\Models\User;
use RuntimeException;

final class SyncGroupBetsAction
{
    /**
     * Sync a user's classified-team predictions for a single group.
     * Teams are stored at sequential positions (1..N) following selection order;
     * positions beyond the selected count are removed.
     *
     * @param  array<int, int>  $teamIds  Ordered list of predicted classified team ids.
     */
    public function handle(User $user, Season $season, string $groupLetter, array $teamIds): void
    {
        if ($season->bonusLocked()) {
            throw new RuntimeException('Os palpites bônus estão encerrados.');
        }

        $teamIds = array_values($teamIds);

        foreach ($teamIds as $index => $teamId) {
            GroupBet::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'season_id' => $season->id,
                    'group_letter' => $groupLetter,
                    'predicted_position' => $index + 1,
                ],
                ['team_id' => (int) $teamId],
            );
        }

        GroupBet::query()
            ->where('user_id', $user->id)
            ->where('season_id', $season->id)
            ->where('group_letter', $groupLetter)
            ->where('predicted_position', '>', count($teamIds))
            ->delete();
    }
}
