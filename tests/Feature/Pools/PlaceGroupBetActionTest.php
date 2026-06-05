<?php

declare(strict_types=1);

use App\Actions\Bet\PlaceGroupBetAction;
use App\Models\Fixture;
use App\Models\GroupBet;
use App\Models\Season;
use App\Models\Stage;
use App\Models\Team;
use App\Models\User;

it('upserts first and second group bets', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $first = Team::factory()->create();
    $second = Team::factory()->create();

    app(PlaceGroupBetAction::class)->handle($user, $season, 'A', [1 => $first->id, 2 => $second->id]);

    expect(GroupBet::where('user_id', $user->id)->where('season_id', $season->id)->where('group_letter', 'A')->count())->toBe(2);

    $other = Team::factory()->create();
    app(PlaceGroupBetAction::class)->handle($user, $season, 'A', [1 => $other->id, 2 => $second->id]);

    expect(GroupBet::where('user_id', $user->id)->where('season_id', $season->id)->where('group_letter', 'A')->count())->toBe(2)
        ->and(GroupBet::where('user_id', $user->id)->where('season_id', $season->id)->where('group_letter', 'A')->where('predicted_position', 1)->first()->team_id)->toBe($other->id);
});

it('throws when the bonus is locked', function (): void {
    $user = User::factory()->create();
    $season = Season::factory()->create();
    $stage = Stage::factory()->for($season)->create();
    Fixture::factory()->for($stage)->create(['match_date' => now()->subHour()]);
    $team = Team::factory()->create();

    expect(fn () => app(PlaceGroupBetAction::class)->handle($user, $season, 'A', [1 => $team->id, 2 => $team->id]))
        ->toThrow(RuntimeException::class);
});
