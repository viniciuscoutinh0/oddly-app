<?php

declare(strict_types=1);

use App\Actions\Bet\ResolveChampionBetsAction;
use App\Models\ChampionBet;
use App\Models\Season;
use App\Models\Team;

it('marks champion bets correct when they match the winner', function (): void {
    $season = Season::factory()->create();
    $winner = Team::factory()->create();
    $loser = Team::factory()->create();

    $right = ChampionBet::factory()->for($season)->create(['team_id' => $winner->id]);
    $wrong = ChampionBet::factory()->for($season)->create(['team_id' => $loser->id]);

    $season->update(['winner_id' => $winner->id]);
    app(ResolveChampionBetsAction::class)->handle($season->fresh());

    expect($right->refresh()->is_correct)->toBeTrue()
        ->and($wrong->refresh()->is_correct)->toBeFalse();
});

it('does nothing while the season has no winner', function (): void {
    $season = Season::factory()->create(['winner_id' => null]);
    $bet = ChampionBet::factory()->for($season)->create();

    app(ResolveChampionBetsAction::class)->handle($season);

    expect($bet->refresh()->is_correct)->toBeNull();
});

it('resolves automatically via the observer when a winner is set', function (): void {
    $season = Season::factory()->create(['winner_id' => null]);
    $winner = Team::factory()->create();
    $bet = ChampionBet::factory()->for($season)->create(['team_id' => $winner->id]);

    $season->update(['winner_id' => $winner->id]);

    expect($bet->refresh()->is_correct)->toBeTrue();
});
