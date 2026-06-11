<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Fixture\Duration;
use App\Enums\Fixture\Status;
use App\Observers\FixtureObserver;
use Database\Factories\FixtureFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy(FixtureObserver::class)]
final class Fixture extends Model
{
    private const MINUTES_BEFORE_MATCH_TO_LOCK = 30;

    /** @use HasFactory<FixtureFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
            'locked_at' => 'datetime',
            'status' => Status::class,
            'duration' => Duration::class,
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

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    #[Scope]
    public function finished(Builder $query): Builder
    {
        return $query->where('status', Status::Finished);
    }

    public function isLocked(): bool
    {
        return now()->gte(
            $this->locked_at ?? $this->match_date->subMinutes(self::MINUTES_BEFORE_MATCH_TO_LOCK),
        );
    }

    public function isFinished(): bool
    {
        return $this->status === Status::Finished;
    }

    public function winner(): ?Team
    {
        if (! $this->isFinished()) {
            return null;
        }

        [$home, $away] = $this->decisiveScores();

        if ($home === $away) {
            return null;
        }

        return $home > $away ? $this->homeTeam : $this->awayTeam;
    }

    /**
     * Final score pair to display, honouring the duration. Null until finished.
     *
     * @return array{home: int, away: int}|null
     */
    public function finalScore(): ?array
    {
        if (! $this->isFinished()) {
            return null;
        }

        [$home, $away] = $this->decisiveScores();

        return ['home' => $home, 'away' => $away];
    }

    /**
     * Resolve the score pair that decides the result, honouring the duration.
     *
     * @return array{0: int, 1: int}
     */
    private function decisiveScores(): array
    {
        return match ($this->duration) {
            Duration::Penalties => [(int) $this->home_score_pen, (int) $this->away_score_pen],
            Duration::ExtraTime => [(int) $this->home_score_et, (int) $this->away_score_et],
            default => [(int) $this->home_score, (int) $this->away_score],
        };
    }
}
