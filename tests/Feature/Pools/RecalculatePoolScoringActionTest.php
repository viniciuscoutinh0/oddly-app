<?php

declare(strict_types=1);

use App\Actions\Pool\RecalculatePoolScoringAction;
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

it('resolves fixture, champion, and group bets for the pool season', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 2, 'away_score' => 1,
    ]);

    $user = User::factory()->create();
    $pool = Pool::factory()->public()->create([
        'season_id' => $season->id,
        'points_exact' => 10, 'points_champion' => 25, 'points_group_position' => 3,
    ]);
    $pool->participants()->attach($user->id, ['joined_at' => now()]);

    $bet = Bet::factory()->for($fixture)->create([
        'user_id' => $user->id, 'home_score' => 2, 'away_score' => 1,
        'is_exact' => null, 'is_correct_result' => null,
    ]);

    $champion = Team::factory()->create();
    $season->updateQuietly(['winner_id' => $champion->id]);
    $championBet = ChampionBet::factory()->for($season)->create([
        'user_id' => $user->id, 'team_id' => $champion->id, 'is_correct' => null,
    ]);

    $groupTeam = Team::factory()->create();
    $season->teams()->attach($groupTeam->id, ['group_letter' => 'A', 'group_position' => 1]);
    $groupBet = GroupBet::factory()->for($season)->create([
        'user_id' => $user->id, 'group_letter' => 'A', 'team_id' => $groupTeam->id,
        'predicted_position' => 1, 'is_correct' => null,
    ]);

    app(RecalculatePoolScoringAction::class)->handle($pool);

    expect($bet->refresh()->is_exact)->toBeTrue()
        ->and($championBet->refresh()->is_correct)->toBeTrue()
        ->and($groupBet->refresh()->is_correct)->toBeTrue()
        ->and(app(PoolStandings::class)->for($pool)->first()->points)->toBe(38);
});

it('is idempotent', function (): void {
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    $fixture = Fixture::factory()->for($stage)->create([
        'status' => Status::Finished, 'home_score' => 0, 'away_score' => 0,
    ]);
    $pool = Pool::factory()->public()->create(['season_id' => $season->id]);
    $user = User::factory()->create();
    $pool->participants()->attach($user->id, ['joined_at' => now()]);
    Bet::factory()->for($fixture)->create([
        'user_id' => $user->id, 'home_score' => 0, 'away_score' => 0,
    ]);

    $action = app(RecalculatePoolScoringAction::class);
    $action->handle($pool);
    $action->handle($pool);

    expect(app(PoolStandings::class)->for($pool)->first()->points)->toBe($pool->points_exact);
});
