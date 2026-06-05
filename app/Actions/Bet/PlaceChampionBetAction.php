<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\ChampionBet;
use App\Models\Season;
use App\Models\User;
use RuntimeException;

final class PlaceChampionBetAction
{
    public function handle(User $user, Season $season, int $teamId): ChampionBet
    {
        if ($season->bonusLocked()) {
            throw new RuntimeException('Os palpites bônus estão encerrados.');
        }

        return ChampionBet::updateOrCreate(
            ['user_id' => $user->id, 'season_id' => $season->id],
            ['team_id' => $teamId],
        );
    }
}
