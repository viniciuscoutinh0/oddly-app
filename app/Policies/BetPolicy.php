<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Bet;
use App\Models\User;

final class BetPolicy
{
    public function update(User $user, Bet $bet): bool
    {
        $bet->loadMissing('fixture');

        return $user->id === $bet->user_id && ! $bet->fixture->isLocked();
    }
}
