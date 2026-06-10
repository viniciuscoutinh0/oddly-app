<?php

declare(strict_types=1);

use App\Actions\Pool\JoinPoolAction;
use App\Enums\Fixture\Status;
use App\Models\Bet;
use App\Models\ChampionBet;
use App\Models\Fixture;
use App\Models\GroupBet;
use App\Models\Pool;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;
use App\Services\Pool\PoolStandings;

it('ranks participants by total points using pool rules', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);

    $pool = Pool::factory()->public()->create([
        'season_id' => $season->id,
        'points_exact' => 10, 'points_result' => 5, 'points_champion' => 25,
    ]);

    $exactUser = User::factory()->create();
    $resultUser = User::factory()->create();
    app(JoinPoolAction::class)->handle($exactUser, $pool);
    app(JoinPoolAction::class)->handle($resultUser, $pool);

    Bet::factory()->for($fixture)->create([
        'user_id' => $exactUser->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => true, 'is_correct_result' => true,
    ]);
    Bet::factory()->for($fixture)->create([
        'user_id' => $resultUser->id, 'home_score' => 3, 'away_score' => 0,
        'is_exact' => false, 'is_correct_result' => true,
    ]);
    $champion = Team::factory()->create();
    $season->update(['winner_id' => $champion->id]);
    ChampionBet::factory()->for($season)->create([
        'user_id' => $exactUser->id, 'team_id' => $champion->id, 'is_correct' => true,
    ]);

    $standings = app(PoolStandings::class)->for($pool);

    expect($standings->first()->id)->toBe($exactUser->id)
        ->and($standings->first()->points)->toBe(35)
        ->and($standings->last()->points)->toBe(5);
});

it('returns zero points for a participant with no bets', function (): void {
    $pool = Pool::factory()->public()->create();
    $user = User::factory()->create();
    app(JoinPoolAction::class)->handle($user, $pool);

    $standings = app(PoolStandings::class)->for($pool);

    expect($standings)->toHaveCount(1)
        ->and($standings->first()->points)->toBe(0);
});

it('adds group bonus points using the pool rule', function (): void {
    $season = Season::factory()->create();
    $pool = Pool::factory()->public()->create([
        'season_id' => $season->id,
        'points_group_position' => 3,
    ]);

    $user = User::factory()->create();
    app(JoinPoolAction::class)->handle($user, $pool);

    // Two correct group-position bets => 2 * 3 = 6 points.
    GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'A', 'predicted_position' => 1, 'is_correct' => true,
    ]);
    GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'A', 'predicted_position' => 2, 'is_correct' => true,
    ]);
    // An incorrect one must not count.
    GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'B', 'predicted_position' => 1, 'is_correct' => false,
    ]);

    $standings = app(PoolStandings::class)->for($pool);

    expect($standings->first()->points)->toBe(6);
});
