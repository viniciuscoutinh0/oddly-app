<?php

declare(strict_types=1);

use App\Actions\Bet\PlaceChampionBetAction;
use App\Models\ChampionBet;
use App\Models\Fixture;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;

it('upserts the champion bet for the user and season', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $a = Team::factory()->create();
    $b = Team::factory()->create();

    app(PlaceChampionBetAction::class)->handle($user, $season, $a->id);
    app(PlaceChampionBetAction::class)->handle($user, $season, $b->id);

    expect(ChampionBet::where('user_id', $user->id)->where('season_id', $season->id)->count())->toBe(1)
        ->and(ChampionBet::where('user_id', $user->id)->where('season_id', $season->id)->first()->team_id)->toBe($b->id);
});

it('throws when the bonus is locked', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);
    $team = Team::factory()->create();

    expect(fn () => app(PlaceChampionBetAction::class)->handle($user, $season, $team->id))
        ->toThrow(RuntimeException::class);
});
