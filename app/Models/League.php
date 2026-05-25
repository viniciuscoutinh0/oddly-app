<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class League extends Model
{
    /** @use HasFactory<\Database\Factories\LeagueFactory> */
    use HasFactory;

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
