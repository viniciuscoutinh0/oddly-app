<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\League;
use Illuminate\Support\Str;

final class LeagueObserver
{
    public function creating(League $league): void
    {
        $league->slug = Str::slug($league->name);
    }
}
