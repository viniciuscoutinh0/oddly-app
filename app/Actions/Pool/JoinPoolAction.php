<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Models\Pool;
use App\Models\User;
use InvalidArgumentException;

final class JoinPoolAction
{
    public function handle(User $user, Pool $pool, ?string $inviteCode = null): void
    {
        if ($pool->isPrivate() && ($inviteCode === null || $inviteCode !== $pool->invite_code)) {
            throw new InvalidArgumentException('Código de convite inválido.');
        }

        if ($pool->participants()->whereKey($user->id)->exists()) {
            return;
        }

        $pool->participants()->attach($user->id, ['joined_at' => now()]);
    }
}
