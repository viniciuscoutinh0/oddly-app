<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Fixture\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Fixture extends Model
{
    /** @use HasFactory<\Database\Factories\FixtureFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'locked_at' => 'datetime',
            'status' => Status::class,
        ];
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(Stage::class);
    }

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function isLocked(): bool
    {
        return $this->locked_at !== null && now()->gte($this->locked_at);
    }

    public function isFinished(): bool
    {
        return $this->status === Status::Finished;
    }

    public function winner(): ?Team
    {
        if (! $this->isFinished() || $this->home_score === $this->away_score) {
            return null;
        }

        return $this->home_score > $this->away_score
            ? $this->homeTeam
            : $this->awayTeam;
    }
}
