<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Pool;
use App\Models\User;

final class PoolPolicy
{
    public function view(User $user, Pool $pool): bool
    {
        if (! $pool->isPrivate()) {
            return true;
        }

        return $user->id === $pool->owner_id
            || $pool->participants()->whereKey($user->id)->exists();
    }

    public function update(User $user, Pool $pool): bool
    {
        return $user->id === $pool->owner_id;
    }

    public function delete(User $user, Pool $pool): bool
    {
        return $user->id === $pool->owner_id;
    }
}
