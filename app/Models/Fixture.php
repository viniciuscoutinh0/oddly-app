<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Fixture extends Model
{
    /** @use HasFactory<\Database\Factories\FixtureFactory> */
    use HasFactory;

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function home(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function away(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }
}
