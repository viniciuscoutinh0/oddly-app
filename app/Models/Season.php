<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\SeasonObserver;
use Database\Factories\SeasonFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[ObservedBy(SeasonObserver::class)]
final class Season extends Model
{
    /** @use HasFactory<SeasonFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'season_teams')
            ->withPivot('group_letter', 'group_position')
            ->withTimestamps();
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(Stage::class)->orderBy('sort_order');
    }

    public function fixtures(): HasManyThrough
    {
        return $this->hasManyThrough(Fixture::class, Stage::class);
    }

    public function pools(): HasMany
    {
        return $this->hasMany(Pool::class);
    }

    public function championBets(): HasMany
    {
        return $this->hasMany(ChampionBet::class);
    }

    public function groupBets(): HasMany
    {
        return $this->hasMany(GroupBet::class);
    }

    public function name(): Attribute
    {
        return Attribute::get(
            fn (): string => $this->start_date->format('Y'),
        );
    }
}
