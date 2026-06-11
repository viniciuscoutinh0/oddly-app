<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Exceptions\Bet\BetException;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;

final class PlaceBetAction
{
    public function handle(User $user, Fixture $fixture, int $homeScore, int $awayScore): Bet
    {
        if ($fixture->isLocked() || $fixture->isFinished()) {
            throw new BetException('Os palpites para este jogo estão encerrados.');
        }

        if ($homeScore < 0 || $awayScore < 0 || $homeScore > 99 || $awayScore > 99) {
            throw new BetException('Placar deve estar entre 0 e 99.');
        }

        return $user->bets()->updateOrCreate(
            ['fixture_id' => $fixture->id],
            ['home_score' => $homeScore, 'away_score' => $awayScore],
        );
    }
}
