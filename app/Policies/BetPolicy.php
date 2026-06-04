<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bet;
use App\Models\User;

final class BetPolicy
{
    public function update(User $user, Bet $bet): bool
    {
        $fixture = $bet->fixture;
        $lockTime = $fixture->locked_at ?? $fixture->match_date;

        return $user->id === $bet->user_id && now()->lt($lockTime);
    }
}
