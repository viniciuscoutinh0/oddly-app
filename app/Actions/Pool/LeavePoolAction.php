<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Models\Pool;
use App\Models\User;
use RuntimeException;

final class LeavePoolAction
{
    public function handle(User $user, Pool $pool): void
    {
        if ($user->id === $pool->owner_id) {
            throw new RuntimeException('O dono não pode sair do próprio bolão.');
        }

        $pool->participants()->detach($user->id);
    }
}
