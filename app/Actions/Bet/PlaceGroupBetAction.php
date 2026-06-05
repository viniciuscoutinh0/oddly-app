<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\GroupBet;
use App\Models\Season;
use App\Models\User;
use RuntimeException;

final class PlaceGroupBetAction
{
    /**
     * @param  array<int, int>  $positions  Map of predicted_position => team_id.
     */
    public function handle(User $user, Season $season, string $groupLetter, array $positions): void
    {
        if ($season->bonusLocked()) {
            throw new RuntimeException('Os palpites bônus estão encerrados.');
        }

        foreach ($positions as $position => $teamId) {
            GroupBet::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'season_id' => $season->id,
                    'group_letter' => $groupLetter,
                    'predicted_position' => $position,
                ],
                ['team_id' => $teamId],
            );
        }
    }
}
