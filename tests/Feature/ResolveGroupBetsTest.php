<?php

declare(strict_types=1);

use App\Actions\Bet\ResolveGroupBetsAction;
use App\Models\GroupBet;
use App\Models\Season;
use App\Models\Team;

it('marks group bets correct against actual group positions', function (): void {
    $season = Season::factory()->create();
    $first = Team::factory()->create();
    $second = Team::factory()->create();

    $season->teams()->attach($first->id, ['group_letter' => 'A', 'group_position' => 1]);
    $season->teams()->attach($second->id, ['group_letter' => 'A', 'group_position' => 2]);

    $right = GroupBet::factory()->for($season)->create([
        'group_letter' => 'A', 'team_id' => $first->id, 'predicted_position' => 1,
    ]);
    $swapped = GroupBet::factory()->for($season)->create([
        'group_letter' => 'A', 'team_id' => $first->id, 'predicted_position' => 2,
    ]);

    app(ResolveGroupBetsAction::class)->handle($season);

    expect($right->refresh()->is_correct)->toBeTrue()
        ->and($swapped->refresh()->is_correct)->toBeFalse();
});

it('marks a bet incorrect when the slot has no actual team yet', function (): void {
    $season = Season::factory()->create();
    $team = Team::factory()->create();

    $bet = GroupBet::factory()->for($season)->create([
        'group_letter' => 'B', 'team_id' => $team->id, 'predicted_position' => 1,
    ]);

    app(ResolveGroupBetsAction::class)->handle($season);

    expect($bet->refresh()->is_correct)->toBeFalse();
});
