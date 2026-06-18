<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\User\Role;
use App\Models\Pool;
use App\Models\User;

final class PoolPolicy
{
    public function before(User $user): ?bool
    {
        return $user->role === Role::Admin ? true : null;
    }

    public function view(User $user, Pool $pool): bool
    {
        if ($pool->isPublic()) {
            return true;
        }

        return $user->id === $pool->owner_id || $pool->hasParticipant($user);
    }

    public function update(User $user, Pool $pool): bool
    {
        return $user->id === $pool->owner_id;
    }

    public function delete(User $user, Pool $pool): bool
    {
        return $user->id === $pool->owner_id;
    }

    public function isOwner(User $user, Pool $pool): bool
    {
        return $this->isOwner($user, $pool);
    }

    public function bet(User $user, Pool $pool): bool
    {
        return $pool->isOwner($user) || $pool->hasParticipant($user);
    }

    public function seeInviteCode(User $user, Pool $pool): bool
    {
        return $pool->invite_code !== null && $pool->isOwner($user);
    }

    public function leave(User $user, Pool $pool): bool
    {
        return ! $pool->isOwner($user) && $pool->hasParticipant($user);
    }
}
