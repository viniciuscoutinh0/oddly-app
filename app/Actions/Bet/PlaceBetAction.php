<?php

declare(strict_types=1);

namespace App\Actions\Bet;

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;
use RuntimeException;

final class PlaceBetAction
{
    public function handle(User $user, Fixture $fixture, int $homeScore, int $awayScore): Bet
    {
        if ($this->isLocked($fixture)) {
            throw new RuntimeException('Os palpites para este jogo estão encerrados.');
        }

        return $user->bets()->updateOrCreate(
            ['fixture_id' => $fixture->id],
            ['home_score' => $homeScore, 'away_score' => $awayScore],
        );
    }

    private function isLocked(Fixture $fixture): bool
    {
        $lockTime = $fixture->locked_at ?? $fixture->match_date;

        return now()->gte($lockTime);
    }
}
