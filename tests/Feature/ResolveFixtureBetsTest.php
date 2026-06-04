<?php

declare(strict_types=1);

use App\Actions\Bet\ResolveFixtureBetsAction;
use App\Enums\Fixture\Status;
use App\Models\Bet;
use App\Models\Fixture;

function finishedFixture(int $home, int $away): Fixture
{
    return Fixture::factory()->create([
        'status' => Status::Finished,
        'home_score' => $home,
        'away_score' => $away,
    ]);
}

it('flags an exact bet as exact and correct result', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 1]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeTrue()
        ->and($bet->is_correct_result)->toBeTrue()
        ->and($bet->resolved_at)->not->toBeNull();
});

it('flags a correct-result-only bet', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 3, 'away_score' => 0]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeTrue();
});

it('flags a wrong bet', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 0, 'away_score' => 2]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeFalse();
});

it('handles draws as correct result', function (): void {
    $fixture = finishedFixture(1, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 2]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeTrue();
});

it('recomputes idempotently after a score edit', function (): void {
    $fixture = finishedFixture(2, 1);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 1]);

    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());
    $fixture->update(['home_score' => 0, 'away_score' => 0]);
    app(ResolveFixtureBetsAction::class)->handle($fixture->fresh());

    $bet->refresh();
    expect($bet->is_exact)->toBeFalse()->and($bet->is_correct_result)->toBeFalse();
});

it('resolves bets automatically via the observer when a fixture finishes', function (): void {
    $fixture = Fixture::factory()->create([
        'status' => Status::Scheduled,
        'home_score' => null,
        'away_score' => null,
    ]);
    $bet = Bet::factory()->for($fixture)->create(['home_score' => 2, 'away_score' => 1]);

    $fixture->update([
        'status' => Status::Finished,
        'home_score' => 2,
        'away_score' => 1,
    ]);

    expect($bet->refresh()->is_exact)->toBeTrue();
});
